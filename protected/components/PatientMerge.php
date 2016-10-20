<?php
/**
 * OpenEyes.
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
class PatientMerge
{
    /**
     * @var Patient AR
     */
    private $primary_patient;

    /**
     * @var Patient AR
     */
    private $secondary_patient;

    /**
     * @var array
     */
    private $log = array();

    /**
     * Set primary patient by id.
     *
     * @param int $id
     */
    public function setPrimaryPatientById($id)
    {
        $this->primary_patient = Patient::model()->findByPk($id);
    }

    /**
     * Returns the Primary patient.
     *
     * @return Patient AR record
     */
    public function getPrimaryPatient()
    {
        return $this->primary_patient;
    }

    /**
     * Set secondaty patient by id.
     *
     * @param int $id
     */
    public function setSecondaryPatientById($id)
    {
        $this->secondary_patient = Patient::model()->findByPk($id);
    }

    /**
     * Returns the secondary patient.
     *
     * @return Patient AR record
     */
    public function getSecondaryPatient()
    {
        return $this->secondary_patient;
    }

    /**
     * Adding message to the log array.
     *
     * @param string $msg
     */
    public function addLog($msg)
    {
        $this->log[] = $msg;
    }

    /**
     * Returns the log messages.
     * 
     * @return type
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Load data from PatientMergeRequest AR record.
     * 
     * @param PatientMergeRequest $request
     */
    public function load(PatientMergeRequest $request)
    {
        $this->setPrimaryPatientById($request->primary_id);
        $this->setSecondaryPatientById($request->secondary_id);
    }

    /**
     * Compare data in the patient table.
     * 
     * @param patient AR record $primary
     * @param patient AR record $secondary
     *
     * @return array
     */
    public function comparePatientDetails(Patient $primary, Patient $secondary)
    {
        //columns to be compared in patient table
        $columns = array(
            'dob', 'gender', /*'hos_num', 'nhs_num', 'date_of_death', 'ethnic_group_id', 'contact_id', */
        );

        $conflict = array();

        foreach ($columns as $column) {
            if ($primary->$column !== $secondary->$column) {
                $conflict[] = array(
                    'column' => $column,
                    'primary' => $primary->$column,
                    'secondary' => $secondary->$column,
                );
            }
        }

        return array(
            'is_conflict' => !empty($conflict),
            'details' => $conflict,
        );
    }

    /**
     * Do the actual merging by calling separated functions to move episodes, events etc...
     * 
     * @return bool $is_merged success or fail
     */
    public function merge()
    {
        $is_merged = false;

        // Update Episode
        $is_merged = $this->updateEpisodes($this->primary_patient, $this->secondary_patient);

        // Update legacy episodes
        $is_merged = $is_merged && $this->updateLegacyEpisodes($this->primary_patient, $this->secondary_patient);

        // Update allergyAssignments
        $is_merged = $is_merged && $this->updateAllergyAssignments($this->primary_patient, $this->secondary_patient);

        // Updates riskAssignments
        $is_merged = $is_merged && $this->updateRiskAssignments($this->primary_patient, $this->secondary_patient->riskAssignments);

        // Update previousOperations
        $is_merged = $is_merged && $this->updatePreviousOperations($this->primary_patient, $this->secondary_patient->previousOperations);

        //Update Other ophthalmic diagnoses
        $is_merged = $is_merged && $this->updateOphthalmicDiagnoses($this->primary_patient, $this->secondary_patient->ophthalmicDiagnoses);
        
        // Update Systemic Diagnoses
        $is_merged = $is_merged && $this->updateSystemicDiagnoses($this->primary_patient, $this->secondary_patient->systemicDiagnoses);

        if ($is_merged) {
            $secondary_patient = $this->secondary_patient;

            $secondary_patient->deleted = 1;

            if ($secondary_patient->save()) {
                $msg = 'Patient hos_num: '.$this->secondary_patient->hos_num.' flagged as deleted.';
                $this->addLog($msg);
                Audit::add('Patient Merge', 'Patient flagged as deleted', $msg);
                $is_merged = $is_merged && true;
            } else {
                throw new Exception('Failed to update Patient: '.print_r($secondary_patient->errors, true));
            }
        }

        return $is_merged;
    }

    /**
     * Updating an episode
     *  - if primary has no episodes than we just assign the secondary patient's episodes to the primary
     *  - if secondary patient has no episodes we have nothing to do here
     *  - if both patiens have episode we have to check if there is any conflicting(same subspeicaly like cataract or glaucoma) episodes
     *      - we move the non conflictong episodes from secondary to primary
     *      - when two episodes are conflicting we have to keep the episode with the highest status (when compared using the standard order of status from New to Discharged).
     *      - start date should be the earliest start date of the two episodes
     *      - end date should be the latest end date of the two episodes (null is classed as later than any date).
     *   
     * @param Patient $primary_patient
     * @param Patient $secondary_patient
     *
     * @return bool
     *
     * @throws Exception
     */
    public function updateEpisodes(Patient $primary_patient, Patient $secondary_patient)
    {
        $primary_has_episodes = $primary_patient->episodes;
        $secondary_has_episodes = $secondary_patient->episodes;

        // if primary has no episodes than we just assign the secondary patient's episodes to the primary
        if (!$primary_has_episodes && $secondary_has_episodes) {
            // this case is fine, we can assign the episodes from secondary to primary
            $this->updateEpisodesPatientId($primary_patient->id, $secondary_patient->episodes);
        } elseif ($primary_has_episodes && !$secondary_has_episodes) {
            // primary has episodes but secondary has not, nothing to do here
        } else {
            // Both have episodes, we have to compare the subspecialties

            foreach ($secondary_patient->episodes as $secondary_episode) {
                $secondary_subspecialty = $secondary_episode->getSubspecialtyID();

                $is_same_subspecialty = false;
                foreach ($primary_has_episodes as $primary_episode) {
                    $primary_subspecialty = $primary_episode->getSubspecialtyID();

                    if ($secondary_subspecialty == $primary_subspecialty) {

                        /* We have to keep the episode withe the highest status */             

                        if ($primary_episode->status->order > $secondary_episode->status->order) {
                            // the primary episode has greater status than the secondary so we move the events from the Secondary into the Primary
                            $this->updateEventsEpisodeId($primary_episode->id, $secondary_episode->events);

                            //set earliest start date and latest end date of the two episodes
                            list($primary_episode->start_date, $primary_episode->end_date) = $this->getTwoEpisodesStartEndDate($primary_episode, $secondary_episode);

                            $primary_episode->save();

                            // after all events are moved we flag the secondary episode as deleted
                            $secondary_episode->deleted = 1;
                            if ($secondary_episode->save()) {
                                $msg = 'Episode '.$secondary_episode->id." marked as deleted, events moved under the primary patient's same firm episode.";
                                $this->addLog($msg);
                                Audit::add('Patient Merge', 'Episode marked as deleted', $msg);
                            } else {
                                throw new Exception('Failed to update Episode: '.$secondary_episode->id.' '.print_r($secondary_episode->errors, true));
                            }
                        } else {

                            // the secondary episode has greated status than the primary so we move the events from the Primary into the Secondary
                            $this->updateEventsEpisodeId($secondary_episode->id, $primary_episode->events);

                            list($secondary_episode->start_date, $secondary_episode->end_date) = $this->getTwoEpisodesStartEndDate($primary_episode, $secondary_episode);

                            /* BUT do not forget we have to delete the primary episode AND move the secondary episode to the primary patient **/
                            $primary_episode->deleted = 1;

                            if ($primary_episode->save()) {
                                $msg = 'Episode '.$primary_episode->id." marked as deleted, events moved under the secondary patient's same firm episode.";
                                $this->addLog($msg);
                                Audit::add('Patient Merge', 'Episode marked as deleted', $msg);
                            } else {
                                throw new Exception('Failed to update Episode: '.$primary_episode->id.' '.print_r($primary_episode->errors, true));
                            }

                            //then we move the episode to the pri1mary
                            $this->updateEpisodesPatientId($primary_patient->id, array($secondary_episode));
                        }

                        $is_same_subspecialty = true;
                    }
                }

                // if there is no conflict we still need to move the secondary episode to the primary patient
                if (!$is_same_subspecialty) {
                    $this->updateEpisodesPatientId($primary_patient->id, array($secondary_episode));
                } else {
                    // there was a conflict and the episode was already moved in the foreach above
                }
            }
        }

        // if the save() functions not throwing errors than we can just return true
        return true;
    }

    /**
     * Moving Legacy episode from secondary patient to primary.
     * 
     * @param type $primary_patient
     * @param type $secondary_patient
     *
     * @return bool
     *
     * @throws Exception
     */
    public function updateLegacyEpisodes($primary_patient, $secondary_patient)
    {
        // if the secondary patient has legacy episodes
        if ($secondary_patient->legacyepisodes) {

            // if primary patient doesn't have legacy episode we can just update the episode's patient_id to assign it to the primary patient
            if (!$primary_patient->legacyepisodes) {

                // Patient can have only one legacy episode
                $legacy_episode = $secondary_patient->legacyepisodes[0];

                $legacy_episode->patient_id = $primary_patient->id;
                if ($legacy_episode->save()) {
                    $msg = 'Legacy Episode '.$legacy_episode->id.' moved from patient '.$secondary_patient->hos_num.' to '.$primary_patient->hos_num;
                    $this->addLog($msg);
                    Audit::add('Patient Merge', 'Legacy Episode moved', $msg);
                } else {
                    throw new Exception('Failed to update (legacy) Episode: '.$legacy_episode->id.' '.print_r($legacy_episode->errors, true));
                }
            } else {
                $primary_legacy_episode = $primary_patient->legacyepisodes[0];
                $secondary_legacy_episode = $secondary_patient->legacyepisodes[0];

                if ($primary_legacy_episode->created_date < $secondary_legacy_episode->created_date) {
                    // we move the events from the secondaty patient's legacy episod to the primary patient's legacy epiode
                    $this->updateEventsEpisodeId($primary_legacy_episode->id, $secondary_legacy_episode->events);

                    // Flag secondary patient's legacy episode deleted as it will be empty

                    $secondary_legacy_episode->deleted = 1;
                    if ($secondary_legacy_episode->save()) {
                        $msg = 'Legacy Episode '.$secondary_legacy_episode->id."marked as deleted, events moved under the primary patient's same firm episode.";
                        $this->addLog($msg);
                        Audit::add('Patient Merge', 'Legacy Episode marked as deleted', $msg);
                    } else {
                        throw new Exception('Failed to update (legacy) Episode: '.$secondary_legacy_episode->id.' '.print_r($secondary_legacy_episode->errors, true));
                    }
                } else {
                    // in this case the secondary legacy episode is older than the primary
                    // so move the primary legacy episode's events to the secondary legacy episode
                    // then move the secondary legacy episode to the Primary patient
                    // then flag the primary's legacy episode as deleted // as only 1 legacy episode can be assigned to the patient

                    $this->updateEventsEpisodeId($secondary_legacy_episode->id, $primary_legacy_episode->events);

                    $primary_legacy_episode->deleted = 1;

                    if ($primary_legacy_episode->save()) {
                        $msg = 'Legacy Episode '.$primary_legacy_episode->id."marked as deleted, events moved under the secondary patient's same firm episode.";
                        $this->addLog($msg);
                        Audit::add('Patient Merge', 'Legacy Episode marked as deleted', $msg);
                    } else {
                        throw new Exception('Failed to update (legacy) Episode: '.$primary_legacy_episode->id.' '.print_r($primary_legacy_episode->errors, true));
                    }

                    //then we move the episode to the pri1mary
                    $this->updateEpisodesPatientId($primary_patient->id, array($secondary_legacy_episode));
                }
            }
        }

        // if the save() functions not throwing errors than we can just return true
        return true;
    }

    /**
     * Updates the patient id in the Allergy Assigment.
     * 
     * @param int         $new_patient_id Primary patient id
     * @param array of AR $allergies
     *
     * @throws Exception AllergyAssigment cannot be saved
     */
    public function updateAllergyAssignments($primary_patient, $secondary_patient)
    {
        $primary_assignments = $primary_patient->allergyAssignments;
        $secondary_assignments = $secondary_patient->allergyAssignments;

        if (!$primary_assignments && $secondary_assignments) {
            foreach ($secondary_assignments as $allergy_assignment) {
                $msg = 'AllergyAssignment '.$allergy_assignment->id.' moved from patient '.$allergy_assignment->patient->hos_num.' to '.$primary_patient->hos_num;
                $allergy_assignment->patient_id = $primary_patient->id;
                if ($allergy_assignment->save()) {
                    $this->addLog($msg);
                    Audit::add('Patient Merge', 'AllergyAssignment moved patient', $msg);
                } else {
                    throw new Exception('Failed to update AllergyAssigment: '.$allergy_assignment->id.' '.print_r($allergy_assignment->errors, true));
                }
            }
        } elseif ($primary_assignments && $secondary_assignments) {
            foreach ($secondary_assignments as $secondary_assignment) {
                $same_assignment = false;
                foreach ($primary_assignments as $primary_assignment) {
                    if ($primary_assignment->allergy_id ==  $secondary_assignment->allergy_id) {
                        // the allergy is already present in the primary patient's record so we just update the 'comment' and 'other' fields

                        $same_assignment = true;

                        $comments = $primary_assignment->comments.' ; '.$secondary_assignment->comments;
                        $other = $primary_assignment->other.' ; '.$secondary_assignment->other;

                        $primary_assignment->comments = $comments;
                        $primary_assignment->other = $other;

                        if ($primary_assignment->save()) {
                            $msg = "AllergyAssignment 'comments' and 'other' updated";
                            $this->addLog($msg);
                            Audit::add('Patient Merge', 'AllergyAssignment updated', $msg);
                        } else {
                            throw new Exception('Failed to update AllergyAssigment: '.$primary_assignment->id.' '.print_r($primary_assignment->errors, true));
                        }

                        // as we just copied the comments and other fields we remove the assignment
                        $secondary_assignment->delete();
                    }
                }

                // This means we have to move the assignment from secondary to primary
                if (!$same_assignment) {
                    $secondary_assignment->patient_id = $primary_patient->id;
                    if ($secondary_assignment->save()) {
                        $msg = 'AllergyAssignment '.$secondary_assignment->id.' moved from patient '.$secondary_patient->hos_num.' to '.$primary_patient->hos_num;
                        $this->addLog($msg);
                        Audit::add('Patient Merge', 'AllergyAssignment moved from patient', $msg);
                    } else {
                        throw new Exception('Failed to update AllergyAssigment: '.$allergy_assignment->id.' '.print_r($allergy_assignment->errors, true));
                    }
                }
            }
        }

        return true;
    }

    /**
     * Updates patient id in Risk Assignment.
     * 
     * @param int         $new_patient_id
     * @param array of AR $risks
     *
     * @throws Exception Failed to save RiskAssigment
     */
    public function updateRiskAssignments($primary_patient, $risk_assignments)
    {
        foreach ($risk_assignments as $risk_assignment) {
            $msg = 'RiskAssignment '.$risk_assignment->id.' moved from patient '.$risk_assignment->patient->hos_num.' to '.$primary_patient->id;
            $risk_assignment->patient_id = $primary_patient->id;
            if ($risk_assignment->save()) {
                $this->addLog($msg);
                Audit::add('Patient Merge', 'RiskAssignment moved patient', $msg);
            } else {
                throw new Exception('Failed to update RiskAssigment: '.$risk_assignment->id.' '.print_r($risk_assignment->errors, true));
            }
        }

        return true;
    }

    /**
     * Moving previous operations from secondaty patient to primary.
     * 
     * @param Patient $new_patient
     * @param type $previous_operations
     *
     * @return bool
     *
     * @throws Exception
     */
    public function updatePreviousOperations($new_patient, $previous_operations)
    {

        foreach ($previous_operations as $previous_operation) {
            $msg = 'Previous Operation '.$previous_operation->id.' moved from Patient ' . $previous_operation->patient->hos_num.' to '.$new_patient->hos_num;
            $previous_operation->patient_id = $new_patient->id;
            if ($previous_operation->save()) {
                $this->addLog($msg);
                Audit::add('Patient Merge', 'Previous Operation moved patient', $msg);
            } else {
                throw new Exception('Failed to update Previous Operation: ' . $previous_operation->id.' ' . print_r($previous_operation->errors, true));
            }
        }

        return true;
    }
    
    /**
     * Updates the Ophthalmic Diagnoses' patient_id
     * 
     * @param Patient $new_patient
     * @param type $ophthalmic_diagnoses
     * @throws Exception
     */
    public function updateOphthalmicDiagnoses($new_patient, $ophthalmic_diagnoses)
    {
        foreach ($ophthalmic_diagnoses as $ophthalmic_diagnosis) {
            $msg = 'Ophthalmic Diagnosis(SecondaryDiagnosis) ' . $ophthalmic_diagnosis->id . ' moved from Patient ' . $ophthalmic_diagnosis->patient->hos_num . ' to ' . $new_patient->hos_num;
            $ophthalmic_diagnosis->patient_id = $new_patient->id;
            if ($ophthalmic_diagnosis->save()) {
                $this->addLog($msg);
                Audit::add('Patient Merge', 'Ophthalmic Diagnosis(SecondaryDiagnosis) moved patient', $msg);
            } else {
                throw new Exception('Failed to update Ophthalmic Diagnosis(SecondaryDiagnosis): ' . $ophthalmic_diagnosis->id . ' ' . print_r($ophthalmic_diagnosis->errors, true));
            }
        }
        
        return true;
    }
    
    /**
     * Update Systemati Diagnoses' patient id
     * 
     * @param Patient $new_patient
     * @param type $systemic_diagnoses
     * @return boolean
     * @throws Exception
     */
    public function updateSystemicDiagnoses($new_patient, $systemic_diagnoses)
    {
        foreach ($systemic_diagnoses as $systemic_diagnosis) {
            $msg = 'Systemic Diagnoses ' . $systemic_diagnosis->id . ' moved from Patient ' . $systemic_diagnosis->patient->hos_num . ' to ' . $new_patient->hos_num;
            $systemic_diagnosis->patient_id = $new_patient->id;
            if ($systemic_diagnosis->save()) {
                $this->addLog($msg);
                Audit::add('Patient Merge', 'Systemic Diagnoses moved patient', $msg);
            } else {
                throw new Exception('Failed to update Systemic Diagnoses: ' . $systemic_diagnosis->id . ' ' . print_r($systemic_diagnosis->errors, true));
            }
        }
        
        return true;
    }
    
    

    /**
     * Assign episodes to a new paient id.
     *
     * @param int $patientId the primary Patient Id
     * @param array of AR $episodes
     *
     * @return bool true if no error thrown
     */
    public function updateEpisodesPatientId($new_patient_id, $episodes)
    {
        foreach ($episodes as $episode) {
            $msg = 'Episode '.$episode->id.' moved from patient '.$episode->patient_id.' to '.$new_patient_id;
            $episode->patient_id = $new_patient_id;

            if ($episode->save()) {
                $this->addLog($msg);
                Audit::add('Patient Merge', 'Episode moved patient', $msg);
            } else {
                throw new Exception('Failed to save Episode: '.print_r($secondary_patient->errors, true));
            }
        }

        return true;
    }

    /**
     * Moving event from one episode to another.
     * 
     * @param int   $new_episode_id
     * @param array $events
     *
     * @return bool
     *
     * @throws Exception
     */
    public function updateEventsEpisodeId($new_episode_id, $events)
    {
        foreach ($events as $event) {
            $msg = 'Event '.$event->id.' moved from Episode '.$event->episode_id.' to '.$new_episode_id;

            $event->episode_id = $new_episode_id;

            if ($event->save()) {
                $this->addLog($msg);
                Audit::add('Patient Merge', 'Event moved episode', $msg);
            } else {
                throw new Exception('Failed to save Event: '.print_r($event->errors, true));
            }
        }

        return true;
    }

    /**
     * Returns the  earliest start date and the latest end date of the two episodes
     * 
     * @param Episode $primary_episode
     * @param Episode $secondary_episode
     * @return array start date, end date
     */
    public function getTwoEpisodesStartEndDate(Episode $primary_episode, Episode $secondary_episode)
    {
        $start_date = ($primary_episode->start_date > $secondary_episode->start_date) ? $secondary_episode->start_date : $primary_episode->start_date;

        if( !$primary_episode->end_date || !$secondary_episode->end_date){
            $end_date = null;
        } else {
            $end_date = ($primary_episode->end_date < $secondary_episode->end_date) ? $secondary_episode->end_date : $primary_episode->end_date;
        }

        return array($start_date, $end_date);
    }
}

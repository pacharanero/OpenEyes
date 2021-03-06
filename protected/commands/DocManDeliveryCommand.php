<?php

/**
 * OpenEyes.
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
class DocManDeliveryCommand extends CConsoleCommand
{
    private $path;

    private $event;

    private $csv_format = 'OEGPLetterReport_%s.csv';

    private $generate_xml = false;

    private $generate_csv = false;

    /**
     * DocManDeliveryCommand constructor.
     */
    public function __construct()
    {
        $this->path = Yii::app()->params['docman_export_dir'];
        $this->generate_xml = !isset(Yii::app()->params['docman_xml_format']) || Yii::app()->params['docman_xml_format'] !== 'none';
        $this->generate_csv = Yii::app()->params['docman_generate_csv'];

        // check if directory exists
        if (!is_dir($this->path)) {
            mkdir($this->path);
            echo "ALERT! Directory " . $this->path . " has been created!";
        }
        parent::__construct(null, null);
    }


    /**
     * Run the command.
     */
    public function run($args)
    {
        $pending_documents = $this->getPendingDocuments();
        foreach ($pending_documents as $document) {
            echo 'Processing event ' . $document->document_target->document_instance->correspondence_event_id . PHP_EOL;
            $this->savePDFFile($document->document_target->document_instance->correspondence_event_id, $document->id);
        }
    }

    /**
     * @return CActiveRecord[]
     */
    private function getPendingDocuments()
    {   
        $criteria = new CDbCriteria();
        $criteria->join = "JOIN `document_target` tr ON t.`document_target_id` = tr.id";
        $criteria->join .= " JOIN `document_instance` i ON tr.`document_instance_id` = i.`id`";
        $criteria->join .= " JOIN event e ON i.`correspondence_event_id` = e.id";
        $criteria->addCondition("e.deleted = 0");
        $criteria->addCondition("t.`output_status` = 'PENDING' AND t.`output_type`= 'Docman'");
        
        return DocumentOutput::model()->findAll($criteria);
    }

    /**
     * @param $event_id
     * @param $output_id
     *
     * @return bool;
     */
    private function savePDFFile($event_id, $output_id)
    {
        $pdf_generated = false;
        $xml_generated = false;
        $this->event = Event::model()->findByPk($event_id);
        if ($this->event) {
            $login_page = Yii::app()->params['docman_login_url'];
            $username = Yii::app()->params['docman_user'];
            $password = Yii::app()->params['docman_password'];
            $print_url = Yii::app()->params['docman_print_url'];
            $inject_autoprint_js = Yii::app()->params['docman_inject_autoprint_js'];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $login_page);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie.txt');
            curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt');

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                die(curl_error($ch));
            }

            preg_match("/YII_CSRF_TOKEN = '(.*)';/", $response, $token);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_POST, true);

            $params = array(
                'LoginForm[username]' => $username,
                'LoginForm[password]' => $password,
                'LoginForm[YII_CSRF_TOKEN]' => $token[0],
                'YII_CSRF_TOKEN' => $token[0],
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

            curl_exec($ch);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_URL, $print_url . $this->event->id . '?auto_print=' . (int)$inject_autoprint_js . '&print_only_gp=1');
            $content = curl_exec($ch);
            
            curl_close($ch);
            
            if(substr($content, 0, 4) !== "%PDF"){
                echo 'File is not a PDF for event id: '.$this->event->id."\n";
                $this->updateFailedDelivery($output_id);
                return false;
            }
            
            if (!isset(Yii::app()->params['docman_filename_format']) || Yii::app()->params['docman_filename_format'] === 'format1') {
                $filename = "OPENEYES_" . (str_replace(' ', '', $this->event->episode->patient->hos_num)) . '_' . $this->event->id . "_" . rand();
            } else {
                if (Yii::app()->params['docman_filename_format'] === 'format2') {
                    $filename = (str_replace(' ', '', $this->event->episode->patient->hos_num)) . '_' . date('YmdHi',
                            strtotime($this->event->last_modified_date)) . '_' . $this->event->id;
                } else {
                    if (Yii::app()->params['docman_filename_format'] === 'format3') {
                        $filename = (str_replace(' ', '', $this->event->episode->patient->hos_num)) . '_edtdep-OEY_' .
                            date('Ymd_His', strtotime($this->event->last_modified_date)) . '_' . $this->event->id;
                    }
                }
            }
            
            $pdf_generated = (file_put_contents($this->path . "/" . $filename . ".pdf", $content) !== false);

            if ($this->generate_xml) {
                $xml_generated = $this->generateXMLOutput($filename);
            }

            if (!$pdf_generated || ($this->generate_xml && !$xml_generated)) {
                echo 'Generating for file ' . $filename . ' failed' . PHP_EOL;

                return false;
            }

            $correspondenceDate = $this->event->event_date;

            $event_type = EventType::model()->find('class_name=?', array('OphTrOperationnote'));
            $event_type_id = $event_type->id;

            $criteria = new CDbCriteria();
            $criteria->condition = "episode_id = '" . $this->event->episode->id
                . "' AND event_date <= '$correspondenceDate' AND deleted = 0 AND event_type_id = '$event_type_id'";
            $criteria->order = 'event_date desc, created_date desc';

            $lastOpNoteDate = '';
            if($opNote = Event::model()->find($criteria)){
                $lastOpNoteDate = $opNote->event_date;
            }

            $event_type = EventType::model()->find('class_name=?', array('OphCiExamination'));
            $event_type_id = $event_type->id;

            $criteria = new CDbCriteria();
            $criteria->condition = "episode_id = '" . $this->event->episode->id
                . "' AND event_date <= '$correspondenceDate' AND deleted = 0 AND event_type_id = '$event_type_id'";
            $criteria->order = 'event_date desc, created_date desc';

            $lastExamDate = '';
            if($examEvent = Event::model()->find($criteria)){
                $lastExamDate = $examEvent->event_date;
            }

            $lastSignificantEventDate = '';
            if(!$lastExamDate && $lastOpNoteDate) {
                $lastSignificantEventDate = $lastOpNoteDate;
            }
            if($lastExamDate && !$lastOpNoteDate) {
                $lastSignificantEventDate = $lastExamDate;
            }
            if(!$lastExamDate && !$lastOpNoteDate) {
                $lastSignificantEventDate = NULL;
            }
            if($lastExamDate && $lastOpNoteDate){
                $diff = date_diff(date_create($lastExamDate), date_create($lastOpNoteDate));
                if($diff->days >= 0){
                    $lastSignificantEventDate = $lastOpNoteDate;
                }else{
                    $lastSignificantEventDate = $lastExamDate;
                }
            }

            if ($this->updateDelivery($output_id)) {
                $element_letter = ElementLetter::model()->findByAttributes(array("event_id" => $this->event->id));
                $this->logData(array(
                    'hos_num' => $this->event->episode->patient->hos_num,
                    'clinician_name' => $this->event->user->getFullName(),
                    'letter_type' => (isset($element_letter->letterType->name) ? $element_letter->letterType->name : ''),
                    'letter_finalised_date' => $this->event->last_modified_date,
                    'letter_created_date' => $this->event->created_date,
                    'last_significant_event_date' => $lastSignificantEventDate,
                    'letter_sent_date' => date('Y-m-d H:i:s'),
                ));
            }

            return true;
        }
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    private function generateXMLOutput($filename)
    {
        $element_letter = ElementLetter::model()->findByAttributes(array("event_id" => $this->event->id));
        $subObj = $this->event->episode->firm->serviceSubspecialtyAssignment->subspecialty;
        $subspeciality = isset($subObj->ref_spec) ? $subObj->ref_spec : 'SS';
        $subspeciality_name = isset($subObj->name) ? $subObj->name : 'Support Services';
        $nat_id = isset($this->event->episode->patient->gp->nat_id) ? $this->event->episode->patient->gp->nat_id : null;
        $gp_name = isset($this->event->episode->patient->gp->contact) ? $this->event->episode->patient->gp->contact->getFullName() : null;
        $practice_code = isset($this->event->episode->patient->practice->code) ? $this->event->episode->patient->practice->code : '';
        $address = isset($this->event->episode->patient->contact->address) ? $this->event->episode->patient->contact->address->getLetterArray() : array();
        $address1 = isset($this->event->episode->patient->contact->address) ? ($this->event->episode->patient->contact->address->address1) : '';
        $city = isset($this->event->episode->patient->contact->address) ? ($this->event->episode->patient->contact->address->city) : '';
        $county = isset($this->event->episode->patient->contact->address) ? ($this->event->episode->patient->contact->address->county) : '';
        $city = isset($this->event->episode->patient->contact->address) ? ($this->event->episode->patient->contact->address->city) : '';
        $post_code = isset($this->event->episode->patient->contact->address) ? ($this->event->episode->patient->contact->address->postcode) : '';
        $letter_type = isset($element_letter->letterType->name) ? $element_letter->letterType->name : '';

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <DocumentInformation>
            <PatientNumber>" . $this->event->episode->patient->hos_num . "</PatientNumber>
            <NhsNumber>" . $this->event->episode->patient->nhs_num . "</NhsNumber>
            <Name>" . $this->event->episode->patient->contact->getFullName() . "</Name>
            <Surname>" . $this->event->episode->patient->contact->last_name . "</Surname>
            <FirstForename>" . $this->event->episode->patient->contact->first_name . "</FirstForename>
            <SecondForename></SecondForename>
            <Title>" . $this->event->episode->patient->contact->title . "</Title>
            <DateOfBirth>" . $this->event->episode->patient->dob . "</DateOfBirth>
            <Sex>" . $this->event->episode->patient->gender . "</Sex>
            <Address>" . implode(", ", $address) . "</Address>
            <AddressName></AddressName>
            <AddressNumber></AddressNumber>
            <AddressStreet>" . $address1 . "</AddressStreet>
            <AddressDistrict></AddressDistrict>
            <AddressTown>" . $city . "</AddressTown>
            <AddressCounty>" . $county . "</AddressCounty>
            <AddressPostcode>" . $post_code . "</AddressPostcode>
            <GP>" . $nat_id . "</GP>
            <GPName>" . $gp_name . "</GPName>
            <Surgery>" . $practice_code . "</Surgery>
            <SurgeryName></SurgeryName>
            <LetterType>" . $letter_type . "</LetterType>
            <ActivityID>" . $this->event->id . "</ActivityID>
            <ActivityDate>" . $this->event->event_date . "</ActivityDate>
            <ClinicianType></ClinicianType>
            <Clinician></Clinician>
            <ClinicianName></ClinicianName>
            <Specialty>" . $subspeciality . "</Specialty>
            <SpecialtyName>" . $subspeciality_name . "</SpecialtyName>
            <Location>" . $element_letter->site->short_name . "</Location>
            <LocationName>" . $element_letter->site->name . "</LocationName>
            <SubLocation></SubLocation>
            <SubLocationName></SubLocationName>
            </DocumentInformation>";

        return (file_put_contents($this->path . "/" . $filename . ".XML", $this->cleanXML($xml)) !== false);
    }

    /**
     * Special function to sanitize XML
     *
     * @param string $xml
     * @return string
     */
    private function cleanXML($xml)
    {
        return str_replace("&", "and", $xml);
    }

    /**
     * @param $output_id
     * @return bool
     */
    private function updateDelivery($output_id)
    {
        $output = DocumentOutput::model()->findByPk($output_id);
        $output->output_status = "COMPLETE";

        return $output->save();
    }
    
    private function updateFailedDelivery($output_id)
    {
        $output = DocumentOutput::model()->findByPk($output_id);
        $output->output_status = "FAILED";

        return $output->save();
    }

    /**
     * @param $data
     */
    private function logData($data)
    {
        if ($this->generate_csv) {
            $doc_log = new DocumentLog();
            $doc_log->attributes = $data;
            $doc_log->save();

            $csv_filename = implode(DIRECTORY_SEPARATOR, array($this->path, sprintf($this->csv_format, date('Ymd'))));
            $put_header = !file_exists($csv_filename);

            $fp = fopen($csv_filename, 'ab');
            if($put_header){
                fputcsv($fp, array_keys($data));
            }
            fputcsv($fp, $data);
            fclose($fp);
        }
    }
}
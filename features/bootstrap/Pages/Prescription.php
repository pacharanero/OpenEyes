<?php
use Behat\Behat\Exception\BehaviorException;


class Prescription extends OpenEyesPage {

	protected $path = "/site/OphDrPrescription/Default/create?patient_id={parentId}";

	//protected $repeatDrugCheck= "//*[@class='prescription-label']//*[@value=".repeatDrug."]";
	protected $elements = array (
		'filterBy' => array (
			'xpath' => "//*[@id='drug_type_id']"
		),
		'noPreservative' => array (
			'xpath' => "//*[@id='preservative_free']"
		),
		'prescriptionCommonDrug' => array (
			'xpath' => "//*[@id='common_drug_id']"
		),
		'prescriptionStandardSet' => array (
			'xpath' => "//*[@id='drug_set_id']"
		),
		'prescriptionDoseItem0' => array (
			//'xpath' => "//*[@id='prescription_item_0_dose']"
			'xpath' => "//*[@class='prescriptionItemDose']//*[@id='prescription_item_0_dose']"
		),
		'prescriptionRouteItem0' => array (
			'xpath' => "//*[@id='prescription_item_0_route_id']"
		),
		'prescriptionEyeOptionItem0' => array (
			'xpath' => "//*[@id='prescription_item_0_route_option_id']"
		),
		'prescriptionEyeOptionItem1' => array (
			'xpath' => "//*[@id='prescription_item_1_route_option_id']"
		),
		'prescriptionEyeOptionItem2' => array (
			'xpath' => "//*[@id='prescription_item_2_route_option_id']"
		),
		'prescriptionFrequencyItem0' => array (
			'xpath' => "//*[@id='prescription_item_0_frequency_id']"
		),
		'prescriptionDurationItem0' => array (
			'xpath' => "//*[@id='prescription_item_0_duration_id']"
		),
		'prescriptionComments' => array (
			'xpath' => "//textarea[@id='Element_OphDrPrescription_Details_comments']"
		),
		'savePrescriptionandPrint' => array (
			'xpath' => "//*[@id='et_save_print']"
		),
		'prescriptionSaveDraft' => array (
			'xpath' => "//*[@id='et_save_draft']"
		),
		'prescriptionSavedOk' => array (
			'xpath' => "//*[@id='flash-success']"
		),
		'addTaper' => array (
			'xpath' => "//*[@class='taperItem']"
		),
		'firstTaperDose' => array (
			'xpath' => "//*[@id='prescription_item_0_taper_0_dose']"
		),
		'firstTaperFrequency' => array (
			'xpath' => "//*[@id='prescription_item_0_taper_0_frequency_id']"
		),
		'firstTaperDuration' => array (
			'xpath' => "//*[@id='prescription_item_0_taper_0_duration_id']"
		),
		'secondTaperDose' => array (
			'xpath' => "//*[@id='prescription_item_0_taper_1_dose']"
		),
		'secondTaperFrequency' => array (
			'xpath' => "//*[@id='prescription_item_0_taper_1_frequency_id']"
		),
		'secondTaperDuration' => array (
			'xpath' => "//*[@id='prescription_item_0_taper_1_duration_id']"
		),
		'removeThirdTaper' => array (
			'xpath' => "//*[@data-taper='2']//*[@class='removeTaper']"
		),
		'prescriptionValidationWarning' => array (
			'xpath' => "//*[contains(text(),'Items cannot be blank.')]"
		),
		'standardSetRepeatDrug1' => array (
			'xpath' => "//*[@class='prescription-item prescriptionItem even']//*[contains(text(),'atropine 1% eye drops')]"
		),
		'standardSetRepeatDrug2' => array (
			'xpath' => "//*[@class='prescription-item prescriptionItem odd']//*[contains(text(),'chlorAMPhenicol 0.5% eye drops')]"
		),
		'standardSetRepeatDrug3' => array (
			'xpath' => "//*[@class='prescription-item prescriptionItem even']//*[contains(text(),'dexamethasone 0.1% eye drops')]"
		),
		'repeatPrescription' => array (
			'xpath' => "//*[@id='repeat_prescription']"
		),
		'previousPrescription' => array (
			'xpath' => 'repeatDrugCheck'
		),

		'prescriptionExist' => array(
            'xpath' => "//*[@class='events']//*[@class='tooltip quicklook']//*[contains(text(),'Prescription')]"
        ),

		'prescriptionHover'=>array(
			'xpath'=>"//*[@class='event-type']"
		),
		'prescriptionHoverText'=>array(
			'xpath'=>"//*[@class='events-container show']//*[contains(text(),'Prescription')]"
		),

		'deleteEvent' => array(
			'xpath' => "//*[@class=' delete event-action button button-icon small']"
		),
		'deleteEventButton' => array(
			'xpath'=> "//*[@id='et_deleteevent']"
		),
		'prescriptionExistWarning' => array(
			'xpath'=> "//*[@class='alert-box alert with-icon']//*[contains(text(),'WARNING: A Prescription has already been created for this patient today. ')]"
		),

		'prescriptionExistWarning2' => array(
			'xpath'=> "//*[@class='alert-box alert with-icon']//*[contains(text(),'WARNING: Prescriptions have already been created for this patient today.')]"
		),
		'prescriptionExistsYesOption' => array(
			'xpath'=>"//*[@id='prescription-yes']"
		),
		'prescriptionExistsNoOption' => array(
			'xpath'=>"//*[@id='prescription-no']"
		),
		'eventCreationPage' => array(
			'xpath'=>"//*[@class='selected']//*[contains(text(),'Create')]"
		),
		'eventSummaryPage'=> array(
			'xpath'=>"//*[@class='inline-list tabs event-actions']//*[contains(text(),'View')]"
		)
	)
	;
	public function filterBy($filter) {
		$this->getElement ( 'filterBy' )->selectOption ( $filter );
	}
	public function addTaper() {
		$this->getElement ( 'addTaper' )->click ();
	}
	public function firstTaperDose($taper) {
		//$this->getElement ( 'firstTaperDose' )->selectOption ( $taper );

		$this->getElement ( 'firstTaperDose' )->setValue($taper);
	}
	public function firstTaperFrequency($frequency) {
		$this->getElement ( 'firstTaperFrequency' )->selectOption ( $frequency );
	}
	public function firstTaperDuration($duration) {
		$this->getElement ( 'firstTaperDuration' )->selectOption ( $duration );
	}
	public function secondTaperDose($taper) {
		$this->getElement ( 'secondTaperDose' )->setValue ( $taper );
	}
	public function secondTaperFrequency($frequency) {
		$this->getElement ( 'secondTaperFrequency' )->selectOption ( $frequency );
	}
	public function secondTaperDuration($duration) {
		$this->getElement ( 'secondTaperDuration' )->selectOption ( $duration );
	}
	public function removeThirdTaper() {
		$element = $this->getElement ( 'removeThirdTaper' );
		$this->scrollWindowToElement ( $element );
		$element->click ();
	}
	public function noPreservativeCheckbox() {
		$this->getElement ( 'noPreservative' )->check ();
	}
	public function prescriptionDropdown($drug) {
		$this->getElement ('prescriptionCommonDrug')->selectOption ( $drug );
		//$repeatDrug=$drug;
		sleep(3);
	}
	public function standardSet($set) {
		$this->getElement ( 'prescriptionStandardSet' )->selectOption ( $set );
		$this->getSession ()->wait ( 1000 );
	}
	public function item0DoseDrops($drops) {

		$this->getElement ( 'prescriptionDoseItem0' )->setValue ( $drops );
	}
	public function item0Route($route) {
		$this->getElement ( 'prescriptionRouteItem0' )->selectOption ( $route );
	}
	public function eyeOptionItem0($eyes) {
		sleep(15);
		$this->getElement ( 'prescriptionEyeOptionItem0' )->selectOption ( $eyes );
	}
	public function eyeOptionItem1($eyes) {
		$this->getElement ( 'prescriptionEyeOptionItem1' )->selectOption ( $eyes );
	}
	public function eyeOptionItem2($eyes) {
		$this->getElement ( 'prescriptionEyeOptionItem2' )->selectOption ( $eyes );
	}
	public function frequencyItem0($frequency) {
		$this->getElement ( 'prescriptionFrequencyItem0' )->selectOption ( $frequency );
	}
	public function durationItem1($duration) {
		$this->getElement ( 'prescriptionDurationItem0' )->setValue ( $duration );
		$this->getSession ()->wait ( 1000 );
	}
	public function comments($comments) {
		$this->getElement ( 'prescriptionComments' )->setValue ( $comments );
	}
	public function repeatPrescription() {
		$this->getElement ( 'repeatPrescription' )->click ();
		$this->getSession ()->wait ( 1000 );
	}
	protected function hasPrescriptionSaved() {
		$this->waitForElementDisplayBlock ( 'prescriptionSavedOk' );
		return ( bool ) $this->find ( 'xpath', $this->getElement ( 'prescriptionSavedOk' )->getXpath () );
	}
	public function savePrescriptionAndConfirm() {
		$this->getElement ( 'prescriptionSaveDraft' )->click ();

		if ($this->hasPrescriptionSaved ()) {
			print "Prescription has been saved OK";
		}

		else {
			throw new BehaviorException ( "WARNING!!!  Prescription has NOT been saved!!  WARNING!!" );
		}
	}
	public function savePrescription() {
		$this->getElement ( 'prescriptionSaveDraft' )->click ();

	}
	protected function doesPrescriptionValidationExist() {
		$this->waitForElementDisplayBlock ( '.alert-box.alert.with-icon ul' );
		return ( bool ) $this->find ( 'xpath', $this->getElement ( 'prescriptionValidationWarning' )->getXpath () );
	}
	public function confirmPrescriptionValidationError() {
		if ($this->doesPrescriptionValidationExist ()) {
			print "Validation error is displayed OK";
		} else {
			throw new BehaviorException ( "WARNING!!! NO Please fix the following input errors WARNING!!!" );
		}
	}
	protected function hasRepeatPrescriptionBeenApplied() {
		return ( bool ) $this->find ( 'xpath', $this->getElement ( 'standardSetRepeatDrug1' )->getXpath () ) && ( bool ) $this->find ( 'xpath', $this->getElement ( 'standardSetRepeatDrug2' )->getXpath () ) && ( bool ) $this->find ( 'xpath', $this->getElement ( 'standardSetRepeatDrug3' )->getXpath () );
	}
	public function repeatPrescriptionCheck() {
		if ($this->hasRepeatPrescriptionBeenApplied ()) {
			print "Repeat Prescription has been applied OK";
		}

		else {
			throw new BehaviorException ( "WARNING!!!  Repeat Prescription has NOT been applied!!  WARNING!!" );
		}
	}

	protected function isPreviousPrescriptionChecked() {
		return ( bool ) $this->find ( 'xpath', $this->getElement ( '' )->getXpath () );
	}
	public function previousPrescriptionCheck(){
		if ($this->isPreviousPrescriptionChecked()){
			print "Repeat Prescription Applied!";
		}
		else{
			throw new BehaviorException ("WARNING!! PREVIOUS PRESCRIPTION NOT APPLIED!!!");
		}
	}

	public function removePrescriptionEvents(){
		sleep(3);
		$this->prescriptionExists();
	}


	protected function prescriptionExists(){
		//if($this->find ( 'xpath', $this->getElement ( 'prescriptionExist' )->getXpath () )){
		$this->getElement('prescriptionHover')->mouseOver();

		if($this->getElement ( 'prescriptionHoverText' )->isVisible()){
			$this->deletePrescription();
			sleep(5);
			$this->removePrescriptionEvents();
		}
		else{
			//return false;
			print "No prescriptions!! Creating now...";
			//throw new BehaviorException ( "No prescriptions!! Creating now..." );
		}
	}

	protected function deletePrescription(){
			$this->getElement ( 'prescriptionHover' )->click ();

		sleep(3);
		$this->getElement('deleteEvent')->click();
		sleep(2);
		$this->getElement('deleteEventButton')->click();
	}

	public function checkWarningShown(){
		sleep(3);
		if ($this->doesWarningShow()||$this->doesWarningShow2()) {
			if($this->getElement('prescriptionExistsYesOption')->getXpath()&&$this->getElement('prescriptionExistsNoOption')->getXpath()){
				print "Warning is displayed OK";

			}
			else{
				print "Yes/No options missing on the warning!";
			}
		} else {
			throw new BehaviorException ( "NO WARNING SHOWN!!! TEST FAILED!" );
		}
	}

	protected function doesWarningShow(){
		return ( bool ) ($this->find ( 'xpath', $this->getElement ( 'prescriptionExistWarning' )->getXpath () ) );
	}

	protected function doesWarningShow2(){
		return ( bool ) ($this->find ( 'xpath', $this->getElement ( 'prescriptionExistWarning2' )->getXpath () ) );
	}

	public function iClickOnYes(){
		$this->getElement('prescriptionExistsYesOption')->click();
		sleep(3);
		if($this->iAmOnCreateEventPage()&&$this->iAmOnPrescriptionPage()){
			print "Test Passed!!";
		}
		else{
			print "User not redirected to Prescription Creation Page! TEST FAILED!!";
		}
	}

	public function iClickOnNo(){
		$this->getElement('prescriptionExistsNoOption')->click();
		sleep(3);
		if($this->iAmOnLatestEventPage()){
			print "Test Passed!!";
		}
		else{
			print "User not redirected to Latest Event Summary Page! TEST FAILED!!";
		}
	}

	protected function iAmOnCreateEventPage(){
		return ( bool ) $this->find ( 'xpath', $this->getElement ( 'eventCreationPage' )->getXpath () );
	}

	protected function iAmOnPrescriptionPage(){
		return ( bool ) $this->find('xpath',$this->getElement('repeatPrescription')->getXpath());
	}

	protected function iAmOnLatestEventPage(){
		return ( bool ) $this->find ( 'xpath', $this->getElement ( 'eventSummaryPage' )->getXpath () );
	}

    public function duplicatePrescriptionOk() {
        $this->popupOk('duplicatePrescriptionOk');
    }
}
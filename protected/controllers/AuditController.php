<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class AuditController extends BaseController
{
	/**
		* @var string the default layout for the views. Defaults to '//layouts/column2', meaning
		* using two-column layout. See 'protected/views/layouts/column2.php'.
		*/
	public $layout='//layouts/main';
	public $items_per_page = 100;

	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'users'=>array('@')
			),
			// non-logged in can't view anything
			array('deny',
				'users'=>array('?')
			),
		);
	}

	public function actionIndex()
	{
		$actions = array();

		foreach (array('add-allergy','associate-contact','change-firm','change-status','create','delete','login-failed','login-successful','logout','print','remove-allergy','reschedule','search-error','search-results','unassociate-contact','update','view') as $field) {
			$actions[$field] = $field;
		}

		$targets = array();

		foreach (array('booking','diary','episode','episode summary','event','login','logout','patient','patient summary','search','session','user','waiting list') as $field) {
			$targets[$field] = $field;
		}

		$criteria = new CDbCriteria;
		$criteria->distinct = true;
		$criteria->compare('created_date','>= '.date('Y-m-d').' 00:00:00',false);
		$criteria->compare('action','login-successful');
		$criteria->select = 'data';

		$unique_users = Audit::model()->count($criteria);

		$criteria->distinct = false;

		$total_logins = Audit::model()->count($criteria);

		$this->render('index',array('actions'=>$actions,'targets'=>$targets,'unique_users'=>$unique_users,'total_logins'=>$total_logins));
	}

	public function actionSearch() {
		if (isset($_POST['page'])) {
			$data = $this->getData($_POST['page']);
		} else {
			$data = $this->getData();
		}

		Yii::app()->clientScript->registerScriptFile('/js/audit.js');
		$this->renderPartial('_list', array('data' => $data), false, true);
		echo "<!-------------------------->";
		$this->renderPartial('_pagination', array('data' => $data), false, true);
	}

	public function criteria() {
		$criteria = new CDbCriteria;

		if (@$_REQUEST['site_id']) {
			$criteria->addCondition('site_id='.$_REQUEST['site_id']);
		}

		if (@$_REQUEST['firm_id']) {
			$firm = Firm::model()->findByPk($_REQUEST['firm_id']);
			$firm_ids = array();
			foreach (Firm::model()->findAll('name=?',array($firm->name)) as $firm) {
				$firm_ids[] = $firm->id;
			}
			if (!empty($firm_ids)) {
				$criteria->addInCondition('firm_id',$firm_ids);
			}
		}

		if (@$_REQUEST['user_id']) {
			$criteria->addCondition('user_id='.$_REQUEST['user_id']);
		}

		if (@$_REQUEST['action']) {
			$criteria->addCondition("action='".$_REQUEST['action']."'");
		}

		if (@$_REQUEST['target_type']) {
			$criteria->addCondition("target_type='".$_REQUEST['target_type']."'");
		}

		if (@$_REQUEST['event_type']) {
			$criteria->addCondition('event_type_id='.$_REQUEST['event_type']);
		}

		if (@$_REQUEST['date_from']) {
			$date_from = Helper::convertNHS2MySQL($_REQUEST['date_from']).' 00:00:00';
			$criteria->addCondition("created_date >= '$date_from'");
		}

		if (@$_REQUEST['date_to']) {
			$date_to = Helper::convertNHS2MySQL($_REQUEST['date_to']).' 23:59:59';
			$criteria->addCondition("created_date <= '$date_to'");
		}

		if (@$_REQUEST['hos_num']) {
			if ($patient = Patient::model()->find('hos_num=?',array(@$_REQUEST['hos_num']))) {
				$criteria->addCondition('patient_id='.$patient->id);
			} else {
				$criteria->addCondition('patient_id=0');
			}
		}

		return $criteria;
	}

	public function getData($page=1) {
		$criteria = $this->criteria();

		$data = array();
		
		$data['total_items'] = Audit::model()->count($criteria);

		$criteria->order = 'id desc';
		$criteria->offset = (($page-1) * $this->items_per_page);
		$criteria->limit = $this->items_per_page;

		$data['items'] = Audit::model()->findAll($criteria);
		$data['pages'] = ceil($data['total_items'] / $this->items_per_page);
		$data['page'] = $page;

		return $data;
	}

	public function getDataFromId($id) {
		$criteria = $this->criteria();

		$data = array();

		$data['total_items'] = Audit::model()->count($criteria);

		$criteria->order = 'id desc';
		$criteria->limit = $this->items_per_page;
		$criteria->addCondition('id > '.(integer)$id);

		$data['items'] = Audit::model()->findAll($criteria);
		$data['pages'] = ceil($data['total_items'] / $this->items_per_page);

		return $data;
	}

	public function actionUpdateList() {
		if (!$audit = Audit::model()->findByPk(@$_GET['last_id'])) {
			throw new Exception('Log entry not found: '.@$_GET['last_id']);
		}

		$this->renderPartial('_list_update', array('data' => $this->getDataFromId($audit->id)), false, true);
	}
}
?>

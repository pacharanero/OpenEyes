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

class BaseEventTypeCActiveForm extends CActiveForm
{
	public function dropDownList($model,$field,$data,$htmlOptions=array()) {
		$this->widget('application.widgets.DropDownList',array('element' => $model, 'field' => $field, 'data' => $data, 'htmlOptions' => $htmlOptions));
	}

	public function radioButtons($element,$field,$table,$selected_item=false, $maxwidth=false) {
		$data = $element->getFormOptions($table);
		$this->widget('application.widgets.RadioButtonList',array('element' => $element, 'name' => get_class($element)."[$field]", 'field' => $field, 'data' => $data, 'selected_item' => $selected_item, 'maxwidth' => $maxwidth));
	}

	public function radioBoolean($element,$field) {
		$this->widget('application.widgets.RadioButtonList',array('element' => $element, 'name' => get_class($element)."[$field]", 'field' => $field, 'data' => array(1=>'Yes',0=>'No')));
	}

	public function datePicker($element,$field,$options,$htmlOptions) {
		$this->widget('application.widgets.DatePicker',array('element' => $element, 'name' => get_class($element)."[$field]", 'field' => $field, 'options' => $options, 'htmlOptions' => $htmlOptions));
	}

	public function textArea($element,$field,$options=array()) {
		if (!isset($options['rows'])) {
			throw new SystemException('textArea requires the rows option to be specified');
		}
		if (!isset($options['cols'])) {
			throw new SystemException('textArea requires the cols option to be specified');
		}

		$this->widget('application.widgets.TextArea',array_merge(array('element' => $element, 'field' => $field), $options));
	}

	public function textField($element,$field,$htmlOptions=array()) {
		$this->widget('application.widgets.TextField',array('element' => $element, 'name' => get_class($element)."[$field]", 'field' => $field, 'htmlOptions' => $htmlOptions));
	}

	public function checkBox($element, $field, $options=false) {
		$this->widget('application.widgets.CheckBox',array('element' => $element, 'field' => $field, 'options' => $options));
	}

	public function checkBoxArray($element,$label,$fields, $options=false) {
		$this->widget('application.widgets.CheckBoxArray',array('element' => $element, 'fields' => $fields, 'label' => $label, 'options' => $options));
	}

	public function multiSelectList($element, $fields, $htmlOptions=array()) {
		$this->widget('application.widgets.MultiSelectList', array('element' => $element, 'fields' => $fields, 'htmlOptions' => $htmlOptions));
	}
}

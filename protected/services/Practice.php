<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

namespace services;

class Practice extends Resource
{
    protected static $fhir_type = 'Organization';
    protected static $fhir_prefix = 'prac';

    public $code;

    public $primary_phone;
    public $address = null;

    protected static function getFhirTemplate()
    {
        return \DataTemplate::fromJsonFile(
            __DIR__.'/fhir_templates/Practice.json',
            array(
                'system_uri_practice_code' => \Yii::app()->params['fhir_system_uris']['practice_code'],
            )
        );
    }
}

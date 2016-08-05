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
?>


<div class="pastintervention-view panel previous-interventions">
	<div class="row data-row">
		<div class="large-6 column">
			<div class="data-label">
				<?php echo $pastintervention->getAttributeLabel('start_date'); ?>:
			</div>
		</div>
		<div class="large-6 column">
			<div class="data-value">
				<?php echo Helper::convertMySQL2NHS($pastintervention->start_date) ?>
			</div>
		</div>
	</div>

	<div class="row data-row">
		<div class="large-6 column">
			<div class="data-label">
				<?php echo $pastintervention->getAttributeLabel('end_date'); ?>:
			</div>
		</div>
		<div class="large-6 column">
			<div class="data-value">
				<?php echo Helper::convertMySQL2NHS($pastintervention->end_date) ?>
			</div>
		</div>
	</div>

	<div class="row data-row">
		<div class="large-6 column">
			<div class="data-label">
				<?php echo $pastintervention->getAttributeLabel('treatment_id'); ?>:
			</div>
		</div>
		<div class="large-6 column">
			<div class="data-value">
				<?php echo $pastintervention->getTreatmentName() ?>
			</div>
		</div>
	</div>

	<div class="row data-row">
		<div class="large-6 column">
			<div class="data-label">
				<?php echo $pastintervention->getAttributeLabel('start_va'); ?>:
			</div>
		</div>
		<div class="large-6 column">
			<div class="data-value">
				<?php echo $pastintervention->start_va ?>
			</div>
		</div>
	</div>

	<div class="row data-row">
		<div class="large-6 column">
			<div class="data-label">
				<?php echo $pastintervention->getAttributeLabel('end_va'); ?>:
			</div>
		</div>
		<div class="large-6 column">
			<div class="data-value">
				<?php echo $pastintervention->end_va ?>
			</div>
		</div>
	</div>

	<div class="row data-row">
		<div class="large-6 column">
			<div class="data-label">
				<?php echo $pastintervention->getAttributeLabel('stopreason_id'); ?>:
			</div>
		</div>
		<div class="large-6 column">
			<div class="data-value">
				<?php if ($pastintervention->stopreason_other) {
                    echo Yii::app()->format->Ntext($pastintervention->stopreason_other);
                } else {
                    echo $pastintervention->stopreason->name;
                } ?>
			</div>
		</div>
	</div>

	<div class="row data-row">
		<div class="large-12 column">
			<div class="data-label data-row">
				<?php echo $pastintervention->getAttributeLabel('comments'); ?>:
			</div>
			<div class="data-value comments">
				<?php if ($pastintervention->comments) {
                    echo Yii::app()->format->Ntext($pastintervention->comments);
                } else {
                    echo 'None';
                }?>
			</div>
		</div>
	</div>
</div>

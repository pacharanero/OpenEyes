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
<section class="element <?php echo $element->elementType->class_name ?>"
         data-element-type-id="<?php echo $element->elementType->id ?>"
         data-element-type-class="<?php echo $element->elementType->class_name ?>"
         data-element-type-name="<?php echo $element->elementType->name ?>"
         data-element-display-order="<?php echo $element->elementType->display_order ?>">
  <input type="hidden" name="<?php echo CHtml::modelName($element); ?>[force_validation]"/>
  <fieldset class="element-fields">
    <div class="row field-row">
      <div class="large-3 column">
        <label>Tests:</label>
      </div>
      <div class="large-9 column">
        <table>
          <thead>
          <tr>
            <th>Date</th>
            <th>Study</th>
            <th>Volume</th>
            <th>Withdrawn by</th>
            <th></th>
          </tr>
          </thead>
          <tbody class="transactions">
          <?php if (!empty($_POST)) {
              $transactions = $this->getFormTransactions();
          } else {
              $transactions = $element->transactions;
          }

          if ($transactions) {
              foreach ($transactions as $i => $transaction) {
                  $disabled = !$this->checkAccess('TaskEditGeneticsWithdrawals');
                  $this->renderPartial('_dna_test', array('transaction' => $transaction, 'i' => $i, 'disabled' => $disabled));
              }
          } else { ?>
            <tr>
              <td id="no-tests" colspan="4">
                No tests have been logged for this DNA.
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
        <button class="button small secondary addTest">
          Add
        </button>
      </div>
    </div>
  </fieldset>
</section>
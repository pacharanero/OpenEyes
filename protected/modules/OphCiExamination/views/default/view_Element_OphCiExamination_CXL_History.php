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
<div class="element-data">
    <div class="data-row">
        <div class="data-value">
            <?php echo $element->getAttributeLabel('asthma_id')?>:
            <?php
            $asthma = $element->asthma_id;
            if($asthma == 1){
                echo "Yes";
            }else{
                echo "No";
            }
            ?><br/>
            <?php echo $element->getAttributeLabel('eczema_id')?>:
            <?php
            $eczema = $element->eczema_id;
            if($eczema == 1){
                echo "Yes";
            }else{
                echo "No";
            }
            ?><br/>
            <?php echo $element->getAttributeLabel('hayfever_id')?>:
            <?php
            $hayfever_id = $element->hayfever_id;
            if($hayfever_id == 1){
                echo "Yes";
            }else{
                echo "No";
            }
            ?><br/>
        </div>
    </div>
</div>
<div class="element-data element-eyes row">
    <div class="element-eye right-eye column">
        <?php if ($element->hasRight()) {?>

            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('right_previous_cxl_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php echo $element->right_previous_cxl_value;
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('right_previous_refractive_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php
                    echo $element->right_previous_refractive_value;
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('right_intacs_kera_ring_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php
                    echo $element->right_intacs_kera_ring_value;
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('right_trans_prk_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php
                    echo $element->right_trans_prk_value;
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('right_previous_hsk_keratitis_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php
                    echo $element->right_previous_hsk_keratitis_value;
                    ?>
                </div>
            </div>
<?php
                } else {
                    ?>
                    Not recorded
                    <?php
                }?>
        </div>

    <div class="element-eye left-eye column">
        <?php if ($element->hasLeft()) {?>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('left_previous_cxl_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php echo $element->left_previous_cxl_value;
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('left_previous_refractive_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php
                    echo $element->left_previous_refractive_value;
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('left_intacs_kera_ring_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php
                    echo $element->left_intacs_kera_ring_value;
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('left_trans_prk_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php
                    echo $element->left_trans_prk_value;
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="large-6 column data-value">
                    <?php echo $element->getAttributeLabel('left_previous_hsk_keratitis_value')?>:
                </div>
                <div class="large-5 column data-value">
                    <?php
                    echo $element->left_previous_hsk_keratitis_value;
                    ?>
                </div>
            </div>
        <?php
        } else {
            ?>
            Not recorded
            <?php
        }?>
    </div>
</div>
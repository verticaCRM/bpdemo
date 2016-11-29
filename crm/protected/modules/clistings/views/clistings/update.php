<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

include("protected/modules/clistings/clistingsConfig.php");

/*Yii::app()->clientScript->registerScript('mask-currency', '
    $("#Clistings_c_ownerscashflow").maskMoney("mask");
', CClientScript::POS_READY);
*/

Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl.'/js/clistings.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/clistingsUpdateForm.css');

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('create')),
	array('label'=>Yii::t('module','View {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Edit {X}',array('{X}'=>Modules::itemDisplayName()))),
	array('label'=>Yii::t('module','Delete {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
));
?>
<div class="page-title"><h2><?php echo Yii::t('module','Update {X}',array('{X}'=>Modules::itemDisplayName())); ?> <?php echo $model->name; ?></h2></div>

<?php echo $this->renderPartial('application.components.views._form', array('model'=>$model,'users'=>$users, 'modelName'=>'clistings')); ?>

<script><?php echo Yii::app()->clientScript->getCurrencyConfigScript (); ?></script>


<?php
/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::import ('application.extensions.unique-attributes-validator.UniqueAttributesValidator');
Yii::import('application.components.NormalizedJSONFieldsBehavior');

/**
 * This is the model class for table "x2_chart_settings".
 * @package application.models
 */
class ChartSetting extends CActiveRecord {

	/**
	 * Returns the static model of the specified AR class.
	 * @return Imports the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_chart_settings';
	}

    public function behaviors(){
        return array(
            'NormalizedJSONFieldsBehavior' => array(
                'class' => 'application.components.NormalizedJSONFieldsBehavior',
                'transformAttributes' => array('settings' => array(
                    'startDate', 'endDate', 'binSize', 'firstMetric', 'visibilityFilter',
					'usersFilter', 'eventsFilter', 'socialSubtypesFilter',
					'dateRange'))
            )
        );
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('name', 'required'),
			array('chartType', 'required'),
            array('name', 'UniqueAttributesValidator', 'with'=>'userId,name'),
            array('name', 'length', 'max' => 25)
        );
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'name' => Yii::t('app','Setting Name'),
			'settings' => Yii::t('app','Setting'),
		);
	}

}


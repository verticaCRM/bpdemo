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

/**
 * Handles relationship grid attribute rendering and filtering
 */

class BuyersPortfolioModel extends CModel {

    /**
     * @var CActiveRecord $relatedModel
     */
    public $relatedModel = 'clistings';

    /**
     * @var string $myModelName
     */
    public $myModel = 'clistings';

    /**
     * @var int $id
     */
    public $id;

    /**
     * Added these to fix the readonly error
     */
    public $name;

    public $assignedTo;

    public $description;

    public $c_release_status;

    public $c_is_hidden;

    public $c_listing_city_c;

    public $c_listing_town_c;

    public $c_listing_region_c;

    public $c_listing_address_c;

    public $createDate;

    public $c_date_released;

    public $c_sales_stage;

    public $c_name_dba_c;

    public $c_listing_id;

    public $c_listing_askingprice_c;

    public $c_businesscategories;

    public $c_financial_net_cashflow_c;
    public $c_financial_officersalary_c;
    public $c_financial_ownerhealthins_c;
    public $c_financial_businessloans_c;
    public $c_financial_addback_interest_c;
    public $c_financial_ownercc_c;
    public $c_financial_ownercell_c;
    public $c_financial_ownerlease_c;
    public $c_financial_fuelvehicle_c;
    public $c_financial_other_income_c;

    public function __construct ($scenario=null) {
        if ($scenario) {
            $this->setScenario ($scenario);
        }
        if ($scenario === 'search' && isset ($_GET[get_called_class ()])) {
            $this->setAttributes ($_GET[get_called_class ()], false);
        }
    }

    public function attributeNames () {

        return array (
            'name',
            'assignedTo',
            'description',
            'createDate',
            'c_date_released',
            'c_release_status',
            'c_is_hidden',
            'c_listing_city_c',
            'c_listing_town_c',
            'c_listing_region_c',
            'c_financial_net_cashflow_c',
            'c_listing_askingprice_c',
            'c_businesscategories',
            'c_sales_stage',
            'c_listing_id',
            'c_name_dba_c',
            'c_financial_officersalary_c',
            'c_financial_ownerhealthins_c',
            'c_financial_businessloans_c',
            'c_financial_addback_interest_c',
            'c_financial_ownercc_c',
            'c_financial_ownercell_c',
            'c_financial_ownerlease_c',
            'c_financial_fuelvehicle_c',
            'c_financial_other_income_c'
        );
    }

    public function getName () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->name;
    }

    public function getDescription () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->description;
    }

    public function getLabel () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->getRelationshipLabel (
            $this->myModel->id, get_class ($this->myModel));
    }

    public function getCreateDate () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->createDate;
    }

    public function getAssignedTo () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->assignedTo;
    }

    public function getReleaseStatus () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->c_release_status;
    }

    public function getHiddenStatus () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->c_is_hidden;
    }

    public function getListingCity () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->c_listing_city_c;
    }

    public function renderAttribute ($name) {
        switch ($name) {
            case 'name':
                echo $this->relatedModel->link;
                break;
            case 'assignedTo':
                echo $this->relatedModel->renderAttribute ('assignedTo');
                break;
            case 'c_release_status':
                echo $this->relatedModel->renderAttribute ('c_release_status');
                break;
            case 'c_date_released':
                echo $this->relatedModel->renderAttribute ('c_date_released');
                break;
            case 'c_is_hidden':
                echo $this->relatedModel->renderAttribute ('c_is_hidden');
                break;
            case 'description':
                echo $this->relatedModel->renderAttribute ('description');
                break;
            case 'c_listing_city_c':
                echo $this->relatedModel->renderAttribute ('c_listing_city_c');
                break;
            case 'c_listing_town_c':
                echo $this->relatedModel->renderAttribute ('c_listing_town_c');
                break;
            case 'c_listing_region_c':
                echo $this->relatedModel->renderAttribute ('c_listing_region_c');
                break;
            case 'c_financial_net_cashflow_c':
                echo $this->relatedModel->renderAttribute ('c_financial_net_cashflow_c');
                break;
            case 'c_listing_askingprice_c':
                echo $this->relatedModel->renderAttribute ('c_listing_askingprice_c');
                break;
            case 'c_businesscategories':
                echo $this->relatedModel->renderAttribute ('c_businesscategories');
                break;
            case 'c_sales_stage':
                echo $this->relatedModel->renderAttribute ('c_sales_stage');
                break;
            case 'c_listing_id':
                echo $this->relatedModel->renderAttribute ('c_listing_id');
                break;
            case 'c_name_dba_c':
                echo $this->relatedModel->renderAttribute ('c_name_dba_c');
                break;
            case 'createDate':
                echo X2Html::dynamicDate ($this->relatedModel->createDate);
                break;
        }
    }



    public function getItems($result_type = false){
        $sql =
            'SELECT id, name, nameId, createDate, assignedTo, description, c_listing_city_c, c_listing_town_c, c_listing_region_c, c_financial_net_cashflow_c, c_listing_askingprice_c, c_businesscategories, c_date_entered
            FROM x2_clistings
            ORDER BY name ASC';

        $command = Yii::app()->db->createCommand($sql);
        $result = $command->queryAll();

        if ($result_type == 'json'){
            echo CJSON::encode($result); exit;
        }
        else
        {
            return $result;
        }
    }

    public function getListingItem($listingId){
        $sql =
            'SELECT id, name, nameId, createDate, assignedTo, description, c_listing_city_c, c_listing_town_c, c_listing_region_c, c_financial_net_cashflow_c, c_listing_askingprice_c, c_businesscategories, c_date_entered
            FROM x2_clistings
            WHERE id
            LIKE :qterm
            ORDER BY name ASC';

        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(":qterm", $listingId, PDO::PARAM_STR);
        $result = $command->queryAll();

        return $result;
    }

    public function filterModels (array $gridModels) {
        $filteredModels = array ();
        $that = $this;
        $filters = array_filter ($this->attributeNames (), function ($a) use ($that) {
            return $that->$a !== '' && $that->$a !== null;
        });

        foreach ($gridModels as $model) {
            $filterOut = false;
            foreach ($filters as $filter) {
                $val = $this->$filter;
                switch ($filter) {
                    case 'name':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('name'));
                        break;
                    case 'description':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('description'));
                        break;
                    case 'relatedModelName':
                        $filterOut = $val !== get_class ($model->relatedModel);
                        break;
                    case 'assignedTo':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('assignedTo.fullName'));
                        break;
                    case 'c_is_hidden':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_is_hidden'));
                        break;
                    case 'c_listing_id':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_listing_id'));
                        break;
                    case 'c_release_status':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_release_status'));
                        break;
                    case 'c_listing_city_c':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Clisings->getAttribute ('c_listing_city_c'));
                        break;
                    case 'c_listing_town_c':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Clisings->getAttribute ('c_listing_town_c'));
                        break;
                    case 'c_listing_region_c':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Clisings->getAttribute ('c_listing_region_c'));
                        break;
                    case 'c_financial_net_cashflow_c':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Clisings->getAttribute ('c_financial_net_cashflow_c'));
                        break;
                    case 'c_listing_askingprice_c':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Clisings->getAttribute ('c_listing_askingprice_c'));
                        break;
                    case 'c_name_dba_c':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Clisings->getAttribute ('c_name_dba_c'));
                        break;
                    case 'c_sales_stage':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Clisings->getAttribute ('c_sales_stage'));
                        break;
                    case 'label':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getRelationshipLabel (
                                $this->myModel->id, get_class ($this->myModel)));
                        break;
                    case 'createDate':
                        $timestampA = Formatter::parseDate ($val);
                        $timestampB = Formatter::parseDate (
                            $model->relatedModel->getAttribute ('createDate'));
                        $filterOut = $timestampA !== $timestampB;
                        break;
                }
                if ($filterOut) break;
            }
            if (!$filterOut)
                $filteredModels[] = $model;
        }
        return $filteredModels;
    }

    public function sortModels (array $gridModels, $sortKey) {
        if (!isset ($_GET[$sortKey])) return;
        $sortOrder = explode ('.', $_GET[$sortKey]);
        if (count ($sortOrder) > 1) $direction = $sortOrder[1];
        else $direction = 'asc';
        $sortAttr = $sortOrder[0];
        @usort ($gridModels, function ($a, $b) use ($sortAttr, $direction) {
            if ($a->$sortAttr < $b->$sortAttr) {
                return ($direction === 'asc' ? -1 : 1);
            } elseif ($a->$sortAttr > $b->$sortAttr) {
                return ($direction === 'asc' ? 1 : -1);
            } else {
                return 0;
            }
        });
        return $gridModels;
    }
    public function getListingFiles($listingId, $status = false){
        $private = 0;
        if ($status)
        {
            switch ($status)
            {
                case 'private':
                    $private = 1;
                    break;
                case 'public':
                    $private = 0;
                    break;
                default:
                    $private = 0;
                    break;
            }
        }


        // $result = $wpdb->get_results( "SELECT * FROM x2_actions LEFT JOIN x2_action_text ON x2_actions.id=x2_action_text.id WHERE x2_actions.associationType='clistings' AND x2_actions.type='attachment' AND x2_actions.associationId='".$searchlisting->id."' ORDER BY x2_actions.id DESC LIMIT 1" );

        $sql =
            "SELECT DISTINCT x2_media.id, x2_media.associationType, x2_media.associationId, x2_media.uploadedBy,x2_media.fileName, x2_media.name, x2_media.nameId, x2_media.createDate, x2_media.lastUpdated, x2_media.private, x2_media.description, x2_media.mimetype, x2_media.filesize, x2_media.dimensions, x2_media.drive, x2_media.thumbnail
            FROM x2_media 
            LEFT JOIN x2_actions on (x2_actions.associationId = x2_media.associationId) 
            WHERE x2_media.associationId = ".abs(intval($listingId))." AND 
                x2_actions.id = ( SELECT x2_action_text.id FROM x2_action_text WHERE x2_action_text.text LIKE CONCAT('%:', x2_media.id ,'%') 
                                    )";
        if ($status)
        {
            $sql .= ' AND private = '.abs(intval($private));
        }
        $sql .= ' ORDER BY name ASC ';

        $command = Yii::app()->db->createCommand($sql);
        $result = $command->queryAll();

        return $result;
    }
    public function saveBuyerListingFiles($data=array(), $portfolioId = false, $listingId = false, $buyerId = false){

        if (empty($data))
        {
            return false;
        }
        else
        {
            //delete all existing media for this portfolio and add them again
            $sql =
                'DELETE FROM x2_portfolio_to_media
                    WHERE portfolio_id = "'.abs(intval($portfolioId)).'" ';
            Yii::app()->db->createCommand($sql)->execute();

            foreach ($data as $mediaItem)
            {
                $private_date = ($mediaItem['private_date'] != '')? date("Y-m-d", strtotime($mediaItem['private_date'])) : '';
                $sql =
                    'INSERT INTO x2_portfolio_to_media (portfolio_id,media_id, private, private_end_date, listing_id, buyer_id )
                     VALUES ("'.abs(intval($portfolioId)).'", "'.abs(intval($mediaItem['file_id'])).'", "'.abs(intval($mediaItem['private'])).'", "'.$private_date.'", "'.abs(intval($listingId)).'", "'.abs(intval($buyerId)).'")
                     ';
                Yii::app()->db->createCommand($sql)->execute();
            }

            return true;
        }
    }
    public function getBuyerListingFiles($mediaId = false, $portfolioId = false, $listingId = false, $buyerId = false, $status = false){

        $private = 0;
        if ($status)
        {
            switch ($status)
            {
                case 'private':
                    $private = 1;
                    break;
                case 'public':
                    $private = 0;
                    break;
                default:
                    $private = 0;
                    break;
            }
        }
        $sql =
            'SELECT id, portfolio_id, media_id, private, private_end_date, listing_id, buyer_id
            FROM x2_portfolio_to_media
            WHERE 1=1 ';
        if ($status)
        {
            $sql .= ' AND private = '.abs(intval($private));
        }
        if ($mediaId)
        {
            $sql .= ' AND media_id = '.abs(intval($mediaId));
        }
        if ($portfolioId)
        {
            $sql .= ' AND portfolio_id = '.abs(intval($portfolioId));
        }
        if ($listingId)
        {
            $sql .= ' AND listing_id = '.abs(intval($listingId));
        }
        if ($buyerId)
        {
            $sql .= ' AND buyer_id = '.abs(intval($buyerId));
        }
        $sql .= ' ORDER BY id DESC ';

        $command = Yii::app()->db->createCommand($sql);
        $result = $command->queryAll();

        return $result;
    }

}

?>



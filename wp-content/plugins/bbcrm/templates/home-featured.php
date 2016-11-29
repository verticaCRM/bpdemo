<?php
global $apiserver, $a;

$searchparams = '';
$searchparams .= ($a['franchise']==true)?"c_listing_franchise_c=Yes;":'c_listing_franchise_c=No;';
$searchparams .= ($a['featured']==true)?"c_listing_featured_c=1;":'';
$searchparams .= (!empty($a['broker']))?"c_assigned_user_id=".$a["broker"].";":'';

//echo 'Clistings/by:c_sales_stage=Active;'.$searchparams.".json";   // $searchparams;
$json = x2apicall(array('_class'=>'Clistings/by:c_sales_stage=Active;'.$searchparams.".json"));
$featured_listings =json_decode($json);

//var_dump($searchparams);
//var_dump($featured_listings->directUris);
//var_dump($a);
///...sometimes we have more than one
//echo ($a['num']>0)."::";
if(is_array($featured_listings->directUris)){
	if(intval($a['num'])>0){
$featuredlistings = array_rand($featured_listings->directUris,intval($a['num']));
}else{
$featuredlistings = array_keys($featured_listings->directUris);
}
$single=false;
}else{

///...and sometimes we have only one
//if(!$featured_listings->directUris){ //singleton
$listing = $featured_listings; //one object
$featuredlistings = array(0=>1);
$single=true;
}

//print_r($featuredlistings);

?>
 <div class="wpp_row_view wpp_property_view_result" style="margin:0 auto; vertical-align:top !important; display: flex;">

<?
foreach ($featuredlistings as $idx=>$val){

if(!$single){
$json = x2apicall(array('_url'=>$featured_listings->directUris[$val]));
$listing = json_decode($json);
//print_r($listing);
}
$json = x2apicall(array('_class'=>'Media/by:description=thumbnail;associationId='.$listing->id.'.json'));
$thumbnail = json_decode($json);
//print_r($thumbnail);
		
$home_propertycss=($listing->c_listing_exclusive_c)?'home_exclusive':'home_featured';
?>
    <div class="property_div clearfix <?php echo $home_propertycss;?>" style="margin-right: 12px;
    width: 230px;
    border: 1px solid #ddd;
    margin-bottom: 18px;
    display: inline-block;">
                                
<?php
$listingtxt = ($a['franchise']==true)?__("Franchise","bbcrm"):__("Listing","bbcrm");
	if($listing->c_listing_exclusive_c):	?>
		<div style="background-color: #f9b854;
    width: 230px;
    padding: 3px 6px;
    text-align: center;
    font-weight: 700;"  class="homepage-exclusive-listing-header"><?php _e("Premiere",'bbcrm'); echo " ".$listingtxt; ?></div>
	<?php else:?>        
		<div style="background-color: #f9b854;
    width: 230px;
    padding: 3px 6px;
    text-align: center;
    font-weight: 700;" class="homepage-featured-listing-header"><?php _e("Featured",'bbcrm');echo " ".$listingtxt; ?></div>
	<?php endif;?>
	<div id='thumbdiv' style="width:230px;height:100px;overflow:hidden">
<?php 

if(!$thumbnail->fileName){
			echo '<a href="/listing/'.sanitize_title($listing->c_name_generic_c).'" class="listing_link" data-id="'.$listing->id.'"><img src="'.plugin_dir_url(__DIR__).'images/noimage.png"></a>';
		}else{
			echo '<a href="/listing/'.sanitize_title($listing->c_name_generic_c).'" class="listing_link" data-id="'.$listing->id.'"><img src="'.get_bloginfo('url').'/crm/uploads/media/'.$thumbnail->uploadedBy.'/'.$thumbnail->fileName.'" style="width:230px" /></a>';	

		}  

?>

</div>
<!-- Here is the new div--><div style="width:230px;" class="featured_under_image">

<h4  style="text-align: center;"
class="featured-listing-title"><a href="/listing/<?php echo sanitize_title($listing->c_name_generic_c)?>" class="listing_link" data-id='<?php echo $listing->id;?>'><?php echo $listing->c_name_generic_c;?></a></h4>

        <div>
            <ul class="wpp_overview_data">            
            <?php if($listing->c_listing_region_c): ?>
                <li class="property_region overview_detail">
<?php 
if($listing->c_listing_town_c): 
echo ($listing->c_listing_town_c).","; endif; ?>
 <?php echo $listing->c_listing_region_c; ?></li>
            <?php endif; ?>
				<li class="overview_detail"><?php echo __('Asking:','bbcrm').' '.$listing->c_currency_id." ".number_format(str_replace("$","",$listing->c_listing_askingprice_c)) ;?></li>
		<li class="overview_detail"><?php echo __('Gross Sales:','bbcrm').' '.$listing->c_currency_id." ".number_format(str_replace("$","",$listing->c_financial_grossrevenue_c)) ;?></li>
<li class="overview_detail"><?php echo __('Years in Business:','bbcrm').' '.$listing->c_listing_yearsest_c; ?></li>
	       </ul>
		</div>	

	

</div>

<div style="text-align: center;
    width: 130px;
    margin-left: 50px;
    margin-right: 50px;
    background-color: #e8e9ea;
    /* padding-top: 20px; */
    border: 2px solid #fab54a;
    border-radius: 4px;
    color: #fff;
    margin-bottom: 18px;" id="featured_more_info"><a href="/listing/<?php echo sanitize_title($listing->c_name_generic_c)?>" data-id='<?php echo $listing->id;?>'>More Info</a></div>


<!-- here is the closing tag of new div --></div>
<?php } 
?>

<br clear=all>
</div>

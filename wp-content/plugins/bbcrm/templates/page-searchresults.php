<?php
/*
Template Name: Search Results
*/
session_start();
// ini_set('display_errors',true);
// error_reporting(E_ALL);
//print_r($_POST);

foreach($_REQUEST as $k=>$v){
	if(''==$v){
		unset($_REQUEST[$k]);
	}
}

// echo '<pre>'; print_r($_REQUEST); echo '</pr/e>';



// Grab our type filters

$get_params = '_partial=1&_escape=0';

if(isset($_REQUEST["id"]) && !empty($_REQUEST["id"])){
	$reg = '&id='.$_REQUEST["id"];
	$get_params .= $reg;
}

if(isset($_REQUEST["c_listing_region_c"]) && !empty($_REQUEST["c_listing_region_c"])){
	$reg = '&c_listing_region_c='.$_REQUEST["c_listing_region_c"];
	$get_params .= $reg;
}

if(isset($_REQUEST["c_listing_town_c"]) && !empty($_REQUEST["c_listing_town_c"])){
	$town = '&c_listing_town_c='.$_REQUEST["c_listing_town_c"];
	$get_params .= $town;
}


// If we have Categories
$business_categories = array();
if(isset($_REQUEST["c_businesscategories"]) && !empty($_REQUEST["c_businesscategories"]) )
{
	//$get_params .= '&c_businesscategories='. '%25'.urlencode($_REQUEST["c_businesscategories"]).'%25';
	if( is_array($_REQUEST["c_businesscategories"])  && $_REQUEST["c_businesscategories"][0] != '' )
	{
		foreach($_REQUEST["c_businesscategories"] as $k=>$v)
		{
			$business_categories[] = trim($v);	
		}
	}
	else 
	{
		$business_categories[] = trim( $_REQUEST["c_businesscategories"] );
	}

	
	if ( count($business_categories)>1 )
	{
		$get_params .= '&c_businesscategories=:multiple:__'.implode('__',$business_categories);	
	}
	elseif ( count($business_categories) == 1 )
	{
		// echo 'hello';
		$get_params .= '&c_businesscategories=:multiple:__'.$business_categories[0];	
	}

	// echo '<pre>'; print_r($get_params); echo '</pre>';
}
/**
*/

if(isset($_REQUEST["c_ownerscashflow"]) && !empty($_REQUEST["c_ownerscashflow"])){
	$ownerscashflow_params = explode("|",$_REQUEST["c_ownerscashflow"]);
	
	$betweenParam = urlencode('between_'.$ownerscashflow_params[0].'_'.$ownerscashflow_params[1]);
	$get_params .= '&c_ownerscashflow='.$betweenParam;
}

if(isset($_REQUEST["c_listing_askingprice_c"]) && !empty($_REQUEST["c_listing_askingprice_c"])){
	$askingprice_params = explode("|",$_REQUEST["c_listing_askingprice_c"]);
	
	$betweenParam = urlencode('between_'.$askingprice_params[0].'_'.$askingprice_params[1]);
	$get_params .= '&c_listing_askingprice_c='.$betweenParam;
}

if(isset($_REQUEST["c_listing_downpayment_c"]) && !empty($_REQUEST["c_listing_downpayment_c"])){
	$listing_downpayment_params = explode("|",$_REQUEST["c_listing_downpayment_c"]);
	
	$betweenParam = urlencode('between_'.$listing_downpayment_params[0].'_'.$listing_downpayment_params[1]);
	$get_params .= '&c_listing_downpayment_c='.$betweenParam;
}









if(isset($_REQUEST["c_listing_franchise_c"]) && !empty($_REQUEST["c_listing_franchise_c"])){
	$franch = '&c_listing_franchise_c='.$_REQUEST["c_listing_franchise_c"];
	$get_params .= $franch;
}


if( isset($_REQUEST['c_name_generic_c']) && is_numeric($_REQUEST["c_name_generic_c"]) )
{
	$home = '&c_listing_frontend_id_c='.trim($_REQUEST["c_name_generic_c"]); // based on Ref ID not on the ID from DB
	$get_params .= $home;	
}
else if( isset($_REQUEST['c_name_generic_c']) && !is_numeric($_REQUEST["c_name_generic_c"]) )
{
	$keyword = trim($_REQUEST["c_name_generic_c"]);
	$get_params .= '&c_name_generic_c=:multiple:__'.$keyword;
}






$json = x2apicall(array('_class'=>'Clistings?'.$get_params));
$decoded_json_All = json_decode($json);

//print_r('<pre>');print_r($json);print_r('</pre>');

//echo '<pre>'; print_r(count($decoded_json_All)); echo '</pre>';

$maxPerPage = MAX_LISTING_PER_PAGE;
/*Get the current page eg index.php?pg=4*/

if(isset($_GET['page_no'])){
    $pageNo = abs(intval($_GET['page_no']));
}else{
    $pageNo = 1;
}

$limit = ($pageNo - 1) * $maxPerPage;
$prev = $pageNo - 1;
$next = $pageNo + 1;
$limits = (int)($pageNo - 1) * $maxPerPage;

$jsonPage = (int)($pageNo - 1);

$sort_order_param = 'sortRecent';
$order_column = '-createDate';
	
if(isset($_GET['sort_order'])){
	$sort_order_param = $_GET['sort_order'];
	if ($_GET['sort_order'] == 'sortRecent')
	{
		$order_column = '-createDate';
	}
	elseif ($_GET['sort_order'] == 'sortOldest')
	{
		$order_column = '+createDate';
	}
	elseif ($_GET['sort_order'] == 'sortPricel')
	{
		$order_column = '+c_listing_askingprice_c';
	}
	elseif ($_GET['sort_order'] == 'sortPriceh')
	{
		$order_column = '-c_listing_askingprice_c';
	}   
}
$sort_order = '&_order='.$order_column;

$get_params = $get_params.'&_limit='.$maxPerPage.'&_page='.$jsonPage.$sort_order;
$jsonLimit = x2apicall(array('_class'=>'Clistings?'.$get_params));
$decoded_jsonLimit = json_decode($jsonLimit);

//print_r('<pre>');print_r($jsonLimit);print_r('</pre>');

//print_r('<pre>');print_r($get_params);print_r('</pre>');

//echo '<pre>'; print_r(count($decoded_jsonLimit)); echo '</pre>';



// Filter Results
//$decoded_json = filter_listings_obj($decoded_json);

$results = $decoded_jsonLimit;
$totalposts = count($decoded_json_All);
$maxPages = ceil(count($decoded_json_All) / $maxPerPage);
$lpm1 = $maxPages - 1;

// echo '<pre>'; print_r($maxPages); echo '</pre>';





//get_template_part('template','top');
get_header();

?>
<section style="margin-top: 72px !important;"  id="content" data="property"> 
<div class="portfolio_group">
        <div style="margin-right: 0 !important;"  class="search_result">
          <div class="row">
          <div class="col-md-12  col-sm-12 ">
        
      </div>
      
      
	<div id="business_container" class="col-md-9">
	 
	
		
		
<?php


global $wpdb;


$results_false_flag = 0;

foreach( $results as $result )
{
	if($result !== false) {
		$results_false_flag = 1;
	}
}


$html = '';

if(count((array)$results) > 0 && $results->status != "404"){
	$listingids = array();

	foreach ($results as $searchlisting){

	if(!in_array($searchlisting->id,$listingids)){
		$listingids[]= $searchlisting->id;

		if(!empty($searchlisting->c_businesscategories)){
		$categories = substr($searchlisting->c_businesscategories,1,-1);
		$categories = explode(',',str_replace('"', '', $categories));
		$cats = '';
		foreach($categories as $cat){
			$cats .='<a href="?find='.urlencode(stripslashes($cat)).'">'.stripslashes($cat).'</a> ';
		}
		}


		$html .= "<div class='searchresult'><p><a style='color:#e99309;font-size:1.3em' href=\"/listing/". sanitize_title($searchlisting->c_name_generic_c) ."\" class=\"listing_link\" data-id=\"". $searchlisting->id ."\">".$searchlisting->c_name_generic_c."</a></p>";
		$html .= "<div style='display:inline-block;width:20%;color:#3b5998; font-size:1.3em;vertical-align:top;'>".__("","bbcrm").$searchlisting->c_listing_region_c;
	    
$html .= "<p style='font-size:1.3em;color:#4672b2;'>".__("",'bbcrm').$searchlisting->c_currency_id.number_format($searchlisting->c_listing_askingprice_c).'</p>';
	    $html .= "<p style='font-size:1.0em;color:#666666;'>".__("Cash Flow: ",'bbcrm').$searchlisting->c_currency_id.number_format($searchlisting->c_ownerscashflow)."</p>";
	$html .="</div>";

		$html .= "<div style='display:inline-block;color:#807e7e;width:53%;margin-bottom:10px; margin:0 10px; display:inline-block; height:140px;'>";


		$result = $wpdb->get_results( "SELECT * FROM x2_actions LEFT JOIN x2_action_text ON x2_actions.id=x2_action_text.id WHERE x2_actions.associationType='clistings' AND x2_actions.type='attachment' AND x2_actions.associationId='".$searchlisting->id."' ORDER BY x2_actions.id DESC LIMIT 1" );
		$result = $result[0];
		$pic_name = explode(':', $result->text);
		$pic_name = $pic_name[0];
		//printR($results);

		if(!$pic_name){
                $html .= '<a href="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'--'.$searchlisting->id.'" class="listing_link" data-id="'.$searchlisting->id.'"><img src="'.plugin_dir_url(__DIR__).'images/noimage.png" align=right></a>';
        }else{
                $html .= '<a href="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'--'.$searchlisting->id.'" class="listing_link" data-id="'.$searchlisting->id.'"><img src="'.get_bloginfo('url').'/crm/uploads/media/'.$result->completedBy.'/'.$pic_name.'" style="max-height:100%;overflow:hidden;border:2px solid #fff" align=right  /></a>';

        } 
		$html .='<div style="width:100%">'.$searchlisting->description.'</div>';

$html .= "</div>";
if(!is_user_logged_in()){	
	$html .= "<div style='display:inline-block;width:20%;vertical-align:top;'><p><a href='/registration/'><span style='font-size:1.0em;color:#ffffff; background-color:#c4202b;padding:4px; 3px 10px;width: 120px;text-align: center; '>".__("Contact Seller",'bbcrm').$searchlisting->c_listing_businesscat_c."</span></a></p>";

	$html .= '<p><a href="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'" class="listing_link" data-id="'.$searchlisting->id.'">'."<span style='font-size:1.0em;color:#ffffff; background-color:#c4202b;padding: 3px 10px;width: 120px;text-align: center; '>".__("More Info",'bbcrm').$searchlisting->c_listing_businesscat_c."</span></a></p></div>";
}	
		
		
		
if(is_user_logged_in() ){
		$html .= '<form action="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'" method=post><input type=hidden name="action" value="add_to_portfolio" /><input type=hidden name="id" value="'. $searchlisting->id.'" /><input type=submit style="display:none; margin-bottom:18px;" value="'. __('Add to my portfolio','bbcrm').' &#10010;" class="portfolio_action_button portfolio-add"  /></form>';
		}
		$html .= "</div>";
	}
	}
}else{
	$qy = (empty($qy))?"your search":'"'.$qy.'"';
	$html .= "<h2>No results were found for ".$qy."</h2>";
	$html .= "<p>Please check your spelling or try a search with different parameters.</p>";
	$html .= do_shortcode('[featuredsearch]');
}

	

//print_r($listingids);

echo '<h2>'.get_the_title().'</h2>';

if ($maxPages > 1) {
?>

		<div class="clearFix row-listing col-md-12" style="padding: 0; margin-bottom: 30px;">
			<div class="clearFix pagination-header">
				<div class="pull-left">
					<div class="results_count">
						<?php
							if(!empty($listingids)){
							echo __("Your search for ",'bbcrm');
							if(is_array($qy)){
								echo join(",",$qy);
							}else {
								echo $qy;
							}
							_e(" returned ",'bbcrm');
							echo count((array)$listingids);
							echo (count((array)$listingids)===1)?__(' result.','bbcrm'):__(' results.','bbcrm');
							}
						?>
					</div>
					<form class="sortForm pull-left" id="orderByForm">
						<select id="sort_listings" name="sort" data-title="Sort By" data-header="Sort By" class="selectpicker show-menu-arrow show-tick" >
							<option class="removeThis"></option>
							<option value="sortRecent" <?php if ($sort_order_param == 'sortRecent') { ?>selected="selected"<?php } ?>>Most Recent</option>
							<option value="sortOldest" <?php if ($sort_order_param == 'sortOldest') { ?>selected="selected"<?php } ?>>Oldest Listings</option>
							<option value="sortPricel" <?php if ($sort_order_param == 'sortPricel') { ?>selected="selected"<?php } ?>>Price (Low - High)</option>
							<option value="sortPriceh" <?php if ($sort_order_param == 'sortPriceh') { ?>selected="selected"<?php } ?>>Price (High - Low)</option>
						</select>
					</form>
				</div>
				<div class="pull-right">
					<?php echo pagination($maxPages,$pageNo,$lpm1,$prev,$next,$maxPerPage, $totalposts); ?>
				</div>
			</div>
		</div>
					
<?php 
}	
	echo $html;

if ($maxPages > 1) {
?>

		<div class="clearFix row-listing col-md-12" style="padding: 0; margin-bottom: 30px;">
			<div class="clearFix pagination-header">
				<div class="pull-left">
					<div class="results_count">
						<?php
							if(!empty($listingids)){
							echo __("Your search for ",'bbcrm');
							if(is_array($qy)){
								echo join(",",$qy);
							}else {
								echo $qy;
							}
							_e(" returned ",'bbcrm');
							echo count((array)$listingids);
							echo (count((array)$listingids)===1)?__(' result.','bbcrm'):__(' results.','bbcrm');
							}
						?>
					</div>
					<form class="sortForm pull-left" id="orderByForm">
						<select id="sort_listings" name="sort" data-title="Sort By" data-header="Sort By" class="selectpicker show-menu-arrow show-tick" >
							<option class="removeThis"></option>
							<option value="sortRecent" <?php if ($sort_order_param == 'sortRecent') { ?>selected="selected"<?php } ?>>Most Recent</option>
							<option value="sortOldest" <?php if ($sort_order_param == 'sortOldest') { ?>selected="selected"<?php } ?>>Oldest Listings</option>
							<option value="sortPricel" <?php if ($sort_order_param == 'sortPricel') { ?>selected="selected"<?php } ?>>Price (Low - High)</option>
							<option value="sortPriceh" <?php if ($sort_order_param == 'sortPriceh') { ?>selected="selected"<?php } ?>>Price (High - Low)</option>
						</select>
					</form>
				</div>
				<div class="pull-right">
					<?php echo pagination($maxPages,$pageNo,$lpm1,$prev,$next,$maxPerPage, $totalposts); ?>
				</div>
			</div>
		</div>
					
<?php 
}	
//get_template_part("home","search");
?>  

	</div>
	
	<div class="col-md-3 sidebar">
       <?php dynamic_sidebar( "content-sidebar" ); ?>
    </div> 
       
       
	</div>
	
	  
       
  </div>
       
 </div>      
</section>

<?php get_footer(); ?>

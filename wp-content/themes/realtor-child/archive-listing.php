<?php
session_start();

//ini_set('display_errors','on');
//error_reporting(E_ALL);

//print_r($_SESSION);

if(!is_user_logged_in()){
	wp_redirect('/registration/');
	exit;
}


if(is_user_logged_in() ){

	$inportfolio = false;
	$is_requested = false;
	$isaddressreleased = false;
	$crmid = 0;

	$bbcrm_option = get_option( 'bbcrm_settings' );

	if(isset($_POST["id"])){
		$crmid = $_POST["id"];
	}elseif(isset($_SESSION["listingid"]) ){
		$crmid = $_SESSION["listingid"];
	}else{}


	if($crmid>0) {
		$json = x2apicall(array('_class'=>'Clistings/'.$crmid.'.json'));
	}
	else
	{
		//we need to get the listing by ID
		$listingParams = explode('--',$_SERVER["REQUEST_URI"]);
		if (count($listingParams) > 1)
		{
			//we have a link that contains id
			$listingID = str_replace('/', '', $listingParams[1]);
			$json = x2apicall(array('_class'=>'Clistings/'.$listingID.'.json'));
		}
		else
		{
			$trailing = (substr($_SERVER["REQUEST_URI"],-1)=="/")?"":"/";
			$json = x2apicall(array('_class'=>'Clistings/by:c_listing_frontend_url='.$_SERVER["REQUEST_URI"].$trailing.'.json'));
		}		
	}
	$listing = json_decode($json);



	//Get The Broker
	if ($listing->c_assigned_user_id != '')
	{
		$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($listing->c_assigned_user_id).".json"));
		$listingbroker =json_decode($json);
	}
	elseif ($listing->assignedTo != '')
	{
		$results = $wpdb->get_results( "SELECT * FROM x2_users WHERE userAlias='".$listing->assignedTo."'", OBJECT );
		$broker_nameId = $results[0]->firstName.' '.$results[0]->lastName.'_'.$results[0]->id;
		$broker_name = $results[0]->firstName.' '.$results[0]->lastName;
		$broker_nameID = $results[0]->nameId;
		
		// $json = x2apicall(array('_class'=>'Brokers/by:name='.urlencode($broker_name).".json"));
		$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($broker_nameID).".json"));
		$listingbroker =json_decode($json);
		
	}


	// Get the buyer
	$json = x2apicall(array('_class'=>'Contacts/by:email='.urlencode($userdata->user_email).".json"));
	$buyer =json_decode($json);
	
	$isuserregistered = ($buyer->c_buyer_status=="Registered")?true:false;
	if ($listingbroker->name == '')
	{
		$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($buyer->c_broker).".json"));
		$buyerbroker =json_decode($json);
	}
	else
	{
		$buyerbroker = $listingbroker;
	}
		
	
	$json = x2apicall(array('_class'=>'Media/by:fileName='.$buyerbroker->c_profilePicture.".json"));
	$brokerimg =json_decode($json);


	if(isset($_POST["add_to_portfolio"]) || $_POST["action"]=="add_to_portfolio"){
		
		$json = x2apicall(array('_class'=>'Portfolio/by:c_listing_id='.$listing->id.";c_buyer=".urlencode($buyer->nameId).".json"));
		$prevlisting =json_decode($json);	

		// echo '<pre>'; print_r($buyer); echo '</pre>';
	
		if($prevlisting->status=="404"){
			$data = array(
				'name'	=>	'Portfolio listing for '.$listing->name,
				'c_listing'	=>	$listing->name,
				'c_listing_id'	=>	$listing->id,
				'c_buyer'	=>	$buyer->nameId,
				'c_buyer_id'	=>	$buyer->id,
				'c_release_status'	=>	'Added',
				'assignedTo'	=>	$buyerbroker->assignedTo,
			);
		
			$json = x2apipost( array('_class'=>'Portfolio/','_data'=>$data ) );
			$portfoliolisting =json_decode($json[1]);

			$json = x2apicall(array('_class'=>'Portfolio/'.$portfoliolisting->id.'.json'));
			$portfoliorelationships =json_decode($json);
			
			$json = x2apicall( array('_class'=>'Portfolio/'.$portfoliorelationships->id."/relationships?secondType=Contacts" ) );
			$rel = json_decode($json);				

			$json = x2apipost( array('_method'=>'PUT','_class'=>'Portfolio/'.$portfoliolisting->id.'/relationships/'.$rel[0]->id.'.json','_data'=>$data ) );		
		}
	}



	//Is this listing in the user's data room?	
	$json = x2apicall(array('_class'=>'Portfolio/by:c_listing_id='.$listing->id.';c_buyer='.urlencode($buyer->nameId).'.json'));
	$portfoliolisting =json_decode($json);	

	// echo '<pre>'; print_r( $portfoliolisting ); echo '</pre>';

	if($portfoliolisting->id){
		$inportfolio=true;		
	}

	if($portfoliolisting->c_is_hidden){
		$is_portfolio_hidden=true;		
	}

	$status =$listing->c_sales_stage;
	$listing_id =$listing->id;
	$listing_dateapproved = $listing->c_listing_date_approved_c;
	$generic_name =$listing->c_name_generic_c;
	$description =$listing->description;
	$region=$listing->c_listing_region_c;
	$terms=$listing->c_listing_terms_c;
	$currency_symbol=$listing->c_currency_id;
	$grossrevenue=number_format($listing->c_financial_grossrevenue_c);
	$amount=number_format($listing->c_listing_askingprice_c);
	$downpayment=number_format($listing->c_listing_downpayment_c);
	$ownercashflow=number_format($listing->c_ownerscashflow);
	$brokername = $listing->assignedTo;
	$brokerid = substr($listing->c_assigned_user_id, strpos($listing->c_assigned_user_id, "_") + 1);;
	$categories = 	join(",",json_decode($listing->c_businesscategories)); //

	$_SESSION["viewed_listings"][$listing_id] = array("brokerid"=>$listingbroker->name,"listingname"=>$generic_name);

	$cssclass = '';


	if($portfoliolisting->c_release_status== "Released"){
		$isaddressreleased = true;
		$cssclass = 'nareq_released';
		$generic_name = $listing->name_dba_c.' "'.$generic_name.'" ';
		$address = $listing->listing_address_c."<br>";
		$city = $listing->listing_city_c." ";
		$postal = $listing->listing_postal_c."<br>";
	}


	$json = x2apicall(array('_class'=>'Clistings/'.$crmid.'/tags'));
	$tags = json_decode($json);

	$listingtags = array();
	foreach ($tags as $idx=>$tag) {
		$listingtags[] = urldecode(substr($tag, 1));
	}


	/* Failsafe. Need to move to create flow */
	if(empty($listing->c_listing_frontend_url) || $listing->c_listing_frontend_url != $listing->c_name_generic_c){
	$json = x2apipost( array('_method'=>'PUT','_class'=>'Clistings/'.$listing->id.'.json','_data'=>array('c_listing_frontend_url'=>'/listing/'.sanitize_title($listing->c_name_generic_c)."/") ) );
	}


	//Get The Broker
	if ($listing->c_assigned_user_id != '')
	{
		$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($listing->c_assigned_user_id).".json"));
		$listingbroker =json_decode($json);
	}
	elseif ($listing->assignedTo != '')
	{
		$results = $wpdb->get_results( "SELECT * FROM x2_users WHERE userAlias='".$listing->assignedTo."'", OBJECT );
		$broker_nameId = $results[0]->firstName.' '.$results[0]->lastName.'_'.$results[0]->id;
		$broker_name = $results[0]->firstName.' '.$results[0]->lastName;
		$broker_nameID = $results[0]->nameId;
		
		// $json = x2apicall(array('_class'=>'Brokers/by:name='.urlencode($broker_name).".json"));
		$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($broker_nameID).".json"));
		$listingbroker =json_decode($json);
		
	}

	if(!$listingbroker->nameId){
		$json = x2apicall(array('_class'=>'Brokers/by:nameId=House%20Broker_5.json'));
		$listingbroker =json_decode($json);
	}


	
	//get all files that are allowed to see them
	$data = array(
			'listing_id'	=>	$listing->id,
			'buyer_id'	=>	$buyer->id,		
		);
	$json = x2apipost( array('_method'=>'GET','_class'=>'PortfolioMedia/','_data'=>$data ) );
	$buyerListingFiles =json_decode($json[1]);
	$currentBuyerListingFiles = array();
	if (!empty($buyerListingFiles))
	{
		foreach ($buyerListingFiles as $indexFile => $portfolioMediaFile)
		{
			if ($portfolioMediaFile->listing_id == $listing->id && $portfolioMediaFile-> buyer_id == $buyer->id)
			{
				$currentBuyerListingFiles[$portfolioMediaFile->media_id] = $portfolioMediaFile;
			}
		}
	}
	
	//get all filed for the listing
	$data = array(
			'associationId'	=>	$listing->id,
			'associationType' => "clistings",		
		);	
	$json = x2apipost( array('_method'=>'GET','_class'=>'Media/','_data'=>$data ) );
	$fileslisting =json_decode($json[1]);
	
	function get_file_icon ($mediaFile)
	{
		
		$mediaIcon = 'unset';
		
		$map = array(
			'image' 		=> '<i class="fa fa-file-picture-o"></i>', 
			'text' 			=> '<i class="fa fa-file-text-o"></i>', 
			'word' 			=> '<i class="fa fa-file-word-o"></i>', 
			'excel' 		=> '<i class="fa fa-file-excel-o"></i>', 
			'sheet' 		=> '<i class="fa fa-file-excel-o"></i>', 
			'powerpoint' 	=> '<i class="fa fa-file-powerpoint-o"></i>', 
			'presentation' 	=> '<i class="fa fa-file-powerpoint-o"></i>', 
			'pdf' 			=> '<i class="fa fa-file-pdf-o"></i>',
			'audio' 		=> '<i class="fa fa-file-audio-o"></i>', 
			'video' 		=> '<i class="fa fa-file-video-o"></i>', 
			'zip' 			=> '<i class="fa fa-file-zip-o"></i>', 
			'rar' 			=> '<i class="fa fa-file-zip-o"></i>',
		);
		
		foreach ($map as $fileType => $fileIcon) {
			if (strpos($mediaFile -> mimetype, $fileType) !== false) {
			    $mediaIcon = $fileIcon;
		        break;
		    }
		}
		if ($mediaIcon == 'unset')
		{
			$mediaIcon = '<i class="fa fa-file-o"></i>';
		}
		return $mediaIcon;
	}


	$currentListingFiles = array();
	if (!empty($fileslisting))
	{
		foreach ($fileslisting as $indexFile => $mediaFile)
		{
			if ($mediaFile->associationType == 'clistings' && $mediaFile->private == 0)
			{
				//check if this buyer has permission to see this file
				if (array_key_exists($mediaFile->id, $currentBuyerListingFiles))
				{
					$buyerFile = $currentBuyerListingFiles[$mediaFile->id];
					if ($buyerFile -> private == 1)
					{
						//check if the date is still available
						if ($buyerFile -> private_end_date == '0000-00-00') {
							$mediaFile -> mediaIcon = get_file_icon($mediaFile);
							$currentListingFiles[$mediaFile->id] = $mediaFile; 
						}
						else
						{
							if (strtotime(date('Y-m-d')) <= strtotime($buyerFile -> private_end_date))
							{
								$mediaFile -> mediaIcon = get_file_icon($mediaFile);
								$currentListingFiles[$mediaFile->id] = $mediaFile; 
								// check what icon need to list based on myme-type
							}
	
						}
					}
					
				}
				
			}
		}
	}

	// echo '<pre>'; print_r( $currentListingFiles ); echo '</pre>';


	    
	if(isset($_POST['action']))
	{

		# POST id = LISTING ID (OPPORTUNITY)
		# POST uid = BUYER/USER ID (LEAD)
		if("hide" ==$_POST['action']){
			//parameters are regimented. Failure to include all params makes things go Bad.
			$json = x2apipost(array('_method'=>'PATCH','_class'=>'Portfolio/'.$_POST["pid"].'.json','_data'=>array('c_is_hidden'=>1)));		  
			$is_portfolio_hidden=true;		 
		}

		if("release" ==$_POST['action']){ //ADDRESS REQUEST
			$json = x2apipost(array('_method'=>'PATCH','_class'=>'Portfolio/'.$_POST["pid"].'.json','_data'=>array('c_release_status'=>'Released')));
		}

		if("request" ==$_POST['action']){ //ADDRESS REQUEST
			$json = x2apicall(array('_class'=>'Portfolio/'.$_POST["pid"].'.json'));
			$portfoliolisting = json_decode($json);
			// echo '<pre>'; print_r($portfoliolisting); echo '</pre>';

			if($portfoliolisting->c_release_status== "Added"){	
				$json = x2apipost(array('_method'=>'PATCH','_class'=>'Portfolio/'.$_POST["pid"].'.json','_data'=>array('c_release_status'=>'Requested')));
				$request = json_decode($json);
				$json = x2apicall(array('_class'=>'Portfolio/'.$_POST["pid"].'.json'));
				$portfoliorelationships =json_decode($json);
				
				$json = x2apicall( array('_class'=>'Portfolio/'.$portfoliorelationships->id."/relationships?secondType=Contacts" ) );
				$rel = json_decode($json);
				$data = array(
					'firstLabel'	=>	'Requested',
				);
				$json = x2apipost( array('_method'=>'PUT','_class'=>'Portfolio/'.$_POST["pid"].'/relationships/'.$rel[0]->id.'.json','_data'=>$data ) );

			}
			
			$data = array(
				'actionDescription'	=>	'Address Request release for '.$listing->name.'<br>Buyer: '.$user->name.'<br>Listing Broker:'.$listing->assignedTo."<br><a href='".get_bloginfo('url')."/portfolio/?action=release&pid=".$_POST["pid"]."' class='x2-button'>Release address</a>",
				'assignedTo'	=>	$listing->assignedTo,
				'associationId' => $_POST["pid"],
				'associationType' => 'portfolio',
				'associationName' => $portfoliolisting->name,
				'subject'	=>	'Address Request release for '.$portfoliolisting->name,
			);	
			
			$json = x2apipost( array('_class'=>'Portfolio/'.$_POST["pid"].'/Actions','_data'=>$data ) );

			if($user->assignedTo != $listing->assignedTo){
				$data['assignedTo']= $user->assignedTo;
				$json = x2apipost( array('_class'=>'Portfolio/'.$_POST["pid"].'/Actions','_data'=>$data ) );
			}

			$is_requested = true;

		} // END IF ADDRESS REQUEST 

	}
}
//////////////////



global $pagetitle;
$pagetitle = get_bloginfo('name')." ".$listing->c_name_generic_c;

wp_enqueue_script('galleria-plugin',get_stylesheet_directory_uri().'/galleria/galleria-1.4.7.js');
wp_enqueue_script('galleria-theme',get_stylesheet_directory_uri().'/galleria/themes/classic/galleria.classic.min.js');
wp_enqueue_style('galleria-css',get_stylesheet_directory_uri().'/galleria/themes/classic/galleria.classic.css');


get_header();
?>

<section id="content" data="property">
	<div class="row listing_content">
	<h2><?php the_title(); ?></h2>

<?php 
if( is_user_logged_in() ){
	if($portfoliolisting->c_release_status== "Deleted"){
		echo '<div class="portfoliostatus deleted">&#10006; ' .	__("This property was removed from your data room",'bbcrm') . "</div>";
	}
	elseif($isaddressreleased){
		echo '<div class="portfoliostatus released"> &#9733; ' .	__("The address of this business is available to you",'bbcrm') . "</div>";
	}
	elseif($inportfolio){
		echo '<div class="portfoliostatus added">&#10003; ' .	__("This property is in your data room",'bbcrm') . "</div>";
	}
}
?>
		<div id="business_container" role="main">
				<div class="<?php wpp_css('property::title', "building_title_wrapper"); ?>">
					<?php if($userdata){ if($isaddressreleased): ?>
							<h3><?php echo $listing->name." ".$address.$city.$region." ".$postal;?></h3>
					<?php else: ?>
							<h1 class="property-title entry-title <?php echo $cssclass;?>"><?php echo $generic_name; ?></h1>
					<?php endif; } ?>
				</div>
				<div class="entry-content">
<?php

	$result = $wpdb->get_results( "SELECT * FROM x2_actions LEFT JOIN x2_action_text ON x2_actions.id=x2_action_text.id WHERE x2_actions.associationType='clistings' AND x2_actions.type='attachment' AND x2_actions.associationId='".$listing->id."' ORDER BY x2_actions.id DESC LIMIT 1" );
	$result = $result[0];
	$pic_name = explode(':', $result->text);
	$pic_name = $pic_name[0];
?>

	<div style="overflow: auto;">

<?php
	if(!$pic_name){
            echo '<a href="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'--'.$searchlisting->id.'" class="listing_link" data-id="'.$searchlisting->id.'" style="float: right;"><img src="'.plugin_dir_url().'bbcrm/images/noimage.png"></a>';
            // echo '<pre>'; print_r(plugin_dir_url()); echo '</pre>';
    }else{
            echo '<a href="/listing/'.sanitize_title($searchlisting->c_name_generic_c).'--'.$searchlisting->id.'" class="listing_link" data-id="'.$searchlisting->id.'" style="float: right;"><img src="'.get_bloginfo('url').'/crm/uploads/media/'.$result->completedBy.'/'.$pic_name.'" style="max-height:100%;overflow:hidden;border:2px solid #fff"  /></a>';

    } 
?>

		<div class="wpp_the_content" style=""><?php echo nl2br($description); ?></div>
    </div>
					
<?php

 if (!empty($currentListingFiles)) { ?>
   		<div id="confidential_files">
   		<h3 class=detailheader>Confidential Files</h3>
        <?php   foreach ($currentListingFiles as $mediaFile) { ?>
	           <div class="property_detail" style="margin-bottom:3px;"><a target="_blank" href="/crm/uploads/media/<?php echo $mediaFile->uploadedBy; ?>/<?php echo $mediaFile->fileName; ?>"><?php echo $mediaFile->mediaIcon; ?> &nbsp;<?php echo $mediaFile->fileName; ?></a></div>	           		
        <?php   } ?>
        </div>
   
   <?php }


if($inportfolio){

	global $wpdb;
	$results = $wpdb->get_results( 'SELECT gp.* FROM x2_gallery_photo gp RIGHT JOIN x2_gallery_to_model gm ON gm.galleryId = gp.gallery_id WHERE gm.modelName="Clistings" AND gm.modelId='.$listing->id, OBJECT );

	//print_r($results);

	if(!empty($results[0]->id)):
	?>
							<h3 class=detailheader style="cursor:pointer;width:100%;background-color:#ddd" onclick='jQuery("#propertygallery").slideToggle()'>Gallery <div style="display:inline;float:right;font-size:.6em;margin:auto 6px; line-height: 28px;">(click to hide/view)</div></h3>

	<div class="galleria" style="max-width:45%;margin:0 auto">
	<?php
	foreach ($results as $image){
		echo "<img src='/crm/uploads/gallery/_".$image->id.".jpg' />";
	}
	?>
	</div>
	<script>
	    //Galleria.loadTheme('/wp-content/');
	    Galleria.run('.galleria', {
			imageCrop:true,
			height: .75,
			debug:false
	});

	</script>
	<?php
	endif;

}
 ?>	


<div class="row" style="">
<div class="col-7 col-lg-7 col-md-6 col-sm-12">

	<?php 
	$detailsheader = __("Business Details", 'bbcrm');

	if($isuserregistered && $inportfolio)
	{
		$detailsheader = __("Complete Business Profile", 'bbcrm');
	}
	 ?>
					<h3 class=detailheader onclick='jQuery("#propertydetails ").slideToggle()'><?php echo $detailsheader;?></h3>
						<div id=propertydetails class="property_details_div">
		
						  <div class="property_details">
	<!--<div class="property_detail"><label><?php _e("Listed on:", 'bbcrm');?></label> <?php echo date('F j, Y',$listing_dateapproved); ?></div>-->
							<div class="property_detail" id="property_listing_id" data-id="<?php echo $listing_id;?>"><label><?php _e("Listing ID:", 'bbcrm');?></label> #<?php echo $listing_id; ?></div>		
							<div class="property_detail"><label><?php _e("Categories:", 'bbcrm');?></label> <?php echo $categories; ?></div>
							<div class="property_detail"><label><?php _e("State:", 'bbcrm');?></label> <?php echo $region;?></div>
							<div class="property_detail"><label><?php _e("Asking Price:", 'bbcrm');?></label> <?php echo $currency_symbol." ".$amount;?></div>
							<div class="property_detail"><label><?php _e("Gross Revenue:", 'bbcrm');?></label> <?php echo $currency_symbol." ".$grossrevenue;?></div>
	<!--<div class="property_detail"><label><?php _e("Down Payment:", 'bbcrm');?></label> <?php echo $currency_symbol." ".$downpayment;?></div>-->
	<!--<div class="property_detail"><label><?php _e("Terms:", 'bbcrm');?></label> <?php echo $terms;?></div>-->
							<div class="property_detail"><label><?php _e("Owner's Cash Flow:", 'bbcrm');?></label> <?php echo $currency_symbol." ".$ownercashflow;?></div>
					<?php if( $inportfolio ): 
					//print_r($listing);
					if($isaddressreleased){
					?>
		                <br />
						<h4 class=detailheader><?php _e("Location", 'bbcrm');?></h4>
						<div class="property_detail"><label><?php _e("Address:", 'bbcrm');?></label> <?php echo $listing->c_listing_address_c;?></div>
						<div class="property_detail"><label><?php _e("City:", 'bbcrm');?></label> <?php echo $listing->c_listing_city_c;?></div>
						<div class="property_detail"><label><?php _e("State:", 'bbcrm');?></label> <?php echo $listing->c_listing_region_c;?></div>					
						<div class="property_detail"><label><?php _e("Zip/Postal:", 'bbcrm');?></label> <?php echo $listing->c_listing_postal_c;?></div>
						
		                <h4 class=detailheader>Additional Information</h4>
						<div class="property_detail"><label>Reason for Selling:</label> <?php echo $listing->c_listing_reasonforselling_c;?></div>
		                <div class="property_detail"><label>Hours of Operation:</label> <?php echo $listing->c_listing_hours_c;?></div>
		                <div class="property_detail"><label>Lease Terms:</label> <?php echo $listing->c_Leaseterms;?></div>
		                <div class="property_detail"><label>Lease Contract Date Start:</label> <?php echo date_format($listing->c_Contractdatestart);?></div>
		                <div class="property_detail"><label>Lease Contract Date End:</label> <?php echo date_format($listing->c_Contractdateend);?></div>
		                <div class="property_detail"><label>Lease Improvements:</label> <?php echo $listing->c_financial_leaseimpr_c;?></div>
		                <div class="property_detail"><label>Lease Copy Available?</label> <?php echo $listing->c_listing_leasecopy_c;?></div>
		                <div class="property_detail"><label>Security:</label> <?php echo $listing->c_listing_security_c;?></div>
		                <div class="property_detail"><label>Rental Increase:</label> <?php echo $currency_symbol . number_format($listing->c_financial_rentincrease_c);?></div>
					<?php } ?>
	                    
					<h3 class=detailheader>Business Information</h3>
					<table id="listingtable">
						<tr>
							<td class="listingtablelabel">Franchise?</td>
							<td class="listingtabledata"><?php echo $listing->c_listing_franchise_c;?></td>
							<td class="listingtablelabel">New Franchise?</td>
							<td class="listingtabledata"><?php echo $listing->c_listing_newfranchise_c;?></td>
						</tr>
						<tr>
							<td height="22" class="listingtablelabel">Relocatable?</td>
							<td class="listingtabledata"><?php echo $listing->c_listing_relocatable_c;?></td>
							<td class="listingtablelabel">Home-Based Business?</td>
							<td class="listingtabledata"><?php echo $listing->c_listing_homebusiness_c;?></td>
						</tr>						
						<tr>
							<td class="listingtablelabel">Currently Operating?</td>
							<td class="listingtabledata"><?php echo $listing->c_listing_currently_operating_c;?></td>
							<td class="listingtablelabel">Support/Training?</td>
							<td class="listingtabledata"><?php echo $listing->c_listing_support_training_c;?></td>
						</tr>
	                    <tr>
						  <td class="listingtablelabel">Real Estate Available?</td>
						  <td class="listingtabledata"><?php echo $listing->c_listing_reavail_c;?></td>
						  <td class="listingtablelabel">Store Size (Sq.m.):</td>
						  <td class="listingtabledata"><?php echo number_format($listing->c_listing_area_c);?></td>
					 	</tr>
						<tr>
						 <td class="listingtablelabel">Parking Spaces:</td>
						  <td class="listingtabledata"><?php echo $listing->c_listing_pkgspace_c;?></td>
						  <td class="listingtablelabel">Inventory Value:</td>
						  <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_inventoryval_c);?></td>
						</tr>
	                    <tr>
						  <td class="listingtablelabel">FT Employees:</td>
						  <td class="listingtabledata"><?php echo number_format($listing->c_listing_emp_ft_c);?></td>
						  <td class="listingtablelabel">PT Employees:</td>
						  <td class="listingtabledata"><?php echo number_format($listing->c_listing_emp_pt_c);?></td>
					  </tr>
						<tr>
						  <td class="listingtablelabel">FF&E:</td>
						  <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_ffae);?></td>
						  <td class="listingtablelabel">Rent up to Date?</td>
						  <td class="listingtabledata"><?php echo $listing->c_listing_rentutd_c;?></td>
					  </tr>
					</table>
	           <div style="height:10px;"></div>    
	           <div class="property_detail"><label>Inventory/Stock Included in Price?</label> <?php echo $listing->c_listing_inventory_incl_c;?></div>   
	           <div class="property_detail"><label>Recent Leasehold Improvements:</label> <?php echo $currency_symbol . number_format($listing->c_recentleaseholdimprovements);?></div>
	           <p>&nbsp;</p>
	           <h3 class=detailheader>Financial Information</h3>
	                    <h4 class=detailheader>Income</h4>
	                    <table id="listingtable">
							<tr>
							  <td class="listingtablelabel">Gross Sales:</td>
								<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_sales_c);?></td>
								
								<td class="listingtablelabel">Monthly Gross Sales:</td>
								<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_monthly_sales_c);?></td>
							</tr>
													
							<tr>
							  <td class="listingtablelabel">Gross Revenue:</td>
								<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_grossrevenue_c);?></td>
								
								<td class="listingtablelabel">Monthly Gross Revenue:</td>
								<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_monthly_revenue_c);?></td>
							</tr>					
							<tr>
							  <td class="listingtablelabel">Less Sales Tax (-):</td>
								<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_lesssalestax);?></td>
							<td class="listingtablelabel">Monthly Gross Profit:</td>
								<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_monthly_profit_c);?></td>
						  </tr>
							<tr>
							 <td class="listingtablelabel">Cost of Goods Sold (%):</td>
								<td class="listingtabledata"><?php echo number_format($listing->c_financial_cgs_c) . "%";?></td>
								 <td>&nbsp;</td>
							  <td>&nbsp;</td>
							</tr>
							<tr>
							  <td class="listingtablelabel">Cost of Goods Sold:</td>
								<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_cgstotal_c);?></td>
								 <td>&nbsp;</td>
							  <td>&nbsp;</td>
							</tr>
							<tr>
							  <td class="listingtablelabel">Other Income:</td>
							  <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_other_income_c);?></td>
							  <td>&nbsp;</td>
							  <td>&nbsp;</td>
				  </tr>
							<tr>
							<td class="listingtablelabel">Gross Profit:</td>
							  <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_grossprofit_c);?></td>
							  <td>&nbsp;</td>
							  <td>&nbsp;</td>
				  </tr>
	</table>

	<!-- Gail's added tables start here -->	

	<h4 class=detailheader>Occupancy Expenses</h4>
					<table id="listingtable">
						<tr>
							<td class="listingtablelabel">Rent:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_rent_c);?></td>
							<td class="listingtablelabel">Utilities:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_utilities_c);?></td>
						</tr>
						<tr>
							<td class="listingtablelabel">CAM:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_cam);?></td>
							<td class="listingtablelabel">Financial Insurance:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format(intval($listing->c_financial_ins_c));?></td>
						</tr>						
						<tr>
							<td class="listingtablelabel">Repairs/Maintenance:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_repairsmaint_c);?></td>
							<td class="listingtablelabel">Rubbish Removal:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_rubbish_c);?></td>
						</tr>					
					</table>

	                <h4 class=detailheader>Operating Expenses</h4>
				<table id="listingtable">
						<tr>
							<td class="listingtablelabel">Advertising:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_advertising_c);?></td>
	                        <td class="listingtablelabel">Credit Card Fees:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ccfees_c);?></td>
						</tr>
						<tr>
							<td class="listingtablelabel">Business Loans:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_businessloans_c);?></td>
							<td class="listingtablelabel">Telephone:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_telephone_c);?></td>
						</tr>						
						<tr>
							<td class="listingtablelabel">Cell Phones:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_cellphones_c);?></td>
							<td class="listingtablelabel">Supplies:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_supplies_c);?></td>
						</tr>
						<tr>
							<td class="listingtablelabel">Interest (eg. Line of Credit):</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_addback_interest_c);?></td>
							<td class="listingtablelabel">Leased Vehicles:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_vehicles_c);?></td>
				 		</tr>
						<tr>
							<td class="listingtablelabel">Leased Equipment:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_leasedequip_c);?></td>
							<td class="listingtablelabel">Postage:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_postage_c);?></td>
				 		</tr>
						<tr>
							<td class="listingtablelabel">Legal/Accounting:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_legal_acct_c);?></td>
							<td class="listingtablelabel">Travel &amp; Entertainment:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_te_c);?></td>
				 		</tr>
						<tr>
							<td class="listingtablelabel">Fuel and Vehicle Expense:</td>
							 <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_fuelvehicle_c);?></td>
						     <td>&nbsp;</td>
							 <td>&nbsp;</td>
				  		</tr>					
				</table>
	            <h4 class=detailheader>Payroll Expenses</h4>
					<table id="listingtable">
						<tr>
							<td class="listingtablelabel">Officer Salary:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_officersalary_c);?></td>
							<td class="listingtablelabel">Payroll:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_payroll_c);?></td>
						</tr>
						<tr>
							<td class="listingtablelabel">Payroll Taxes:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_payrolltaxes_c);?></td>
							<td class="listingtablelabel">Employee Health Insurance:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_employeehealthinsurance);?></td>
						</tr>						
						<tr>
							<td class="listingtablelabel">Owner's Health Insurance:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_healthins_owner_c);?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</table>

	                <h4 class=detailheader>Miscellaneous Expenses</h4>

					<table id="listingtable">
						<tr>
							<td class="listingtablelabel">Miscellaneous 1:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc1);?></td>
							<td class="listingtablelabel">Miscellaneous 2:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc2);?></td>
					  </tr>
						<tr>
							<td class="listingtablelabel">Miscellaneous 3:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc3);?></td>
							<td class="listingtablelabel">Miscellaneous 4:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc4);?></td>
						</tr>						
						<tr>
							<td class="listingtablelabel">Miscellaneous 5:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc5);?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</table>
	                    
	                <h4 class=detailheader>Add-Backs/Adjustments</h4>

					<table id="listingtable">
						<tr>
							<td class="listingtablelabel">Officers' Salaries:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_officersalaries_c);?></td>
	                        <td class="listingtablelabel">Owner's Health Insurance:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownerhealthins_c);?></td>
						</tr>
						<tr>
							<td class="listingtablelabel">Loans:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_loans_c);?></td>
							<td class="listingtablelabel">Interest:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_interest_c);?></td>
						</tr>						
						<tr>
							<td class="listingtablelabel">Owner's Credit Card:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownercc_c);?></td>
							<td class="listingtablelabel">Owner Car Lease Payments:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownerlease_c);?></td>
						</tr>
						<tr>
							<td class="listingtablelabel">Owner's Cell Phone:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownercell_c);?></td>
							<td class="listingtablelabel">Owner's Fuel Expense:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownerfuel_c);?></td>
				 		</tr>
						<tr>
							<td class="listingtablelabel">Miscellaneous 1:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc6);?></td>
							<td class="listingtablelabel">Miscellaneous 2:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc7);?></td>
				 		</tr>
						<tr>
							<td class="listingtablelabel">Miscellaneous 3:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc8);?></td>
							<td class="listingtablelabel">Miscellaneous 4:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc9);?></td>
				 		</tr>
						<tr>
							<td class="listingtablelabel">Miscellaneous 5:</td>
							 <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc10);?></td>
						     <td>&nbsp;</td>
							 <td>&nbsp;</td>
				  		</tr>					
				</table>
	            
	            <h3 class=detailheader>Totals:</h3>
					<table id="listingtable">
						<tr>
							<td class="listingtablelabel">Total Expenses:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_total_expenses_c);?></td>
							<td class="listingtablelabel">Monthly Expenses:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_monthly_expense_c);?></td>
						 </tr>
						<tr>
							<td class="listingtablelabel">Yearly Expenses:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_yearlyexpense);?></td>
							<td class="listingtablelabel">Net Profit:</td>
							<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_net_profit_c);?></td>
						</tr>						
						</table>
	                    
	<!-- Gail's added tables end here -->	
						<?php elseif($isuserregistered): 
							_e('For more details on this listing, please add it to your data room.','bbcrm');				
						else: 
							_e("In order to see more details about this listing, please contact your broker to become registered.",'bbcrm');
						endif; ?>
								</div>
							</div>
	<?php

	if($portfoliolisting->c_release_status!= ""){
		echo '<input type=button onclick=location.href=\'/data-room\' class="portfolio_action_button portfolio-view" value="'.__('View Data Room','bbcrm').' &#10151;">';
	}
	if( $inportfolio && !$is_portfolio_hidden ){
	?>

		<form method="post" style="display:inline">
			<input type=hidden name="uid" value="<?php echo $crmid;?>">
			<input type=hidden name="pid" value="<?php echo $portfoliolisting->id;?>">
			<input type=hidden name="action" value="hide">
			<input type=submit value="<?php _e('Hide from Data Room','bbcrm');?> &#10006;" class="portfolio_action_button portfolio-remove">
		</form> 
		<?php 
	}

	if($inportfolio && $portfoliolisting->c_release_status != "Requested" && !$is_requested && !$isaddressreleased)
	{
		?>
		<form method="post" style="display:inline">
			<input type=hidden name="pid" value="<?php echo $portfoliolisting->id;?>">
			<input type=hidden name="action" value="request">
			<input type=submit value="<?php _e('Request This Address','bbcrm');?> &#9733;"  class="portfolio_action_button request-address" >
		</form>
	<?php }

	if(is_user_logged_in() && !$inportfolio){
		?>
			<form method="post" class="listing_add_to_portfolio_button">
				<input type=submit style="display:inline" value="<?php _e('Add to my Data Room','bbcrm');?> &#10010;" class="portfolio_action_button portfolio-add"  />
				<input type=hidden name="action" value="add_to_portfolio" />
				<input type=hidden name="id" value="<?echo $listing->id;?>" />
			</form>
	<?php } ?>

</div>


<div class="col-5 col-lg-5 col-md-6 col-sm-12" style="margin-top: 20px; ">

			<?php if ($buyerbroker->name != '') { ?>
			<div class="panel panel-default">
				<div style="background-color: #fff !important;" class="panel-heading">
					<h3 class="panel-title">
						<?php if(is_user_logged_in()){ echo 'Your Business Broker'; } else { echo 'Listing Broker'; } ?>
					</h3>
				</div>
				<div class="panel-body">
						<div class="al-agent-image"><?php
	                      if($brokerimg->fileName){
	                          ?>							
	                       <img src="<?php echo "http://".$apiserver."/uploads/media/".$brokerimg->uploadedBy."/".$brokerimg->fileName;?>" style="width:95px; height: auto;" align=right />
	                        <?php } ?>
	                    </div>
	                    <form method=POST id="broker_frm_<?php echo $buyerbroker->id; ?>" action="<?php echo get_permalink($bbcrm_option["bbcrm_pageselect_broker"]);?>">
		                    <input type=hidden name=eid value="<?php echo $buyerbroker->nameId; ?>">
	                    </form>
						<ul class="agentData">
							<li><h4><?php echo $buyerbroker->name ;?>&nbsp;</h4></li>
							<li>Phone: <strong><?php echo $buyerbroker->c_office;?></strong></li>
							<li>Mobile: <strong><?php echo $buyerbroker->c_mobile;?></strong></li>
							<li>Profile: <a href="javascript: document.getElementById('broker_frm_'+<?php echo $buyerbroker->id; ?>).submit();"><strong>view profile</strong></a></li>
							<li class="icon-links savelisting notsaved"></li>							
						</ul>
				</div>
			</div>
			<?php } ?>
			
			
			<div id="businesslinks" class="panel panel-default">
				<div style="background-color: #fff !important;" class="panel-heading">
					<h3 class="panel-title">
						Business Links / Tools
					</h3>
				</div>
				<div class="panel-body">
					<?php
					$hasMap = false;
					$hasCoordinates = false;
					$mapsLink = '';
					if($inportfolio && $isaddressreleased )
					{
						if ($listing -> c_listing_longitude_c != '' && $listing -> c_listing_latitude_c != '')
						{
							$hasMap = true;
							$hasCoordinates = true;
							//$mapsLink = '//www.google.com/maps/place/'.$listing -> c_listing_latitude_c.','.$listing -> c_listing_longitude_c;
							$mapsLink = '//maps.google.com/maps?q='.$listing -> c_listing_latitude_c.','.$listing -> c_listing_longitude_c;
						}
						elseif ($listing -> c_listing_postal_c != '' && $listing -> c_listing_city_c != '' && $listing -> c_listing_address_c != '')
						{
							$hasMap = true;
							//$mapsLink = '//www.google.com/maps/place/'.$listing -> c_listing_latitude_c.','.$listing -> c_listing_longitude_c;
							$mapsLink = '//maps.google.com/maps?daddr='.urlencode($listing -> c_listing_postal_c.' '.$listing -> c_listing_city_c.' '.$listing -> c_listing_address_c);
							$mapsAddress = urlencode($listing -> c_listing_postal_c.' '.$listing -> c_listing_city_c.' '.$listing -> c_listing_address_c);
						}
						elseif ($listing -> c_listing_town_c != '' && $listing -> c_listing_city_c != '')
						{
							$hasMap = true;
							$mapsLink = '//maps.google.com/maps?daddr='.urlencode($listing -> c_listing_town_c.' '.$listing -> c_listing_city_c.' '.$listing-> c_listing_address_c);
							$mapsAddress = urlencode($listing -> c_listing_town_c.' '.$listing -> c_listing_city_c.' '.$listing-> c_listing_address_c);					
							
						}
					} 
					else
					{
						if ($listing -> c_listing_town_c != '' && $listing -> c_listing_city_c != '')
						{
							$hasMap = true;
							$mapsLink = '//maps.google.com/maps?daddr='.urlencode($listing -> c_listing_town_c.' '.$listing -> c_listing_city_c);
							$mapsAddress = urlencode($listing -> c_listing_town_c.' '.$listing -> c_listing_city_c);
							
							
						}
						
					}
					
				?>
					<ul class="listReset listingLinks">

	<li class="icon-links"><a href="javascript:print();" class="printPage"><span class="glyphicon glyphicon-print"></span> Print Page</a></li>
	<li class="icon-links"><a rel="prettyPhotoIFRAME" title="Email this listing to a friend" href="mailto:?subject=Listing: <?php echo $generic_name; ?>&body=Check out <?php echo $generic_name; ?> at <?php echo get_site_url();?>/listing/<?php echo sanitize_title($generic_name)."--".$listing->id;?>"><span class="glyphicon glyphicon-envelope"></span> Email to a Friend</a></li>
	<li class="icon-links"><a href="" title="Superior Fruit and Vegetable Business for Sale – Ref: 2963" class="jQueryBookmark"><span class="glyphicon glyphicon-book"></span> Bookmark Page</a></li>
	<?php if ($mapsLink) { ?>
	<li class="icon-links" ><a href="<?php echo $mapsLink;?>" target="_blank"><span class="glyphicon glyphicon-map-marker"></span> Map directions</a></li>
	<?php } ?>
</ul>

				</div>
			</div>
			<?php if ($hasMap) { ?>
			<div class="panel panel-default" >
				<div style="background-color: #fff !important;" class="panel-heading">
					<h3 class="panel-title">
						Business Location
					</h3>
				</div>
				<div class="panel-body" id="business_map" style="height: 350px;">
					<?php if ($hasCoordinates) { ?>
					<script>
						//function initMap() {
					        var myLatLng = {lat: <?php echo $listing -> c_listing_latitude_c; ?>, lng: <?php echo $listing -> c_listing_longitude_c; ?>};
					
					        var map = new google.maps.Map(document.getElementById('business_map'), {
					          zoom: 16,
					          center: myLatLng
					        });
					
					        var marker = new google.maps.Marker({
					          position: myLatLng,
					          map: map,
					          title: '<?php echo $generic_name; ?>'
					        });
					     // }
				    </script>
				    <!--<script src="https://maps.googleapis.com/maps/api/js?callback=initMap" async defer></script>-->
					<?php } else { ?>
					<script>
						var geocoder = new google.maps.Geocoder();
				            geocoder.geocode({ 'address': '<?php echo $mapsAddress; ?>' }, function (results, status) {
					            if (status == google.maps.GeocoderStatus.OK) {
				                    var latitude = results[0].geometry.location.lat();
				                    var longitude = results[0].geometry.location.lng();
				                    
				                     var myLatLng = {lat: latitude, lng: longitude};
									    var map = new google.maps.Map(document.getElementById('business_map'), {
								          zoom: 10,
								          center: myLatLng
								        });
								
								        var marker = new google.maps.Marker({
								          position: myLatLng,
								          map: map,
								          title: '<?php echo $generic_name; ?>'
								        });
								        
				                } else {
				                   
				                }
						    });
						
					</script>		
					<?php } ?>
				</div>
			</div>
			<?php } ?>

</div>

</div> <!-- end of row -->

<?php

$json = x2apicall(array('_class'=>'Media/by:fileName='.$listingbroker->c_profilePicture.".json"));
$brokerimg =json_decode($json);
?>	

						</div><!-- .entry-content -->

					</div><!-- #post-## -->
		<br clear=all><br>
				
	</div>
</section>	
<?php
get_footer();

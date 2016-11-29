<?php $options = _WSH()->option();
//print_r($options);
global $wp_query,$listing,$listingtags,$bbcrm_option;
?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><!--<![endif]-->
<html <?php language_attributes(); ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
//print_r($wp_query);
$is_listing = get_query_var('listing');
if($is_listing){
?>

<meta name="Keywords" content="<?php echo join(',',$listingtags); ?>" />
<meta name="Description" content="<?php echo $listing->description; ?>" />

<!-- Google Maps -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAME3ZDfU7eQ73tjKlRxQY52mj18-OyuHU&callback=initMap"></script>


<?php
}
?>    
    <!-- Favicons - Touch Icons -->
    <?php if( sh_set( $options, 'site_favicon' ) ):?>
    	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo sh_set( $options, 'site_favicon' );?>">
	<?php endif;?>
	
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri()?>/js/html5shiv.min.js"></script>
        <script src="<?php echo get_template_directory_uri()?>/js/respond.min.js"></script>
    <![endif]-->

    <?php
global $pagetitle;

if(!empty($pagetitle)){
  echo "<title>".$pagetitle."</title>";
}
wp_enqueue_script('jquery');
wp_enqueue_script('my_script', get_stylesheet_directory_uri()."/js/lib.js", array('jquery'), '1.0.0');    
wp_head(); 
?>

<script>
  jQuery(document).ready(function(){

    <?php
    if(is_user_logged_in()){
      echo 'jQuery("#menu-item-7").remove()';
    }
    ?>

  });     
 </script>

<?php echo '</'.'head'.'>';?>
<script src="/crm/webTracker.php"></script>

<body <?php body_class('customize-support'); ?>>

<!-- Page Wrap ===========================================-->
    
  <!--======= TOP BAR =========-->
 <?php echo do_shortcode('[bbcrm_loginbar]'); ?> 
<!--header/sticky orig -->
  <!--======= HEADER =========-->
<header class="sticky">
<div class="container">  
      
      <!--======= LOGO =========-->
<div id="logo"><img src="<?php echo $bbcrm_option["bbcrm_design_logo"]; ?>" style="max-width:100%;max-height:100%"></div>
      <!--======= NAV =========-->
      <nav> 
        
        <!--======= MENU START =========-->
        <ul class="ownmenu">
		  <?php wp_nav_menu( array( 'theme_location' => 'main_menu', 'container_id' => 'navbar-collapse-1',

                                        'container_class'=>'navbar-collapse collapse',
                                        'menu_class'=>'nav navbar-nav navbar-right',
                                        'fallback_cb'=>false, 
                                        'items_wrap' => '%3$s', 
                                        'container'=>false, 
                                        'walker'=> new SH_Bootstrap_walker() 

                                    ) ); ?>	
        </ul>
        
        <!--======= SUBMIT COUPON =========-->
        <div class="sub-nav-co"> <a href="#."><i class="fa fa-search"></i></a> </div>
      </nav>
    </div>
  </header>  

<?php
/*
  Plugin Name: Business Brokers CRM Integration for WordPress
  Plugin URI: http://businessbrokerscrm.com
  Description: integration plugin for the BusinessBrokersCRM platform
  Version: 1.0
  Author: BusinessBrokersCRM
  Author URI: http://businessbrokerscrm.com
  Text Domain: bbcrm
*/
//ini_set('display_errors',1);
//error_reporting(E_ALL);

global $wp_query;
include_once ("_auth.php");
include_once ("functions-bbcrm_wp.php");
include_once ("functions-bbcrm_api.php");
include_once ("class.plugintemplates.php");
include_once ("options-bbcrm.php");

show_admin_bar(false);
$bbcrm_option = get_option( 'bbcrm_settings' );

global $bbcrm_option;

function bbcrm_load_textdomain() {
  load_plugin_textdomain( 'bbcrm', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'bbcrm_load_textdomain' );

function bbcrm_set_wp_title(){
global $pagetitle;
return $pagetitle;
}
add_filter('wp_head','bbcrm_set_wp_title');

function bbcrm_enqueue_scripts(){
	//wp_enqueue_script( 'ajaxform', get_stylesheet_directory_uri() . '/js/ajaxform.js', array(), '1.0.0', true );
	wp_enqueue_script('my_script',plugin_dir_url(__FILE__)."js/lib.js", array('jquery'), '1.0.0');
	wp_enqueue_script('web-tracker',get_bloginfo('url').'/crm/webTracker.php');
	wp_enqueue_style('bbcrm',plugin_dir_url(__FILE__)."css/style.css");
	wp_enqueue_style('bbcrm',plugin_dir_url(__FILE__)."css/wp_properties.css");
 	wp_register_script( 'jquery-form', '/wp-includes/js/jquery/jquery.form.js', array('jquery') );
}
add_action( 'wp_enqueue_scripts', 'bbcrm_enqueue_scripts' );


function bbcrm_set_listing_meta(){
   global $wp_query,$listing,$listingtags;

   $is_listing = get_query_var('listing');
    if($is_listing){
      $html = '<meta name="Keywords" content="'.join(',',$listingtags).'" />'.
     '<meta name="Description" content="'.$listing->description.'" />';

      $title = $listing->c_name_generic_c;
     }
}

function bbcrm_get_loginbar(){
bbcrm_load_textdomain();
	$inctemp = plugin_dir_path(__FILE__)."templates/loginbar.php";
	ob_start();
   include($inctemp);
   return ob_get_clean();
}
add_shortcode('bbcrm_loginbar','bbcrm_get_loginbar');


function get_featured_search( $atts ){
  $a = shortcode_atts( array(
'num'=>'4',    
'title' => 'Business for Sale',
    'type' => 'all',
    'broker'=>'',
    'featured'=>1,
    'franchise'=>false
  ), $atts );
	$search = plugin_dir_path(__FILE__)."templates/home-search.php";
	ob_start();
        include($search);
        return ob_get_clean();
}
add_shortcode('featuredsearch','get_featured_search');

function get_featured_listings($atts){
global $a;

$a = shortcode_atts( array(
'num'=>'4',    
'franchise'=>0,
    'broker'=>'',
    'featured'=>1,
    ), $atts );

	$search = plugin_dir_path(__FILE__)."templates/home-featured-alt.php";
	ob_start();
        include($search);
        return ob_get_clean();

}
add_shortcode('featuredlistings','get_featured_listings');

add_filter( 'no_texturize_shortcodes', 'shortcodes_to_exempt_from_wptexturize' );
function shortcodes_to_exempt_from_wptexturize( $shortcodes ) {
    $shortcodes[] = 'featuredlistings';
	$shortcodes[] = 'featuredsearch';
    return $shortcodes;
}


add_action( 'wp_ajax_contact_to_crm', 'contact_to_crm' );
add_action( 'wp_ajax_nopriv_contact_to_crm', 'contact_to_crm' );

wp_enqueue_script('jquery'); // I assume you registered it somewhere else
wp_localize_script('jquery', 'ajax_custom', array(
   'ajaxurl' => admin_url('admin-ajax.php')
));

//////////
function contact_to_crm(){

parse_str($_REQUEST["data"],$params);

$model = ($_REQUEST["model"])?$_REQUEST["model"]:"Contacts";

if(isset($params["_wpnonce"])){

$res = x2apipost(array("_class"=>$model."/","_data"=>$params));
//print_r($res);
exit;
}
}
//////////





define('MAX_LISTING_PER_PAGE', 3);
function pagination($maxPages,$p,$lpm1,$prev,$next, $max, $totalposts){
    $adjacents = 3;
    if($maxPages > 1)
    {
     
      $limits_start = (int)($p - 1) * $max + 1;
      $limits_end = $limits_start + $max - 1;

      if ($limits_end > $totalposts)
      {
        $limits_end = $totalposts; 
      }
       
      $get_query = http_build_query($_GET);
    $get_query = preg_replace('/page_no=\d*/i', '', $get_query);
    
    $pagination .="<small class='hidden-phone pageClass'>Showing <strong>$limits_start</strong> - <strong>$limits_end</strong> of <strong>$totalposts</strong></small>";
        $pagination .= "<ul class='pagination pagination-sm'>";
        //previous button
        if ($p > 1)
        {
      $pagination.= "<li><a href=\"?$get_query&page_no=$prev\"><small style='padding: 3px 0px;' class='glyphicon glyphicon-chevron-left'></small></a></li>";       
        }
        else
        {
           //$pagination.= "<li><span class=\"disabled\"><small class='glyphicon glyphicon-chevron-left'></small></span></li>";
        } 
        if ($maxPages < 7 + ($adjacents * 2)){
            for ($counter = 1; $counter <= $maxPages; $counter++){
                if ($counter == $p)
                $pagination.= "<li class='active'><span class=\"current\">$counter</span></li>";
                else
                $pagination.= "<li><a href=\"?$get_query&page_no=$counter\">$counter</a></li> ";}
        }elseif($maxPages > 5 + ($adjacents * 2)){
            if($p < 1 + ($adjacents * 2)){
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){
                    if ($counter == $p)
                    $pagination.= "<li><span class=\"current\">$counter</span></li> ";
                    else
                    $pagination.= "<li><a href=\"?$get_query&page_no=$counter\">$counter</a></li> ";
                }
                $pagination.= "<li><span>...</span></li>";
                $pagination.= "<li><a href=\"?$get_query&page_no=$lpm1\">$lpm1</a> ";
                $pagination.= "<li><a href=\"?$get_query&page_no=$maxPages\">$maxPages</a></li> ";
            }
            //in middle; hide some front and some back
            elseif($maxPages - ($adjacents * 2) > $p && $p > ($adjacents * 2)){
                $pagination.= "<li><a href=\"?$get_query&page_no=1\">1</a></li> ";
                $pagination.= "<li><a href=\"?$get_query&page_no=2\">2</a></li> ";
                $pagination.= "<li><span>...</span></li>";
                for ($counter = $p - $adjacents; $counter <= $p + $adjacents; $counter++){
                    if ($counter == $p)
                    $pagination.= "<li class='active'><span class=\"current\">$counter</span></li> ";
                    else
                    $pagination.= "<li><a href=\"?$get_query&page_no=$counter\">$counter</a></li> ";
                }
                $pagination.= "<li><span>...</span></li>";
                $pagination.= "<li><a href=\"?$get_query&page_no=$lpm1\">$lpm1</a></li> ";
                $pagination.= "<li><a href=\"?$get_query&page_no=$maxPages\">$maxPages</a></li> ";
            }else{
                $pagination.= "<li><a href=\"?$get_query&page_no=1\">1</a></li> ";
                $pagination.= "<li><a href=\"?$get_query&page_no=2\">2</a></li> ";
                $pagination.= "<li><span>...</span></li>";
                for ($counter = $maxPages - (2 + ($adjacents * 2)); $counter <= $maxPages; $counter++){
                    if ($counter == $p)
                    $pagination.= "<li class='active'><span class=\"current\">$counter</span></li> ";
                    else
                    $pagination.= "<li><a href=\"?$get_query&page_no=$counter\">$counter</a></li> ";
                }
            }
        }
        if ($p < $counter - 1)
        {
      $pagination.= "<li><a href=\"?$get_query&page_no=$next\"><small style='padding: 3px 0px;' class='glyphicon glyphicon-chevron-right'></small></a></li>";
        }
        else
        {
         //$pagination.= "<li><span class=\"disabled\"><small class='glyphicon glyphicon-chevron-right'></small></span></li>";  
        }
        $pagination.= "</ul>\n";
    }
    return $pagination;
}
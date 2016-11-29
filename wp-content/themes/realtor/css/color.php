<?php

//error_reporting(0);
/** Set ABSPATH for execution */

$root = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';

if( file_exists($root.'wp-config.php')) {
  include_once($root.'wp-config.php');
}
else {
  $newroot = dirname($root);
  if( file_exists($newroot.'wp-config.php'))
    include_once($newroot.'wp-config.php');
  else exit();
}

//define('ABSPATH', $root);
//include_once($root.'wp-config.php');
//define( 'WPINC', 'wp-includes' );

/* Convert hexdec color string to rgb(a) string */


function __hex2rgba($color, $opacity = false) {
 
	$default = 'rgb(0,0,0)';
 
	//Return default if no color provided
	if(empty($color))
          return $default; 
 
	//Sanitize $color if "#" is provided 
        if ($color[0] == '#' ) {
        	$color = substr( $color, 1 );
        }
 
        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }
 
        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);
 
        //Check if opacity is set(rgba or rgb)
        if($opacity){
        	if(abs($opacity) > 1)
        		$opacity = 1.0;
        	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
        	$output = 'rgb('.implode(",",$rgb).')';
        }
 
        //Return rgb(a) color string
        return $output;
}

$green_color = '#4caf50';
$orange_color = '#ff5722';
$header_menu_image= get_template_directory_uri()."/images/nav-head.png";
$options = _WSH()->option();
$green_color = sh_set( $options, 'custom_green_color_scheme' ) ? sh_set( $options, 'custom_green_color_scheme' ) : $green_color;
$orange_color = sh_set( $options, 'custom_orange_color_scheme' ) ? sh_set( $options, 'custom_orange_color_scheme' ) : $orange_color;
$header_menu_image = sh_set( $options, 'top_navigation_img' ) ? sh_set( $options, 'top_navigation_img' ) : $header_menu_image;
$color = '#'.$color;
ob_start(); ?>

/*** Text green Color Primary
------------------------------------------------ ***/

header .ownmenu ul.dropdown li a:hover,
.ownmenu li > .megamenu li:hover,
.ownmenu li > .megamenu li a:hover,
.property-slide .sale,
.properties li:hover .detail-sec a,
.pagination li a,
.load-more,
.call-us h6,
.mobile-app h3,
.what-we-do .nav-tabs li a:hover,
.what-we-do .nav-tabs li a:hover span,
.what-we-do .nav-tabs li a:hover i,
.what-we-do .nav-tabs .active a span,
.what-we-do .nav-tabs .active a i,
.services li .heading:hover,
.services li:hover .heading,
.blog-page .blog .post-admin h6,
.single-post blockquote:before,
.properti-detsil .categories li a:hover,
.contact-info i,
footer .recent-come li span {
  color: <?php echo esc_attr($green_color); ?>;
}

/*** Background green Color Primary
------------------------------------------------ ***/
.btn,
.find-sec .dropdown-menu a:hover,
.find-sec .selected a,
.home-3 .finder h6,
.home-3 .bnr-property .btn-1:hover,
.pagination>.active>a, .pagination>.active>a:focus, .pagination>.active>a:hover, .pagination>.active>span, .pagination>.active>span:focus, .pagination>.active>span:hover,
.pagination>li>a:focus, .pagination>li>a:hover, .pagination>li>span:focus, .pagination>li>span:hover,
.pagination li a:hover,
.load-more:hover,
.services li .icon,
.services li .heading,
.parthner .owl-dots .active span,
.sub-banner .breadcrumb,
.blog hr,
.properti-detsil .tags li a:hover,
footer hr,
footer .subcribe,
.noUi-connect,
.services li:hover .ser-hover {
	background: <?php echo esc_attr($green_color); ?>;
}


/*** border green colors ***/

.pagination li a,
.load-more {
	border: <?php echo esc_attr($green_color); ?>;
}

.pagination li a, .load-more, .noUi-handle{
 border:4px solid <?php echo esc_attr($green_color); ?>;
}

.find-sec::before,
.properti-detsil .tags li a:hover,
.find-sec::before{
	border-color: <?php echo esc_attr($green_color); ?>;
}

.ownmenu ul.dropdown,
.call-us .btn {
	border-bottom: <?php echo esc_attr($green_color); ?>;
}

/*** Text Orange Color Primary
------------------------------------------------ ***/
header nav li.active a,
header nav li:hover a,
.property-slide .pri-info .auther h6,
.properties-small .text-po .locat span,
#testimonials .testi h5,
.cost-price-content span,
.blog-page .blog .post-admin span,
.properti-detsil .categories span,
.properti-detsil .side-bar .recent-come li span,
.regi-sec li .forget,
.regi-sec li .forget a,
.regi-sec .checkbox label,
.contact-info a {
	color: <?php echo esc_attr($orange_color); ?>;
}


/*** Background Orange Color Primary
------------------------------------------------ ***/

.btn:hover,
.home-3 .bnr-property .btn-1,
.info_content .description .price-bg,
.properties li .price-bg,
.mobile-app hr,
.properti-detsil .sale-tag{
	background-color: <?php echo esc_attr($orange_color); ?>;
}

/*** border Orange colors ***/

.services li section:before{
	border: <?php echo esc_attr($orange_color); ?>;
}

.properti-detsil .home-in{
	border-bottom: <?php echo esc_attr($orange_color); ?>;
}

.properties .detail-sec{
	border-top: <?php echo esc_attr($orange_color); ?>;
}

header nav > ul > li:hover{
background:url("<?php  echo esc_attr($header_menu_image);  ?>") no-repeat scroll center 0;
}
<?php 

$out = ob_get_clean();
$expires_offset = 31536000; // 1 year
header('Content-Type: text/css; charset=UTF-8');
header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT');
header("Cache-Control: public, max-age=$expires_offset");
header('Vary: Accept-Encoding'); // Handle proxies
header('Content-Encoding: gzip');

echo gzencode($out);
exit;
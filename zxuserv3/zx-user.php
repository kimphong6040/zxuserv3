<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://kogi.asia
 * @since             1.0.2
 * @package           Zx_User
 *
 * @wordpress-plugin
 * Plugin Name:       ZX User
 * Plugin URI:        https://zxuser.kogi.asia
 * Description:       User Management by ZX
 * Version:           1.0.2
 * Author:            Kogi Dev
 * Author URI:        https://kogi.asia/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       zx-user
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
//date_default_timezone_set("Asia/Ho_Chi_Minh");
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ZX_USER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-zx-user-activator.php
 */
function activate_zx_user() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-zx-user-activator.php';
	Zx_User_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-zx-user-deactivator.php
 */
function deactivate_zx_user() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-zx-user-deactivator.php';
	Zx_User_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_zx_user' );
register_deactivation_hook( __FILE__, 'deactivate_zx_user' );
function enqueue_bootstrap() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery'), '4.3.1', true);
	wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), '1.13.6', true);
	wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js', array('jquery'), '1.13.6', true);
    wp_enqueue_style('datatables-css-main', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), '1.13.6', 'all');
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css', array(), '1.13.6', 'all');
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array('jquery'), '1.13.6', true);
	
}

add_action('admin_enqueue_scripts', 'enqueue_bootstrap');
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-zx-user.php';
require plugin_dir_path( __FILE__ ) . 'includes/admin-display.php';
require plugin_dir_path( __FILE__ ) . 'includes/histories-display.php';
require plugin_dir_path( __FILE__ ) . 'includes/setting-display.php';


function custom_menu() { 

  add_menu_page (
            'Lịch sử đăng nhập', 
            'Lịch sử đăng nhập', 
            'manage_options', 
            'zxusers', 
            'zxusermanager', 
            '', 
            '2'
    );
	add_submenu_page (
			'zxusers',
            'Cài đặt', 
            'Cài đặt', 
            'manage_options', 
            'zxusers-option', 
            'render_zxusers_settings_page', 
            '', 
            '2'
    );
}
add_action('admin_menu', 'custom_menu');
function add_login_history_page_to_admin_menu() {
    add_submenu_page(
        '', // Parent menu slug (trong ví dụ này, 'Thông tin Đăng nhập')
        'Lịch sử Đăng nhập', // Page title
        'Lịch sử Đăng nhập', // Menu title
        'manage_options',
        'login-history',
        'render_login_history_page'
    );
}


add_action('admin_menu', 'add_login_history_page_to_admin_menu');
function restrict_admin_menu_items(){
    $user = wp_get_current_user();
    if ( ! $user->has_cap( 'manage_options' ) ) {
        remove_menu_page( 'plugins.php' ); // Ẩn menu Plugin
        remove_menu_page( 'themes.php' ); // Ẩn menu Theme
        remove_menu_page( 'options-general.php' ); // Ẩn menu Settings
    }
}
add_action( 'admin_menu', 'restrict_admin_menu_items' );

function sendEmailNotify($username,$msg)
{
	$to = get_option('zxuser-email');
	$subject = 'Cảnh báo đăng nhập nhiều lần';
	$message = 'Cảnh báo User: '.$username.': '.$msg;

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
	);

	$result = wp_mail($to, $subject, $message, $headers);

	if ($result) {
		return true;
	} else {
		return false;
	}

}
function lockUser($user)
{
	global $wpdb;
	$table_users = $wpdb->prefix . 'users';
	$updateDB = $wpdb->query("UPDATE $table_users SET user_status = 2 WHERE id = '".$user->id."' ORDER BY id DESC LIMIT 1");
	update_user_meta( $uid, 'block_reason', 'Tài khoản đăng nhập vượt quá số thiết bị/IP cho phép');
}

function checkLogin($uid,$username)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'user_login_activities';
	
	$countIPToday = $wpdb->get_var("SELECT COUNT(DISTINCT ip_address) FROM $table_name WHERE user_id = '".$uid."' AND DATE(login_time) BETWEEN '".date("Y-m-d")."' AND '".date("Y-m-d")."'");
	$countDeviceToday = $wpdb->get_var("SELECT COUNT(DISTINCT device_info) FROM $table_name WHERE user_id = '".$uid."' AND DATE(login_time) BETWEEN '".date("Y-m-d")."' AND '".date("Y-m-d")."'");
	$countDeviceWeek = $wpdb->get_var("SELECT COUNT(DISTINCT device_info) FROM $table_name WHERE user_id = '".$uid."' AND YEARWEEK(login_time) = YEARWEEK(CURDATE())");
	
	
	$countDeviceMonth = $wpdb->get_var("SELECT COUNT(DISTINCT device_info) FROM $table_name WHERE user_id = '".$uid."' AND YEAR(login_time) = YEAR(CURDATE()) AND MONTH(login_time) = MONTH(CURDATE())");
	$maxIPNotify  = get_option('zxuser-limit-ip') ?? 2;
   	$maxDeviceNotify = get_option('zxuser-limit-devices') ?? 2;
   	$maxIPToday = get_option('zxuser-limit-ip-per-user') ?? 2;
   	$maxDeviceToday = get_option('zxuser-limit-devices-per-user') ?? 2;
	$maxDevicePerWeek = get_option('zxuser-limit-device-per-week') ?? 5;
   	$maxDevicePerMonth = get_option('zxuser-limit-devices-per-user') ?? 10;
	
	$message = "";
	if($countDeviceToday >= $maxDeviceNotify)
	{
		$message .= "Số thiết bị hôm nay vượt quá giới hạn, số thiết bị hiện tại: ".$countDeviceToday."<br>";
	}
	if($countIPToday >= $maxIPNotify)
	{
		$message .= "Số thiết bị hôm nay vượt quá giới hạn, số IP hiện tại: ".$countIPToday."<br>";
	}
	if($countIPToday >= $maxIPNotify || $countDeviceToday >= $maxDeviceNotify)
	{
		sendEmailNotify($username,$message);
	}
	if($countIPToday > $maxIPToday ||  $countDeviceToday > $maxDeviceToday || $countDeviceWeek > $maxDevicePerWeek || $countDeviceMonth > $maxDevicePerMonth)
	{
		$table_users = $wpdb->prefix . 'users';
		$updateDB = $wpdb->query("UPDATE $table_users SET user_status = 2 WHERE id = '".$uid."' ORDER BY id DESC LIMIT 1");
		update_user_meta( $uid, 'block_reason', 'Tài khoản đăng nhập vượt quá số thiết bị/IP cho phép');
		displayBlock($uid);
	}
	
}

function getLocation($ip)
{
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://ip-api.com/json/'.$ip,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        //'apikey: e11ZxnjCp4hm6HxI4MRa9WG5VK52zxLl'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    $datas = json_decode($response,true);
    if($datas)
    {
        return $datas["city"].", ".$datas["country"];    
    }
    else
    {
        return "N/A";
    }
    
}
function displayBlock($uid)
{
	$notify = get_user_meta( $uid, 'block_reason', true );
	echo '
	<link href="https://fonts.googleapis.com/css?family=Inter:300,700" rel="stylesheet">
	<style>
				
	#notfound {
	  position: relative;
	  height: 100vh;
	}
	
	#notfound .notfound {
	  position: absolute;
	  left: 50%;
	  top: 50%;
	  -webkit-transform: translate(-50%, -50%);
		  -ms-transform: translate(-50%, -50%);
			  transform: translate(-50%, -50%);
	}
	
	.notfound {
	  max-width: 520px;
	  width: 100%;
	  text-align: center;
	  line-height: 1.4;
	}
	
	.notfound .notfound-404 {
	  height: 190px;
	}
	
	.notfound .notfound-404 h1 {
	  font-family: "Inter", sans-serif;
	  font-size: 146px;
	  font-weight: 700;
	  margin: 0px;
	  color: #232323;
	}
	
	.notfound .notfound-404 h1>span {
	  display: inline-block;
	  width: 120px;
	  height: 120px;
	  background-image: url("../img/emoji.png");
	  background-size: cover;
	  -webkit-transform: scale(1.4);
		  -ms-transform: scale(1.4);
			  transform: scale(1.4);
	  z-index: -1;
	}
	
	.notfound h2 {
	  font-family: "Inter", sans-serif;
	  font-size: 22px;
	  font-weight: 700;
	  margin: 0;
	  text-transform: uppercase;
	  color: #232323;
	}
	
	.notfound p {
	  font-family: "Inter", sans-serif;
	  color: #787878;
	  font-weight: 300;
	}
	
	.notfound a {
	  font-family: "Inter", sans-serif;
	  display: inline-block;
	  padding: 12px 30px;
	  font-weight: 700;
	  background-color: #f99827;
	  color: #fff;
	  border-radius: 40px;
	  text-decoration: none;
	  -webkit-transition: 0.2s all;
	  transition: 0.2s all;
	}
	
	.notfound a:hover {
	  opacity: 0.8;
	}
	
	@media only screen and (max-width: 767px) {
	  .notfound .notfound-404 {
		height: 115px;
	  }
	  .notfound .notfound-404 h1 {
		font-size: 86px;
	  }
	  .notfound .notfound-404 h1>span {
		width: 86px;
		height: 86px;
	  }
	}

	</style>
	<div id="notfound">
		<div class="notfound">
			<div class="notfound-404">
				<h1>403</h1>
			</div>
			<h2>Tài khoản của bạn đã bị khoá</h2>
			<p>Lý do: '.$notify.'</p>
			<a href="'.site_url().'">Quay lại trang chủ</a>
		</div>
	</div>';
	wp_logout();
	die();
}
function update_user_login_activities($user_login, $user) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_login_activities';
    $user_id = $user->ID;
    
    
    $login_time = current_time('mysql');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $device_info = $_SERVER['HTTP_USER_AGENT'];
    $locationData = getLocation($ip_address);
	$countIPToday = $wpdb->get_var("SELECT COUNT(id) as countIP FROM $table_name WHERE user_id = '".$user_id."' AND ip_address = '".$ip_address."'");
	$countDeviceToday = $wpdb->get_var("SELECT COUNT(id) as countDevice FROM $table_name WHERE user_id = '".$user_id."' AND device_info = '".$device_info."'");
	
	if($countIPToday == 0 || $countDeviceToday == 0)
	{
		$sql = "SELECT count(id) FROM user_login_today WHERE uid = '".$user_id."'";
		$checkexisted = $wpdb->get_var($sql);
		if($checkexisted <= 0)
		{
			$wpdb->insert(
				'user_login_today',
				array(
					'uid' => $user_id,
					'login_ip' => 1,
					'login_device' => 1,
				),
				array(
					'%d',
					'%d',
					'%d',
				)
			);
		}
		else
		{
			$arrQ = [];
			if($countIPToday == 0)
			{	
				$arrQ[] = 'login_ip = login_ip + 1';
			}
			if($countDeviceToday == 0)
			{	
				$arrQ[] = 'login_device = login_device + 1';
			}
			if(count($arrQ))
			{
				$sq = implode(", ", $arrQ);
				$sql = "UPDATE user_login_today SET ".$sq.", log_date = '".date("Y-m-d H:i:s")."' WHERE uid = '".$user_id."'";
				$wpdb->query($sql);
			}
		}
		
	}
	$wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'login_time' => $login_time,
            'ip_address' => $ip_address,
            'device_info' => $device_info,
            'location_data' => $locationData,
        ),
        array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
        )
    );
    
    if($user->user_status == 2)
    {
        displayBlock($user->id);
    }
	checkLogin($user_id,$user->user_login);
}

add_action('wp_login', 'update_user_login_activities', 10, 2);

function custom_settings_init() {
    register_setting(
        'custom-settings-group',
        'zxuser-limit',
        'sanitize_callback'
    );
	register_setting(
        'custom-settings-group',
        'zxuser-limit-ip',
        'sanitize_callback'
    );
	register_setting(
        'custom-settings-group',
        'zxuser-limit-devices',
        'sanitize_callback'
    );
	register_setting(
        'custom-settings-group',
        'zxuser-email',
        'sanitize_callback'
    );
    register_setting(
        'custom-settings-group',
        'zxuser-block-reason',
        'sanitize_callback'
    );
    register_setting(
        'custom-settings-group',
        'zxuser-unblock-reason',
        'sanitize_callback'
    );
    register_setting(
        'custom-settings-group',
        'zxuser-limit-ip-per-user',
        'sanitize_callback'
    );
    register_setting(
        'custom-settings-group',
        'zxuser-limit-devices-per-user',
        'sanitize_callback'
    );
    register_setting(
        'custom-settings-group',
        'zxuser-limit-device-per-week',
        'sanitize_callback'
    );
    register_setting(
        'custom-settings-group',
        'zxuser-limit-device-per-month',
        'sanitize_callback'
    );

    add_settings_section(
        'custom-settings-section',
        'Cài đặt hiển thị dữ liệu',
        'section_callback',
        'custom-settings-page'
    );

    add_settings_field(
        'zxuser-limit',
        'Số dòng hiển thị',
        'field_callback',
        'custom-settings-page',
        'custom-settings-section'
    );
	add_settings_field(
        'zxuser-limit-ip',
        'Số IP cảnh báo',
        'field_callback2',
        'custom-settings-page',
        'custom-settings-section'
    );
	add_settings_field(
        'zxuser-limit-devices',
        'Số thiết bị cảnh báo',
        'field_callback4',
        'custom-settings-page',
        'custom-settings-section'
    );
	add_settings_field(
        'zxuser-email',
        'Email nhận thông báo',
        'field_callback3',
        'custom-settings-page',
        'custom-settings-section'
    );
    add_settings_field(
        'zxuser-block-reason',
        'Lý do khóa',
        'field_callback5',
        'custom-settings-page',
        'custom-settings-section'
    );
    add_settings_field(
        'zxuser-unblock-reason',
        'Lý do mở khóa',
        'field_callback6',
        'custom-settings-page',
        'custom-settings-section'
    );
}

function sanitize_callback($input) {
    return sanitize_text_field($input);
}

function section_callback() {
    // Nội dung phần section
}

function field_callback() {
    $value = get_option('zxuser-limit');
    echo "<input type='text' name='zxuser-limit' value='$value' />";
}

function field_callback2() {
    $value = get_option('zxuser-limit-ip');
    echo "<input type='text' name='zxuser-limit-ip' value='$value' />";
}
function field_callback3() {
    $value = get_option('zxuser-email');
    echo "<input type='text' name='zxuser-email' value='$value' />";
}
function field_callback4() {
    $value = get_option('zxuser-limit-devices');
    echo "<input type='text' name='zxuser-limit-devices' value='$value' />";
}
function field_callback5() {
    $value = get_option('zxuser-block-reason');
    echo "<textarea style='width: 500px;' rows=10 name='zxuser-block-reason' placeholder='Cách nhau bởi dấu ,'>$value</textarea>";
}

function field_callback6() {
    $value = get_option('zxuser-unblock-reason');
    echo "<textarea style='width: 500px;' rows=10 name='zxuser-unblock-reason' placeholder='Cách nhau bởi dấu ,'>$value</textarea>";
}

add_action('admin_init', 'custom_settings_init');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_zx_user() {

	$plugin = new Zx_User();
	$plugin->run();

}
run_zx_user();

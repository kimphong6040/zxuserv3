<?php

   function zxusermanager()
   {
	
   	global $wpdb;
   
       $table_name_activities = $wpdb->prefix . 'user_login_activities';
   	$table_name_users = $wpdb->prefix . 'users';
	$page = $_GET['paged'] ?? 1;
   	$current_page = max(1, intval($page));
       $items_per_page = get_option('zxuser-limit') ?? 10; 
       $offset = ($current_page - 1) * $items_per_page;
   	
   	$ipSearch = '';
   	if(!empty($_GET['ip']) && $_GET['ip'] != "")
   	{
   		$ipSearch = " AND id IN(SELECT user_id FROM $table_name_activities WHERE ip_address LIKE '%".sanitize_text_field($_GET['ip'])."%')";
   	}
   	$deviceSearch = '';
   	if(!empty($_GET['device']) && $_GET['device'] != "")
   	{
   		$deviceSearch = " AND id IN(SELECT user_id FROM $table_name_activities WHERE device_info LIKE '%".sanitize_text_field($_GET['device'])."%')";
   	}
   	$usernameSearch = '';
   	if(!empty($_GET['username']) && $_GET['username'] != "")
   	{
   		$usernameSearch = " AND user_login LIKE '%".sanitize_text_field($_GET['username'])."%'";
   	}
   	
   	$emailSearch = '';
   	if(!empty($_GET['email']) && $_GET['email'] != "")
   	{
   		$deviceSearch = " AND user_email LIKE '%".sanitize_text_field($_GET['email'])."%'";
   	}
   	$sql = "
   	    SELECT
   		u.id AS uid,
        u.user_login AS username,
        u.user_email AS email,
        u.user_status as user_status,
        l.login_ip as numips,
        l.login_device as numdevices,
        l.log_date as lastlogin
        FROM $table_name_users as u
        LEFT JOIN user_login_today as l ON u.id = l.uid
        WHERE u.id > 0 ".$usernameSearch.$emailSearch.$ipSearch.$deviceSearch."
        ORDER BY
            numips
        DESC
        LIMIT $offset, $items_per_page
   	";
   	$users = $wpdb->get_results($sql);
   	$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_users");
   
       $page_links = paginate_links(array(
           'base'    => add_query_arg('paged', '%#%'),
           'format'  => '',
           'prev_text' => __('&laquo; Trang trước', 'text-domain'),
           'next_text' => __('Trang tiếp theo &raquo;', 'text-domain'),
           'total'   => ceil($total_items / $items_per_page),
           'current' => $current_page
       ));
   	$form_action_url = admin_url('admin.php?page=zxusers');
   	$maxIPDisplay  = get_option('zxuser-limit-ip') ?? 2;
   	$maxDeviceDisplay = get_option('zxuser-limit-devices') ?? 2;
   	?>
<div class="container-fluid">
   <div class="row">
      <div class="col-12">
         <div class="card table-card">
            <h5 class="card-header">Quản lý Users</h5>
            <div class="card-body">
               <form method="GET" action="<?php echo admin_url( 'admin.php'); ?>" class="form-inline mt-2">
                  <input type="hidden" name="page" value="zxusers" />
                  <div class="form-group mr-2">
                     <label for="ipInput" class="sr-only">Username</label>
                     <input type="text" class="form-control" value="<?php echo $_GET['username'] ?? "";?>" name="username" id="username" placeholder="Username">
                  </div>
                  <div class="form-group mr-2">
                     <label for="ipInput" class="sr-only">Email</label>
                     <input type="text" class="form-control" value="<?php echo $_GET['email'] ?? "";?>" name="email" id="email" placeholder="Email">
                  </div>
                  <div class="form-group mr-2">
                     <label for="ipInput" class="sr-only">IP Address</label>
                     <input type="text" class="form-control" value="<?php echo $_GET['ip'] ?? "";?>" name="ip" id="ip" placeholder="IP Address">
                  </div>
                  <div class="form-group mr-2">
                     <label for="deviceInput" class="sr-only">Device</label>
                     <input type="text" class="form-control" value="<?php echo $_GET['device'] ?? "";?>" name="device" id="device" placeholder="Device">
                  </div>
                  <button type="submit" class="btn btn-primary">Tìm kiếm</button>
               </form>
               <table id="users-table" class="table table-striped table-bordered table-hover mt-2">
                  <thead>
                     <tr>
                        <th class="text-center" scope="col">#</th>
                        <th class="" scope="col">Đăng nhập lần cuối</th>
                        <th class="" scope="col">UserName</th>
                        <th class="" scope="col">Email</th>
                        <th class="text-center" scope="col">Tổng số IP</th>
                        <th class="text-center" scope="col">Tổng số thiết bị</th>
                        <th class="text-center" scope="col">Thao tác</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                        $i = 1;
                        foreach($users as $u)
                        {
                        ?>
                     <tr>
                        <th class="text-center" scope="row"><?php echo $i;?></th>
                        <td><?php echo $u->lastlogin; ?> </td>
                        <td><?php echo $u->username; ?> </td>
                        <td><?php echo $u->email;?></td>
                        <td class="text-center"><button type="button" class="btn <?php echo ($u->numips >= $maxIPDisplay) ? 'btn-danger' : 'btn-outline-secondary'; ?> "><?php echo $u->numips ?? 0;?></button></td>
                        <td class="text-center"><button type="button" class="btn <?php echo ($u->numdevices >= $maxDeviceDisplay) ? 'btn-danger' : 'btn-outline-secondary'; ?> "><?php echo $u->numdevices ?? 0;?></button></td>
                        <td class="text-center">
							<a href="#" data-id="<?php echo $u->uid;?>" data-name="<?php echo $u->username; ?>" class="btn btn-primary btn-sm btn-view-detail">Chi tiết</a>
							
							<a href="#" data-id="<?php echo $u->uid;?>" data-name="<?php echo $u->username; ?>" class="btn btn-danger btn-sm btn-reset-alert">Reset</a>
							<a href="#" data-id="<?php echo $u->uid;?>" data-stag="<?php echo ($u->user_status == 0) ? 'active' : 'deactive'; ?>" data-name="<?php echo $u->username; ?>" class="btn btn-warning btn-sm btn-lock-unlock"><?php echo ($u->user_status == 0) ? "Khóa" : "Mở khóa"; ?></a>
						</td>
                     </tr>
                     <?php
                        $i++;
                        }
                        ?>
                  </tbody>
               </table>
               
            </div>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="modalLoginDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Chi tiết lịch sử đăng nhập của user: <span id="title_username"></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<table id="users-detail-table" class="table table-striped table-bordered table-hover">
		  <thead>
			<tr>
			  <th class="text-center" scope="col">#</th>
			  <th class="text-center" scope="col">Thời gian</th>
			  <th class="text-center" scope="col">Địa chỉ IP</th>
			  <th class="text-center" scope="col">Vị trí</th>
			  <th class="text-center" scope="col">Thiết bị</th>
			  
			</tr>
		  </thead>
		  <tbody id="login-detail-data">
		  </tbody>
		</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
$blockreasons = get_option('zxuser-block-reason');
$blockreasons = explode(",",$blockreasons);
$displayBlockReason = [];
foreach($blockreasons as $r)
{
    $displayBlockReason[$r] = $r;
}
$unblockreasons = get_option('zxuser-unblock-reason');
$unblockreasons = explode(",",$unblockreasons);
$displayunBlockReason = [];
foreach($unblockreasons as $r)
{
    $displayunBlockReason[$r] = $r;
}
?>
<input type="hidden" id="block-reason-list" value='<?php echo json_encode($displayBlockReason); ?>'>
<input type="hidden" id="unblock-reason-list" value='<?php echo json_encode($displayunBlockReason); ?>'>
	<script>
    jQuery(document).ready(function(e) {
        
		jQuery(".btn-reset-alert").on("click", function(e){
			var btn = jQuery(this);
            var uid = btn.attr("data-id");
            if (confirm('Bạn có chắc muốn reset bộ đếm cho user này không?')) {
                var wp_ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
    			jQuery.ajax({
                    url: wp_ajax_url,
                    type: 'POST',
                    data:  {
                                action: "zxuser_disable_alert",
    							uid : uid,
                            },
                    dataType: 'json',
                    success: function(data, textStatus, jqXHR){ 
    					alert(data.message);
    					location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                    
                    }          
    			});
            } else {
            }
			
			return false;
		});
        jQuery(".btn-view-detail").click(function(e) {
			jQuery("#users-detail-table").DataTable().clear().destroy();
			jQuery("#login-detail-data").empty();
			var btn = jQuery(this);
            var uid = btn.attr("data-id");
			var username = btn.attr("data-name");
			var wp_ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
            jQuery.ajax({
                url: wp_ajax_url,
                type: 'POST',
                data:  {
                            action: "zxuser_load_login_detail",
							uid : uid,
                        },
                dataType: 'json',
                success: function(data, textStatus, jqXHR){ 
					jQuery("#title_username").html(username);
                    console.log(data); //return false;
					jQuery.each(data.data, function(key, value){
                        let tr = jQuery('<tr>').append(
                            jQuery('<td>').text(value.no).addClass("text-center"),
                            jQuery('<td>').text(value.login_time),
                            jQuery('<td>').text(value.ip_address),
                            jQuery('<td>').text(value.location_data),
                            jQuery('<td>').text(value.device_info),
                        );
                        jQuery("#login-detail-data").append(tr);
                    });
					jQuery("#users-detail-table").DataTable({
						paging: true,
						pageLength: 5,
						lengthMenu: [ 5, 10, 25, 50, 75, 100 ]
					});
					jQuery("#modalLoginDetail").modal("show");
                },
                error: function(jqXHR, textStatus, errorThrown){
                
                }          
            });
            return false;
        });
        jQuery(".btn-lock-unlock").on("click", function(e){
            var btn = jQuery(this);
            var uid = btn.data("id");
            var name = btn.data("name");
            var stag = btn.data("stag");
            var blockReason = jQuery('#block-reason-list').val();
            blockReasonJSON = JSON.parse(blockReason);
            var unblockReason = jQuery('#unblock-reason-list').val();
            unblockReasonJSON = JSON.parse(unblockReason);
            var logString = "";
            var displayValue;
            if(stag == "active")
            {
                logString = "khóa";
                displayValue = blockReasonJSON;
            }
            else
            {
                logString = "mở khóa";
                displayValue = unblockReasonJSON;
            }
            Swal.fire({
                icon: 'question',
                title: 'Oops...',
              title: 'Bạn có chắc chắn muốn ' + logString + ' người dùng: ' +name + '?',
              input: 'select',
              inputOptions: displayValue,
              inputPlaceholder: 'Chọn lý do ' + logString,
              showCancelButton: true,
              preConfirm: (reason) => {
                  var wp_ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                  jQuery.ajax({
                    url: wp_ajax_url,
                    type: 'POST',
                    data:  {
                                action: "zxuser_update_user_status",
    							uid : uid,
    							stag : stag,
    							reason: reason
                            },
                    dataType: 'json',
                    success: function(data, textStatus, jqXHR){ 
    					alert(data.message);
    					location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                    
                    }          
    			});
              }
            })
           return false; 
        });
    });
</script>
<?php
   }
   function zxuser_disable_alert()
   {
	   header("Content-Type: application/json", true);
		global $wpdb;
		$uid = $_POST['uid'] ?? "";
		$updateDB = $wpdb->query("UPDATE user_login_today SET login_ip = 0, login_device = 0, log_date = null WHERE uid = '".$uid."' ORDER BY id DESC LIMIT 1");
		wp_send_json(['status' => 200, 'message' => 'Success']);
		die();
   }
   add_action( 'wp_ajax_zxuser_disable_alert', 'zxuser_disable_alert' );
	add_action('wp_ajax_nopriv_zxuser_disable_alert', 'zxuser_disable_alert');
	function sendEmailUnblock($email,$username,$msg)
    {
    	$to = $email;
    	$subject = 'Tài khoản của bạn đã được mở khoá';
    	$message = 'Xin chào: <b>'.$username.'</b><br>
    	Tài khoản của bạn đã được mở khoá với nội dung từ admin: '.$msg;
    
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
	function zxuser_update_user_status()
   {
	   header("Content-Type: application/json", true);
		global $wpdb;
		$table_users = $wpdb->prefix . 'users';
		$uid = $_POST['uid'] ?? "";
		$status = ($_POST['stag'] == "active") ? 2 : 0;
		$updateDB = $wpdb->query("UPDATE $table_users SET user_status = $status WHERE id = '".$uid."' ORDER BY id DESC LIMIT 1");
		
		if($status == 2)
		{
		    	update_user_meta( $uid, 'block_reason', sanitize_text_field($_POST['reason']) );
		    $updateDB = $wpdb->query("UPDATE user_login_today SET block_reason = '".sanitize_text_field($_POST['reason'])."' WHERE uid = '".$uid."' ORDER BY id DESC LIMIT 1");    
		}
		else
		{
		    	update_user_meta( $uid, 'unblock_reason', sanitize_text_field($_POST['reason']) );
		    $updateDB = $wpdb->query("UPDATE user_login_today SET unblock_reason = '".sanitize_text_field($_POST['reason'])."' WHERE uid = '".$uid."' ORDER BY id DESC LIMIT 1");    
		}
		if($status == 0)
		{
	        $user = $wpdb->get_row("SELECT * FROM $table_users WHERE id = '".$uid."' ORDER BY id DESC LIMIT 1");
		    sendEmailUnblock($user->user_email,$user->user_login,$_POST['reason']);
		}
		wp_send_json(['status' => 200, 'message' => 'Success']);
		die();
   }
   add_action( 'wp_ajax_zxuser_update_user_status', 'zxuser_update_user_status' );
	add_action('wp_ajax_nopriv_zxuser_update_user_status', 'zxuser_update_user_status');
	
	
   function zxuser_load_login_detail(){
		header("Content-Type: application/json", true);
		global $wpdb;
		$table_name_activities = $wpdb->prefix . 'user_login_activities';
		$uid = $_POST['uid'] ?? "";
		
		$results = $wpdb->get_results("
			SELECT 
				activities.login_time AS login_time,
				activities.ip_address AS ip_address,
				activities.location_data AS location_data,
				activities.device_info AS device_info
			FROM $table_name_activities AS activities
			WHERE activities.user_id = '".sanitize_text_field($uid)."'
			ORDER BY activities.login_time DESC LIMIT 100
		");
		$datas = [];
		$i = 1;
		foreach($results as $d)
		{
			$datas[] = [
				'no' => $i,
				'login_time' => $d->login_time,
				'ip_address' => $d->ip_address,
				'location_data' => $d->location_data,
				'device_info' => $d->device_info,
			];
			$i++;
		}
		$arr_response = array();
		
		$arr_response = array(
								'status' => 200,
								'data' => $datas,
							);
		wp_send_json($arr_response);
		die();
		
	}
	add_action( 'wp_ajax_zxuser_load_login_detail', 'zxuser_load_login_detail' );
	add_action('wp_ajax_nopriv_zxuser_load_login_detail', 'zxuser_load_login_detail');
   ?>
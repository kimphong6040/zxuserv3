<?php
function render_login_history_page() {
    global $wpdb;
    $table_name_activities = $wpdb->prefix . 'user_login_activities';

    $results = $wpdb->get_results("
        SELECT 
            users.user_login AS username,
            users.user_email AS email,
            activities.login_time AS login_time,
            activities.ip_address AS ip_address,
            activities.location_data AS location_data,
            activities.device_info AS device_info
        FROM $table_name_activities AS activities
        JOIN {$wpdb->users} AS users ON activities.user_id = users.ID
		WHERE users.id = '".sanitize_text_field($_GET['id'])."'
        ORDER BY activities.login_time DESC
    ");

    
	?>
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card table-card">
				  <h5 class="card-header">Chi tiết lịch sử đăng nhập</h5>
				  <div class="card-body">
					<table id="users-table" class="table table-striped table-bordered table-hover">
					  <thead>
						<tr>
						  <th class="text-center" scope="col">#</th>
						  <th class="text-center" scope="col">Thời gian</th>
						  <th class="text-center" scope="col">Địa chỉ IP</th>
						  <th class="text-center" scope="col">Vị trí</th>
						  <th class="text-center" scope="col">Thiết bị</th>
						  
						</tr>
					  </thead>
					  <tbody>
						<?php
						$i = 1;
						foreach($results as $r)
						{
						?>
						<tr>
						  <th class="text-center" scope="row"><?php echo $i;?></th>
						  <td><?php echo $r->login_time; ?> </td>
						  <td><?php echo $r->ip_address;?></td>
						  <td><?php echo $r->location_data;?></td>
						  <td class="text-center"><?php echo $r->device_info;?></td>
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
	<?php
}


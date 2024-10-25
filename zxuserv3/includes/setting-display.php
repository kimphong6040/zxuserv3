<?php
function render_zxusers_settings_page() {
    ?>
    <div class="wrap">
        <h1>Cài đặt</h1>
        <form method="post" action="options.php">
			<div class="form-group row">
				<label for="zxuser-limit" class="col-sm-2 col-form-label">Số dòng hiển thị</label>
				<div class="col-sm-3">
				  <input type="text"  class="form-control-plaintext" id="zxuser-limit" name="zxuser-limit" value="<?php echo get_option('zxuser-limit');?>">
				</div>
			</div>
			<div class="form-group row">
				<label for="zxuser-limit-ip" class="col-sm-2 col-form-label">Số IP cảnh báo</label>
				<div class="col-sm-3">
				  <input type="text"  class="form-control-plaintext" id="zxuser-limit-ip" name="zxuser-limit-ip" value="<?php echo get_option('zxuser-limit-ip');?>">
				</div>
				<label for="zxuser-limit-devices" class="col-sm-2 col-form-label">Số thiết bị cảnh báo</label>
				<div class="col-sm-3">
				  <input type="text"  class="form-control-plaintext" id="zxuser-limit-devices" name="zxuser-limit-devices" value="<?php echo get_option('zxuser-limit-devices');?>">
				</div>
			</div>
			<div class="form-group row">
				<label for="zxuser-limit-ip-per-user" class="col-sm-2 col-form-label">Số IP tối đa/user</label>
				<div class="col-sm-3">
				  <input type="text"  class="form-control-plaintext" id="zxuser-limit-ip-per-user" name="zxuser-limit-ip-per-user" value="<?php echo get_option('zxuser-limit-ip-per-user');?>">
				</div>
				<label for="zxuser-limit-devices-per-user" class="col-sm-2 col-form-label">Số thiết bị tối đa/user</label>
				<div class="col-sm-3">
				  <input type="text"  class="form-control-plaintext" id="zxuser-limit-devices-per-user" name="zxuser-limit-devices-per-user" value="<?php echo get_option('zxuser-limit-devices-per-user');?>">
				</div>
			</div>
			<div class="form-group row">
				<label for="zxuser-limit-device-per-week" class="col-sm-2 col-form-label">Số TB tối đa/tuần</label>
				<div class="col-sm-3">
				  <input type="text"  class="form-control-plaintext" id="zxuser-limit-device-per-week" name="zxuser-limit-device-per-week" value="<?php echo get_option('zxuser-limit-device-per-week');?>">
				</div>
				<label for="zxuser-limit-device-per-month" class="col-sm-2 col-form-label">Số TB tối đa/tháng</label>
				<div class="col-sm-3">
				  <input type="text"  class="form-control-plaintext" id="zxuser-limit-device-per-month" name="zxuser-limit-device-per-month" value="<?php echo get_option('zxuser-limit-device-per-month');?>">
				</div>
			</div>
			<div class="form-group row">
				<label for="zxuser-email" class="col-sm-2 col-form-label">Email nhận thông báo</label>
				<div class="col-sm-3">
				  <input type="text"  class="form-control-plaintext" id="zxuser-email" name="zxuser-email" value="<?php echo get_option('zxuser-email');?>">
				</div>
			</div>
			<div class="form-group row">
				<label for="zxuser-block-reason" class="col-sm-2 col-form-label">Lý do khoá</label>
				<div class="col-sm-3">
					<textarea style='width: 500px;' class="form-control" rows=5 name='zxuser-block-reason' placeholder='Cách nhau bởi dấu ,'><?php echo get_option('zxuser-block-reason');?></textarea>
				  
				</div>
			</div>
			<div class="form-group row">
				<label for="zxuser-unblock-reason" class="col-sm-2 col-form-label">Lý do mở khoá</label>
				<div class="col-sm-3">
					<textarea style='width: 500px;' class="form-control" rows=5 name='zxuser-unblock-reason' placeholder='Cách nhau bởi dấu ,'><?php echo get_option('zxuser-unblock-reason');?></textarea>
				  
				</div>
			</div>
            <?php
            settings_fields('custom-settings-group');
            //do_settings_sections('custom-settings-page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

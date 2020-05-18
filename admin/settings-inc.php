<div class="wrap">
	<!-- <h1><?php esc_html( get_admin_page_title() ); ?></h1> -->
	<h1>OpenSimulator</h1>	<?php screen_icon(); ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'w4os_options_group' ); ?>
		<table class=form-table>
			<tr><th colspan=2>
				<h2><?php _e("Grid");?></h2>
			</th></tr>
			<tr valign="top">
				<th scope="row"><label for="w4os_grid_name"><?php _e("Grid name", "w4os");?></label></th>
				<td><input type="text" class=regular-text id="w4os_grid_name" name="w4os_grid_name" value="<?php echo get_option('w4os_grid_name'); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="w4os_login_uri"><?php _e("Login URI", "w4os");?></label></th>
				<td><input type="text" class=regular-text id="w4os_login_uri" name="w4os_login_uri" value="<?php echo get_option('w4os_login_uri'); ?>" /></td>
			</tr>
			<tr><th colspan=2>
				<h2><?php _e("Robust server connection", "w4os");?></h2>
			</th></tr>
			<?php if(! W4OS_DB_CONNECTED) { ?>
				<tr><td colspan=2>
					<strong><?php _e("Required tables are missing, check your connection settings or your database");?></strong>
				</td></tr>
			<?php } ?>
			<tr valign="top">
				<th scope="row"><label for="w4os_db_host"><?php _e("Hostname");?></label></th>
				<td><input type="text" class=regular-text id="w4os_db_host" name="w4os_db_host" value="<?php echo get_option('w4os_db_host'); ?>" /></td>
			</tr>
			<tr valign="top">
			  <th scope="row"><label for="w4os_db_database"><?php _e("Database name");?></label></th>
			  <td><input type="text" class=regular-text id="w4os_db_database" name="w4os_db_database" value="<?php echo get_option('w4os_db_database'); ?>" /></td>
			</tr>
			<tr valign="top">
			  <th scope="row"><label for="w4os_db_user"><?php _e("Username");?></label></th>
			  <td><input type="text" class=regular-text id="w4os_db_user" name="w4os_db_user" value="<?php echo get_option('w4os_db_user'); ?>" /></td>
			</tr>
			<tr valign="top">
			  <th scope="row"><label for="w4os_db_pass"><?php _e("Password");?></label></th>
			  <td><input type="password" class=regular-text id="w4os_db_pass" name="w4os_db_pass" value="<?php echo get_option('w4os_db_pass'); ?>" /></td>
			</tr>
		</table>
		<?php  submit_button(); ?>
	</form>
</div>
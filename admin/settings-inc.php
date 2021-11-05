<div class="wrap">
	<!-- <h1><?php esc_html( get_admin_page_title() ); ?></h1> -->
	<h1>OpenSimulator</h1>	<?php screen_icon(); ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'w4os_options_group' ); ?>
		<table class=form-table>
			<tr valign="top">
				<th scope="row"><label for="w4os_grid_name"><?php _e("Grid name", "w4os");?></label></th>
				<td><input type="text" class=regular-text id="w4os_grid_name" name="w4os_grid_name" value="<?php echo esc_attr(get_option('w4os_grid_name')); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="w4os_login_uri"><?php _e("Login URI", "w4os");?></label></th>
				<td><input type="text" class=regular-text id="w4os_login_uri" name="w4os_login_uri" value="<?php echo esc_attr(get_option('w4os_login_uri')); ?>" placeholder='example.org:8002'/></td>
			</tr>
			<tr><th colspan=2>
				<h2><?php _e("Robust server database", "w4os");?></h2>
			</th></tr>
			<?php if(! W4OS_DB_CONNECTED) { ?>
				<tr><td colspan=2>
					<strong><?php _e("Required tables are missing, check your connection settings or your database", 'w4os');?></strong>
				</td></tr>
			<?php } ?>
			<tr valign="top">
				<th scope="row"><label for="w4os_db_host"><?php _e("Hostname");?></label></th>
				<td><input type="text" class=regular-text id="w4os_db_host" name="w4os_db_host" value="<?php echo esc_attr(get_option('w4os_db_host')); ?>" /></td>
			</tr>
			<tr valign="top">
			  <th scope="row"><label for="w4os_db_database"><?php _e("Database name", 'w4os');?></label></th>
			  <td><input type="text" class=regular-text id="w4os_db_database" name="w4os_db_database" value="<?php echo esc_attr(get_option('w4os_db_database')); ?>" /></td>
			</tr>
			<tr valign="top">
			  <th scope="row"><label for="w4os_db_user"><?php _e("Username");?></label></th>
			  <td><input type="text" class=regular-text id="w4os_db_user" name="w4os_db_user" value="<?php echo esc_attr(get_option('w4os_db_user')); ?>" /></td>
			</tr>
			<tr valign="top">
			  <th scope="row"><label for="w4os_db_pass"><?php _e("Password");?></label></th>
			  <td><input type="password" class=regular-text id="w4os_db_pass" name="w4os_db_pass" value="<?php echo esc_attr(get_option('w4os_db_pass')); ?>" /></td>
			</tr>

			<tr><th colspan=2>
				<h2><?php _e("Avatar creation", 'w4os');?></h2>
			</th></tr>

			<tr valign="top">
				<th scope="row"><?php _e("Models", 'w4os');?></th>
				<td>
					<p class=description>
						<?php _e('Grid accounts matching first name or last name set below will appear as avatar models, with their profile picture if set, on the avatar registration form.', 'w4os') ?>
					<br/>
						<?php _e('If both left empty, no model will be displayed and avatars will be created with default OpenSimulator appearance (most probably Ugly Ruth).', 'w4os') ?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<td scope="row" align=right><label for="w4os_model_firstname"><?php _e("First name =", "w4os");?></label></td>
				<td><input type="text" class=regular-text id="w4os_model_firstname" name="w4os_model_firstname" value="<?php echo esc_attr(get_option('w4os_model_firstname')); ?>" />
			</td>
			</tr>
			<tr valign="top">
				<td scope="row" align=right><label for="w4os_model_lastname"><?php _e("OR Last name =", "w4os");?></label></td>
				<td><input type="text" class=regular-text id="w4os_model_lastname" name="w4os_model_lastname" value="<?php echo esc_attr(get_option('w4os_model_lastname')); ?>" /></td>
			</tr>
			<tr><th colspan=2>
				<h2><?php _e("Misc", 'w4os');?></h2>
			</th></tr>

			<tr valign="top">
				<th scope="row"><label><?php _e("Web asset server", "w4os");?></label></th>
				<td>
					<label for="w4os_provide_asset_server">
					<input type="checkbox" class=regular-text id="w4os_provide_asset_server" name="w4os_provide_asset_server" value="1" <?php if (get_option('w4os_provide_asset_server')==1) echo "checked"; ?> onchange="valueChanged(this)" />
						<?php _e('Provide web assets service', 'w4os') ?>
					</label>
				</td>
			</tr>
			<tr valign="top">
				<td scope="row" align=right><label for="w4os_asset_server_uri"><?php _e("Web assets server uri", "w4os");?></label></td>
				<td>
					<input type="text" class=regular-text id="w4os_internal_asset_server_uri" name="w4os_internal_asset_server_uri" value="<?php echo W4OS_WEB_ASSETS_SERVER_URI; ?>" readonly />
					<input type="text" class=regular-text id="w4os_asset_server_uri" name="w4os_asset_server_uri" value="<?php echo esc_attr(get_option('w4os_asset_server_uri')); ?>" placeholder='https://example.com/assets/asset.php?id='/>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Permalinks", "w4os");?></label></th>
				<td>
					<p class=description>
						<?php echo sprintf(__('Set w4os slugs on %spermalink options page%s.', 'w4os'), '<a href=' . get_admin_url('', 'options-permalink.php').'>', '</a>'); ?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e("Exclude from stats", "w4os");?></label></th>
				<td>
					<input type="checkbox" class=regular-text id="w4os_exclude_models" name="w4os_exclude_models" value="1" <?php if (get_option('w4os_exclude_models')==1) echo "checked"; ?>/><label for="w4os_exclude_models"><?php _e('Models', 'w4os') ?></label>
					<br><input type="checkbox" class=regular-text id="w4os_exclude_nomail" name="w4os_exclude_nomail" value="1" <?php if (get_option('w4os_exclude_nomail')==1) echo "checked"; ?>/><label for="w4os_exclude_nomail"><?php _e('Accounts without mail address', 'w4os') ?></label>
					<p class=description>
						<?php _e('Accounts without email address are usually test accounts created from the console. Uncheck only if you have real avatars without email address.', 'w4os') ?>
					</p>
				</td>
			</tr>
		</table>
		<?php  submit_button(); ?>
	</form>
</div>
<script type="text/javascript">
function valueChanged(w4os_provide_asset_server) {
	document.getElementById("w4os_internal_asset_server_uri").style.display = w4os_provide_asset_server.checked ? "block" : "none";
	document.getElementById("w4os_asset_server_uri").style.display = w4os_provide_asset_server.checked ? "none" : "table-row";
}
valueChanged(w4os_provide_asset_server);
</script>

<?php if ( ! defined( 'W4OS_ADMIN' ) ) die;

$count = w4os_count_users();

// Note for future me, count broken assets
// SELECT inventoryname, inventoryID, assetID, a.id FROM inventoryitems LEFT JOIN assets AS a ON id = assetID WHERE a.id IS NULL;

?><div class="w4os-status-page wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
	<p><?php echo W4OS_PLUGIN_NAME . " " . W4OS_VERSION ?></p>

	<div class=content>
		<div class=sync>
			<h2><?php _e("Users", 'w4os') ?></h2>
			<table class="w4os-table user-sync">
				<thead>
					<tr>
						<th></th>
						<th><?php _e('Total', 'w4os');?></th>
						<th><?php if($count['wp_only'] > 0) _e('WP only', 'w4os');?></th>
						<th><?php _e('Sync', 'w4os');?></th>
						<th><?php if($count['grid_only'] > 0) _e('Grid only', 'w4os');?></th>
					</tr>
				</thead>
				<tr>
					<th><?php _e("Grid accounts", 'w4os') ?></th>
					<td><?php echo $count['grid_accounts']; ?></td>
					<td></td>
					<td class=success rowspan=2><?php echo $count['sync']; ?></td>
					<td class=error><?php echo $count['grid_only']; ?></td>
				</tr>
				<tr>
					<th><?php _e("Linked WordPress accounts", 'w4os') ?></th>
					<td><?php echo $count['wp_linked']; ?></td>
					<td class=error><?php echo $count['wp_only']; ?></td>
				</tr>
				<tr>
					<th><?php _e("Avatar models", 'w4os') ?></th>
					<td><?php
					echo $count['models'];
					?></td>
				</tr>
				<?php if($count['tech'] > 0) { ?>
					<tr>
						<th><?php _e("Other service accounts", 'w4os') ?></th>
						<td><?php
						echo $count['tech'];
						?></td>
					</tr>
				<?php } ?>
			</table>
			<?php	if($count['wp_only'] + $count['grid_only'] > 0 |! empty($sync_result)) { ?>
				<table class="w4os-table .notes">
					<tr class=notes>
						<th></th>
						<td>
							<?php
							if($count['grid_only']  > 0 ) {
								echo '<p>' . sprintf(_n(
									'%d grid account has no linked WP account. Syncing will create a new WP account.',
									'%d grid accounts have no linked WP account. Syncing will create new WP accounts.',
									$count['grid_only'],
									'w4os'
								), $count['grid_only']) . '</p>';
							}
							if($count['wp_only']  > 0 ) {
								echo '<p>' . sprintf(_n(
									'%d WordPress account is linked to an unexisting avatar (wrong UUID). Syncing accounts will keep this WP account but remove the broken reference.',
									'%d WordPress accounts are linked to unexisting avatars (wrong UUID). Syncing accounts will keep these WP accounts but remove the broken reference.',
									$count['wp_only'],
									'w4os'
								), $count['wp_only']) . '</p>';
							}
							if($count['tech'] > 0) {
								echo '<p>' . sprintf(_n(
									"%d grid account (other than models) has no email address, which is fine as long as it is used only for maintenance or service tasks.",
									"%s grid accounts (other than models) have no email address, which is fine as long as they are used only for maintenance or service tasks.",
									$count['tech'],
									'w4os'
									) . ' ' . __('Real accounts need a unique email address for W4OS to function properly.', 'w4os'
								), $count['tech']) . '</p>';
							}
							if($count['grid_only'] + $count['wp_only'] > 0) {
								echo '<form method="post" action="options.php" autocomplete="off">';
								settings_fields( 'w4os_status' );
								echo '<input type="hidden" input-hidden" id="w4os_sync_users" name="w4os_sync_users" value="1">';

								submit_button(__('Synchronize users now', 'w4os'));
								echo '</form>';
								echo '<p class=description>' . __('Synchronization is made at plugin activation and is handled automatically afterwards, but in certain circumstances it may be necessary to initiate it manually to get an immediate result, especially if users have been added or deleted directly from the grid administration console.', 'w4os') . '<p>';
							}
							if($sync_result)
							echo '<p class=info>' . $sync_result . '<p>';
							?>
						</td>
					</tr>
				</table>
			<?php	} ?>
		</div>
		<div class=shortcodes>
			<h2>
				<?php _e("Available shortcodes", 'w4os') ?>
			</h2>
			<table class="w4os-table shortcodes">
				<tr><th>
					<code>[gridinfo]</code>
					<p><?php _e("General information (grid name and login uri)", 'w4os') ?></p>
				</th><td>
					<?php echo w4os_gridinfo_shortcode(); ?>
				</td></tr>
				<tr><th>
					<code>[gridstatus]</code>
					<p><?php _e("Online users, regions, etc.", 'w4os') ?></p>
				</th><td>
					<?php echo w4os_gridstatus_shortcode(); ?>
				</td></tr>
				<tr><th>
					<code>[gridprofile]</code>
					<p><?php _e("Grid profile if user is connected and has an avatar, avatar registration form otherwise", 'w4os') ?>
						<?php echo sprintf(__("(formerly %s)", 'w4os'), "<code>[w4os_profile]</code>"); ?>
					</p>
				</th><td>
					<?php echo do_shortcode('[gridprofile]'); ?>
				</td></tr>
			</table>
		</div>

	<?php
	  if ( ! function_exists('curl_init') ) $php_missing_modules[]='curl';
	  if ( ! function_exists('simplexml_load_string') ) $php_missing_modules[]='xml';
		if(!empty($php_missing_modules)) {
			echo sprintf(
				'<div class="missing-modules warning"><h2>%s</h2>%s</div>',
				__('Missing PHP modules', 'w4os'),
				sprintf(
					__('These modules were not found: %s. Install them to get the most of this plugin.', 'w4os'),
					'<strong>' . join('</strong>, <strong>', $php_missing_modules) . '</strong>',
				),
			);
		}
	?>
	</div>
	<div class="pages">
		<h2>
			<?php _e("System pages", 'w4os') ?>
		</h2>
		<p><?php _e("The following page are needed by OpenSimulator and/or by W4OS plugin. Make sure they exist or adjust your simulator .ini file.", 'w4os'); ?></p>
		<?php
		$grid_info = W4OS_GRID_INFO;
		$grid_info['profile'] = W4OS_LOGIN_PAGE;

		if(get_option('w4os_profile_page')=='provide')
		$required['profile'] = __('Profile page. This is the page used to dynamically generate avatar profile pages.', 'w4os');
		if(get_option('w4os_login_page')=='profile')
		$required['profile'] .= ' ' . __('This page is set as default login page.', 'w4os');

		if(!empty($required)) {
			echo "<h4>" .__("Required by W4OS", 'w4os') . '</h4>';
			echo '<p>' . __('These page are required for W4OS to function normally. You can adjust your setttings in this ', 'w4os');

			foreach($required as $key => $description) {
				if (empty($grid_info[$key]) ) continue;
				echo sprintf('<dt><strong><a href="%1$s" target=_blank>%1$s</a></strong></dt><dd class=description>%2$s</dd>',
				$grid_info[$key], $description );
			}
		}

		echo "<h4>" .__("From your OpenSimulator .ini file", 'w4os') . '</h4>';
		echo "<p>" .__("These page are set by the simulator config. You can disable them or change their URL in your simulator .ini file.", 'w4os') . '</h4>';

		$required = array(
			'welcome' => __("Viewer splash page. This is the page displayed on the viewer before the user logs in. It's a short, one screen page displaying only relevant info (grid status, important update, message of the day). It is required, or at least highly recommended.", 'w4os'),
			'about' => __('Detailed info page, via a link displayed on the viewer login page. Optional.', 'w4os'),
			'help' => __('Link to a help page. Optional.', 'w4os'),
			'password' => __('Link to lost password page. Optional.', 'w4os'),
			'register' => __('Link to the user registration. Optional.', 'w4os'),
			'economy' => __('This is the base URL for several standard simulator services expected by the viewer, like search, currencies... They are not accessed directly by the user. It requires the installation of separate software and can be hosted on the same or a different web server. Optional.', 'w4os'),
		);

		foreach($required as $key => $description) {
			if (empty($grid_info[$key]) ) continue;
			echo sprintf('<dt><strong><a href="%1$s" target=_blank>%1$s</a></strong></dt><dd class=description>%2$s</dd>',
			$grid_info[$key], $description );
		}
		?>
	</div>
</div>
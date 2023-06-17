<?php
/**
 * Plugin Name:       w4os - OpenSimulator Web Interface (dev)
 * Description:       WordPress interface for OpenSimulator (w4os).
 * Version:           3.0.1-dev.1001
 * Author:            Speculoos World
 * Author URI:        https://speculoos.world
 * Plugin URI:        https://w4os.org/
 * License:           AGPLv3
 * License URI:       https://www.gnu.org/licenses/agpl-3.0.txt
 * Text Domain:       w4os
 * Domain Path:       /languages/
 *
 * @package w4os
 *
 * Icon1x: https://github.com/GuduleLapointe/w4os/raw/master/assets/icon-128x128.png
 * Icon2x: https://github.com/GuduleLapointe/w4os/raw/master/assets/icon-256x256.png
 * BannerHigh: https://github.com/GuduleLapointe/w4os/raw/master/assets/banner-1544x500.jpg
 * BannerLow: https://github.com/GuduleLapointe/w4os/raw/master/assets/banner-772x250.jpg
 *
 * Contributing: If you improve this software, please give back to the
 * community, by submitting your changes on the git repository or sending them
 * to the authors. That's one of the meanings of Affero GPL!
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if(!defined('W4OS_VERSION')) {
	define( 'W4OS_VERSION', '3.0.1-dev.1001' . time() );

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-activator.php
	 */
	function activate_w4os() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
		W4OS_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-deactivator.php
	 */
	function deactivate_w4os() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
		W4OS_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_w4os' );
	register_deactivation_hook( __FILE__, 'deactivate_w4os' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_w4os() {

		$plugin = new W4OS();
		$plugin->run();

	}
	run_w4os();

	// error_reporting(E_ERROR | E_WARNING | E_PARSE);

	/**
	 * Load legacy structure untill it's rewritten
	 */
	require_once plugin_dir_path( __FILE__ ) . 'legacy/init.php';
	if(file_exists(plugin_dir_path( __FILE__ ) . 'lib/package-updater.php'))
	include_once plugin_dir_path( __FILE__ ) . 'lib/package-updater.php';

	if(is_admin()) {
		require_once (plugin_dir_path(__FILE__) . 'legacy/admin/admin-init.php');
	}

} else {
	/**
	 * Another version of the plugin is active and already loaded, so we just
	 * behave and deactivate ourself
	 */
	add_action( 'admin_notices', function() {
		printf (
			"<div class='notice notice-error'><p><strong>W4OS:</strong> %s</p></div>",
			sprintf(
				__("%s %s is installed and active. Deactivate it before activating another version.", 'w4os'),
				'<strong>' . ((defined('W4OS_PLUGIN_NAME') ? W4OS_PLUGIN_NAME : __('w4os - OpenSimulator Web Interface', 'w4os') )) . '<strong>',
				W4OS_VERSION,
			),
		);
	} );
	deactivate_plugins(plugin_basename( __FILE__ ));
}

/**
 * Just a last check to give stable release priority if it is about to load
 */
$w4os_release = "w4os-opensimulator-web-interface/w4os.php";
$w4os_current = plugin_basename( __FILE__ );
if ( $w4os_current != $w4os_release ) {
	$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
	if (in_array($w4os_release, apply_filters( 'active_plugins', get_option( 'active_plugins' )))) {
		add_action( 'admin_notices', function() use ($w4os_release, $w4os_current) {
			printf (
				"<div class='notice notice-error'><p><strong>W4OS:</strong> %s</p></div>",
				sprintf(
					__("A stable release of %s is active. You should use %s and uninstall %s.", 'w4os'),
					'<strong>' . ((defined('W4OS_PLUGIN_NAME') ? W4OS_PLUGIN_NAME : __('w4os - OpenSimulator Web Interface', 'w4os') )) . '</strong>',
					(empty($w4os_release)) ? 'it' : '<code>' . $w4os_release . '</code>',
					(empty($w4os_current)) ? 'any other versions' : '<code>' . $w4os_current . '</code>',
				),
			);
		} );
		// deactivate_plugins($w4os_current);
	}
}

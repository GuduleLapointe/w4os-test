<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    W4OS
 * @subpackage W4OS/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    W4OS
 * @subpackage W4OS/includes
 * @author     Your Name <email@example.com>
 */
class W4OS3_Settings {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		$actions = array(
			array (
				'hook' => 'admin_menu',
				'callback' => 'legacy_admin_menu',
				// 'priority' => 5,
			),
			array (
				'hook' => 'admin_menu',
				'callback' => 'legacy_admin_submenus',
				'priority' => 15,
			),
		);
		$filters = array(
			array (
				'hook' => 'mb_settings_pages',
				'callback' => 'register_settings_pages',
			),
			array (
				'hook' => 'rwmb_meta_boxes',
				'callback' => 'register_settings_fields'
			),
		);

		foreach ( $filters as $hook ) {
			(empty($hook['component'])) && $hook['component'] = __CLASS__;
			(empty($hook['priority'])) && $hook['priority'] = 10;
			(empty($hook['accepted_args'])) && $hook['accepted_args'] = 1;
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $actions as $hook ) {
			(empty($hook['component'])) && $hook['component'] = __CLASS__;
			(empty($hook['priority'])) && $hook['priority'] = 10;
			(empty($hook['accepted_args'])) && $hook['accepted_args'] = 1;
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

	}

	static function legacy_admin_menu() {
		// add_options_page('OpenSimulator settings', 'w4os', 'manage_options', 'w4os', 'w4os_settings_page');
		add_menu_page(
			'OpenSimulator', // page title
			'OpenSimulator', // menu title
			'manage_options', // capability
			'w4os', // slug
			'', // callable function
			// plugin_dir_path(__FILE__) . 'options.php', // slug
			// null,	// callable function
			plugin_dir_url(__DIR__) . 'images/opensimulator-logo-24x14.png', // icon url,
			2 // position
		);

	}

	static function legacy_admin_submenus() {
		add_submenu_page(
			'w4os', // parent
			__('OpenSimulator Settings', "w4os"), // page title
			__('Legacy Settings', 'w4os'), // menu title
			'manage_options', // capability
			'w4os_settings_legacy', // menu slug
			'w4os_settings_page' // function
		);

		if(function_exists('xmlrpc_encode_request')) {
			add_submenu_page(
				'w4os', // parent
				__('OpenSimulator Helpers', "w4os"), // page title
				__('Legacy Helpers', 'w4os'), // menu title
				'manage_options', // capability
				'w4os_helpers', // menu slug
				'w4os_helpers_page' // function
			);
		}
		add_submenu_page('w4os', __('OpenSimulator Status', "w4os"), __('Status'), 'manage_options', 'w4os', 'w4os_status_page', 20);
	}

	static function register_settings_pages( $settings_pages ) {
		// $settings_pages[] = [
		// 	'menu_title'    => __( 'OpenSimulator', 'w4os' ),
		// 	'id'            => 'w4os',
		// 	'position'      => 2,
		// 	// 'submenu_title' => 'Settings',
		// 	'capability'    => 'manage_options',
		// 	'style'         => 'no-boxes',
		// 	'columns'       => 1,
		// 	'icon_url'      => plugin_dir_url(__DIR__) . 'images/opensimulator-logo-24x14.png', // icon url,
		// ];

		$settings_pages[] = [
			'menu_title' => __( 'Settings', 'w4os' ),
			'id'         => 'w4os_settings',
			// 'position'   => 2,
			'parent'     => 'w4os',
			'capability' => 'manage_options',
			'style'      => 'no-boxes',
			// 'icon_url'   => 'dashicons-admin-generic',
		];

		return $settings_pages;
	}

	static function register_settings_fields( $meta_boxes ) {
		$prefix = '';

		$meta_boxes[] = [
			'title'          => __( 'Settings', 'w4os' ),
			'id'             => 'w4os_settings_fields',
			'settings_pages' => ['w4os_settings'],
			'fields'         => [
				[
					'name'              => __( 'Create WP accounts', 'w4os' ),
					'id'                => $prefix . 'create_wp_account',
					'type'              => 'switch',
					'label_description' => __( '(work in progress, not implemented)', 'w4os' ),
					'desc'              => __( 'Create a WordPress account for new avatars. If an account already exists with the same name or email address, force user to login first.', 'w4os' ),
					'style'             => 'rounded',
				],
				[
					'name'              => __( 'Restrict Multiple Avatars', 'w4os' ),
					'id'                => $prefix . 'multiple_avatars',
					'type'              => 'switch',
					'label_description' => __( '(work in progress, not implemented)', 'w4os' ),
					'desc'              => __( 'Multple avatars sharing a single email address and/or WordPress account. Restriction only apply to end users. Multiple avatars are always possible from admin or from OpenSimulator console. (not implemented)', 'w4os' ),
					'style'             => 'rounded',
					'std'               => true,
				],
				[
					'name'              => __( 'Robust INI file', 'w4os' ),
					'id'                => $prefix . 'robust_ini',
					'type'              => 'text',
				],
				[
						'name'     => __( 'Debug', 'w4os' ),
						'id'       => $prefix . 'debug_html',
						'type'     => 'custom_html',
						'callback' => 'W4OS3_Settings::debug_callback',
				],
			],
		];

		return $meta_boxes;
	}

	public static function get_constant_value($config, $value) {
		if(preg_match('/\${.*}/', $value)) {
			$array = preg_split('/[\$}]/', $value);
			foreach ($array as $index => $string) {
				if(preg_match('/{.*\|/', $string)) {
					$section = preg_replace( '/{(.*)\|.*/', '$1', $string );
					$param = preg_replace( '/^{.*\|(.*)/', '$1', $string );
					if(isset($config[$section]) && is_array($config[$section])) {
						$array[$index] = $config[$section][$param];
					} else {
						error_log("Could not parse \$$string}");
						$array[$index] = "\$$string}";
						break;
					}
				}
			}
			$value = join('', $array);
		}

		return $value;
	}

	public static function parse_values($config, $values) {
		foreach($values as $key => $value) {
			switch (gettype($value)) {
				case 'array':
				$values[$key] = self::parse_values($config, $value);
				break;

				case 'string':
				$values[$key] = self::get_constant_value($config, $value);
				break;
			}
		}
		return $values;
	}

	public static function parse_config_file($config_file, $config = []) {
		$cleanup = self::cleanup_ini($config_file);
		if(empty($cleanup)) {
			error_log("$config_file is empty or unreadable");
			return $config;
		}

		$tempfile = wp_tempnam('w4os-config-clean');
		file_put_contents($tempfile, $cleanup);
		$parse = parse_ini_file($tempfile, true);
		$config = array_merge($config, $parse);
		unlink($tempfile);

		foreach($parse as $section => $options) {
			foreach($options as $option => $value) {
				if(preg_match('/^Include-/', $option) ) {
					$include = self::get_constant_value($config, $value);
					$config = array_merge($config, self::parse_config_file($include, $config));
				}
			}
		}

		return $config;
	}

	public static function cleanup_ini($ini_file) {
		$cleanup = file($ini_file);
		if(! $cleanup) return [];
		if(!is_array($cleanup)) error_log("$cleanup $ini_file is not an array");
		$cleanup = preg_replace('/^[[:blank:]]*([^=]*)[[:blank:]]*=[[:blank:]]*([^"]*\${.*)$/', '$1 = "$2"', $cleanup);
		$cleanup = preg_replace("/\n/", '', $cleanup);
		// $cleanup = preg_replace('/^/', 'begin', $cleanup);
		$cleanup = preg_replace('/\$/', '\\\$', $cleanup);
		// return '<pre>' . print_r($cleanup, true) . '</pre>';
		return join("\n", $cleanup);
	}

	public static function debug_callback() {
		$html = '';
		$config_file = W4OS::get_option('robust_ini', false);
		// return '<pre>' . print_r(self::cleanup_ini($config_file), true) . '</pre>';

		if($config_file) {
			$config = self::parse_config_file($config_file);
			$values = self::parse_values($config, $config);
			$html .= '<pre>' . print_r($values, true) . '</pre>';
		}

		return $html;
	}
}

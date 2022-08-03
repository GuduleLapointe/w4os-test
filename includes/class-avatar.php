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
class W4OS3_Avatar {

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

	protected $post;
	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct($post = NULL) {
		if(is_numeric($post)) {
			$post_id = $post;
			$post = get_post($post_id);
		}
		if(!empty($post) &! is_wp_error($post)) {
			$this->post = $post;
		}
	}

	function update($data = []) {
	}

	function create() {
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		$actions = array(
			array (
				'hook' => 'init',
				'callback' => 'register_post_types',
			),
			array (
				'hook' => 'wp_ajax_check_name_availability',
				'callback' => 'ajax_check_name_availability',
			),
		);

		$filters = array(
			array (
				'hook' => 'rwmb_meta_boxes',
				'callback' => 'add_fields',
			),
			array (
				'hook' => 'wp_insert_post_data',
				'callback' => 'insert_post_data',
				'accepted_args' => 4,
			),
			// array (
			// 	'hook' => 'post_row_actions',
			// 	'add_row_action_links',
			// 	'accepted_args' => 2,
			// ),
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

	// function ajax_check_name_availability() {
	// 	error_reporting(E_ALL);
	// 	error_log(print_r($_REQUEST, true));
	// 	// Get the field value via the global variable $_GET
	// 	if ( $_GET['avatar_name_firstname'] === 'Wrong' ) {
	// 		echo 'false'; // Invalid
	// 	} else {
	// 		echo 'true'; // Valid
	// 	}
	// 	die;
	// }

	static function register_post_types() {
	  $labels = [
	    'name'                     => esc_html__( 'Avatars', 'w4os' ),
	    'singular_name'            => esc_html__( 'Avatar', 'w4os' ),
	    'add_new'                  => esc_html__( 'Add New', 'w4os' ),
	    'add_new_item'             => esc_html__( 'Add new avatar', 'w4os' ),
	    'edit_item'                => esc_html__( 'Edit Avatar', 'w4os' ),
	    'new_item'                 => esc_html__( 'New Avatar', 'w4os' ),
	    'view_item'                => esc_html__( 'View Avatar', 'w4os' ),
	    'view_items'               => esc_html__( 'View Avatars', 'w4os' ),
	    'search_items'             => esc_html__( 'Search Avatars', 'w4os' ),
	    'not_found'                => esc_html__( 'No avatars found', 'w4os' ),
	    'not_found_in_trash'       => esc_html__( 'No avatars found in Trash', 'w4os' ),
	    'parent_item_colon'        => esc_html__( 'Parent Avatar:', 'w4os' ),
	    'all_items'                => esc_html__( 'All Avatars', 'w4os' ),
	    'archives'                 => esc_html__( 'Avatar Archives', 'w4os' ),
	    'attributes'               => esc_html__( 'Avatar Attributes', 'w4os' ),
	    'insert_into_item'         => esc_html__( 'Insert into avatar', 'w4os' ),
	    'uploaded_to_this_item'    => esc_html__( 'Uploaded to this avatar', 'w4os' ),
	    'featured_image'           => esc_html__( 'Featured image', 'w4os' ),
	    'set_featured_image'       => esc_html__( 'Set featured image', 'w4os' ),
	    'remove_featured_image'    => esc_html__( 'Remove featured image', 'w4os' ),
	    'use_featured_image'       => esc_html__( 'Use as featured image', 'w4os' ),
	    'menu_name'                => esc_html__( 'Avatars', 'w4os' ),
	    'filter_items_list'        => esc_html__( 'Filter avatars list', 'w4os' ),
	    'filter_by_date'           => esc_html__( 'Filter by date', 'w4os' ),
	    'items_list_navigation'    => esc_html__( 'Avatars list navigation', 'w4os' ),
	    'items_list'               => esc_html__( 'Avatars list', 'w4os' ),
	    'item_published'           => esc_html__( 'Avatar published', 'w4os' ),
	    'item_published_privately' => esc_html__( 'Avatar published privately', 'w4os' ),
	    'item_reverted_to_draft'   => esc_html__( 'Avatar reverted to draft', 'w4os' ),
	    'item_scheduled'           => esc_html__( 'Avatar scheduled', 'w4os' ),
	    'item_updated'             => esc_html__( 'Avatar updated', 'w4os' ),
	    'text_domain'              => 'w4os',
	  ];
	  $args = [
	    'label'               => esc_html__( 'Avatars', 'w4os' ),
	    'labels'              => $labels,
	    'description'         => '',
	    'public'              => true,
	    'hierarchical'        => false,
	    'exclude_from_search' => false,
	    'publicly_queryable'  => true,
	    'show_ui'             => true,
	    'show_in_nav_menus'   => true,
	    'show_in_admin_bar'   => true,
	    'show_in_rest'        => true,
	    'query_var'           => true,
	    'can_export'          => true,
	    'delete_with_user'    => true,
	    'has_archive'         => true,
	    'rest_base'           => '',
	    'show_in_menu'        => 'w4os',
	    'menu_icon'           => 'dashicons-universal-access',
	    'capability_type'     => 'post',
	    'supports'            => false,
	    'taxonomies'          => [],
	    'rewrite'             => [
	      'with_front' => false,
	    ],
	  ];

	  register_post_type( 'avatar', $args );
	}

	static function add_fields( $meta_boxes ) {
	  $prefix = 'avatar_';
	  $user = wp_get_current_user();
	  if($user) {
			$default_name = self::sanitize_name(
				(empty($user->display_name))
				? "$user->first_name $user->last_name"
				: $user->display_name
			);
	  }

	  $meta_boxes['avatar'] = [
	    'title'      => __( 'Profile', 'w4os' ),
	    'id'         => 'avatar-profile-fields',
	    'post_types' => ['avatar'],
	    'context'    => 'after_title',
	    'style'      => 'seamless',
			'fields'     => [
				'name' => [
						// 'name'     => __( 'Avatar Name', 'w4os' ),
						'id'       => $prefix . 'name',
						'type'     => 'custom_html',
						'std' => '<h1>' . self::current_avatar_name() . '</h1>',
						// 'callback' => __CLASS__ . '::current_avatar_name',
				],
				[
					'name'       => __( 'WordPress User', 'w4os' ),
					'id'         => $prefix . 'owner',
					'type'       => 'user',
					'field_type' => 'select_advanced',
					'columns'     => 4,
					'std' => wp_get_current_user()->ID,
					'placeholder' => __('Select a user', 'w4os'),
					'admin_columns' => [
						'position'   => 'after title',
						'sort'       => true,
						'searchable' => true,
					],
				],
	      'email' => [
	        'name'          => __( 'E-mail', 'w4os' ),
	        'id'            => $prefix . 'email',
	        'type'          => 'email',
	        'std'           => wp_get_current_user()->user_email,
					'admin_columns' => [
						'position'   => 'after avatar_owner',
						'sort'       => true,
						'searchable' => true,
					],
	        'columns'       => 4,
	        // 'readonly' => (!W4OS::is_new_post()),
	        'desc' => __('Optional. If set, the avatar will be linked to any matching WP user account.', 'w4os'),
	        'hidden'        => [
	            'when'     => [['avatar_owner', '!=', '']],
	            'relation' => 'or',
	        ],
	      ],
	      [
	          'name'    => __( 'Create WP user', 'w4os' ),
	          'id'      => $prefix . 'create_wp_user',
	          'type'    => 'switch',
	          'style'   => 'rounded',
	          'columns' => 2,
	          'visible' => [
	              'when'     => [['avatar_email', '!=', ''], ['avatar_owner', '=', '']],
	              'relation' => 'and',
	          ],
	      ],
	      [
	          'name'    => (W4OS::is_new_post()) ? __( 'Password', 'w4os' ) : __('Change password', 'w4os'),
	          'id'      => $prefix . 'password',
	          'type'    => 'password',
	          'columns' => 4,
	      ],
	      [
	          'name'    => __( 'Confirm password', 'w4os' ),
	          'id'      => $prefix . 'password_2',
	          'type'    => 'password',
	          'columns' => 4,
	      ],
	      [
	          'name'    => __( 'Same password as WP user', 'w4os' ),
	          'id'      => $prefix . 'use_wp_password',
	          'type'    => 'switch',
	          'style'   => 'rounded',
	          'std'     => true,
	          'columns' => 2,
	          'visible' => [
	              'when'     => [
	                ['avatar_owner', '!=', ''],
	                ['create_wp_user', '=', true],
	              ],
	              'relation' => 'or',
	          ],
	      ],
	    ],
	  ];
	  if(W4OS::is_new_post()) {
	    $meta_boxes['avatar']['fields'] = array_merge( $meta_boxes['avatar']['fields'], [
	      'model' => [
	        'name'    => __( 'Model', 'w4os' ),
	        'id'      => $prefix . 'model',
	        'type'    => 'image_select',
	        'options' => self::w4os_get_models_options(),
	      ],
	    ]);
			$meta_boxes['avatar']['fields']['name'] = [
				'name'   => __( 'Avatar Name', 'w4os' ),
				'id'     => $prefix . 'name',
				'type'        => 'text',
				// 'disabled' => (!W4OS::is_new_post()),
				'readonly' => (!W4OS::is_new_post()),
				'required'    => true,
				// Translators: Avatar name placeholder, only latin, unaccended characters, first letter uppercase, no spaces
				'placeholder' => __( 'Firstname', 'w4os' ) . ' ' . __('Lastname', 'w4os' ),
				'required'    => true,
				// 'columns'     => 6,
				'std' => $default_name,
				'desc' => (W4OS::is_new_post()) ? __('The avatar name is permanent, it can\'t be changed later.', 'w4os') : '',
			];
			$meta_boxes['avatar']['validation']['rules'][$prefix . 'name'] = [
					// 'maxlength' => 64,
					'pattern'  => W4OS_PATTERN_NAME, // Must have 9 digits
					'remote' => admin_url( 'admin-ajax.php?action=check_name_availability' ), // remote ajax validation
			];
			$meta_boxes['avatar']['validation']['messages'][$prefix . 'name'] = [
					'remote'  => 'This name is not available.',
					'pattern'  => __('Please provide first and last name, only letters and numbers, separated by a space.', 'w4os'),
			];

	  } else {
	    // $meta_boxes['avatar']['fields']['first_name']['disabled'] = true;
	    // $meta_boxes['avatar']['fields']['first_name']['readonly'] = true;
	    // $meta_boxes['avatar']['fields']['last_name']['disabled'] = true;
	    // $meta_boxes['avatar']['fields']['last_name']['readonly'] = true;
	    // $meta_boxes['avatar']['fields']['email']['disabled'] = true;
	    // $meta_boxes['avatar']['fields']['email']['readonly'] = true;
	    // $meta_boxes['avatar']['fields'] = array_merge( $meta_boxes['avatar']['fields'], [
	    //   [
	    //     'name'        => __( 'UUID', 'w4os' ),
	    //     'id'          => $prefix . 'uuid',
	    //     'type'        => 'text',
	    //     'placeholder' => __( 'Wil be set by the server', 'w4os' ),
	    //     'disabled'    => true,
	    //     'readonly'    => true,
	    //     'visible'     => [
	    //       'when'     => [['avatar_uuid', '!=', '']],
	    //       'relation' => 'or',
	    //     ],
	    //   ],
	    // ]);
	  }

	  return $meta_boxes;
	}

	static function insert_post_data( $data, $postarr, $unsanitized_postarr, $update ) {
	  if(!$update) return $data;
	  if('avatar' !== $data['post_type']) return $data;

		/**
		 * Rewrite post title
		 */
	  $avatar_name = trim(@$postarr['avatar_name']);
	  if(empty($avatar_name)) {
	    $avatar_name = trim(get_post_meta($postarr['ID'], 'avatar_name', true));
	  }
	  if(!empty($avatar_name)) $data['post_title'] = $avatar_name;

		/**
		 * If new, eheck if name is valid and create on simulator
		 */

		/**
		 * Update WP post with simulator info (UUID, About, Created, Partner, Watns, Skills, Languages, Real Life)
		 */

	  return $data;
	}

	/**
	 * example row action link for avatar post type
	 */
	// static function add_row_action_links($actions, $post) {
	//   if( 'avatar' == $post->post_type )
	//   $actions['google_link'] = sprintf(
	//     '<a href="%s" class="google_link" target="_blank">%s</a>',
	//     'http://google.com/search?q=' . $post->post_title,
	//     sprintf(__('Search %s on Google', 'w4os'), $post->post_title),
	//   );
	//
	//   return $actions;
	// }

	static function sanitize_name($value, $field = [], $old_value = NULL, $object_id = NULL) {
	  // return $value;
	  $return = sanitize_text_field($value);
	  // $return = strtr(utf8_decode($return), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	  $return = remove_accents($return);

	  $return = substr(preg_replace('/(' . W4OS_PATTERN_NAME . ')[^[:alnum:]]*/', '$1', $return), 0, 64);
	  if($value != $return &! empty($field['name'])) {
	    w4os_notice(sprintf(
	      __('%s contains invalid characters, replaced "%s" by "%s"', 'w4os'),
	      $field['name'],
	      wp_specialchars_decode(strip_tags(stripslashes($value))),
	      esc_attr($return),
	    ), 'warning');
	  }
	  return $return;
	}

	static function w4os_get_profile_picture() {
	  $options = array(
	    w4os_get_asset_url(),
	  );
	  return $options;
	}

	static function w4os_get_models_options() {
	  global $w4osdb;
	  $results = [];

	  $models=$w4osdb->get_results("SELECT FirstName, LastName, profileImage, profileAboutText
	    FROM UserAccounts LEFT JOIN userprofile ON PrincipalID = userUUID
	    WHERE active = true
	    AND (FirstName = '" . get_option('w4os_model_firstname') . "'
	    OR LastName = '" . get_option('w4os_model_lastname') . "')
	    ORDER BY FirstName, LastName"
	  );
		$results[] = w4os_get_asset_url(W4OS_NOTFOUND_PROFILEPIC);
	  if($models) {
	    foreach($models as $model) {
	      $model_name = $model->FirstName . " " . $model->LastName;
	      $model_imgid = (w4os_empty($model->profileImage)) ? W4OS_NOTFOUND_PROFILEPIC : $model->profileImage;
	      $model_img_url = w4os_get_asset_url($model_imgid);
	      $results[$model_name] = $model_img_url;
	    }
	  }
	  return $results;
	}

	static function check_name_availability($avatar_name) {
		if(!preg_match('/^' . W4OS_PATTERN_NAME . '$/', $avatar_name))
		return false;

		// Check if name restricted
		$parts = explode(' ', $avatar_name);
		foreach ($parts as $part) {
			if (in_array(strtolower($part), array_map('strtolower', W4OS_DEFAULT_RESTRICTED_NAMES)))
			return false;
		}

		// Check if there is another avatar with this name in WordPress
		$wp_avatar = self::get_wpavatar_by_name($avatar_name);
		if($wp_avatar) return false;

		// check if there avatar exist in simulator
		$uuid = self::get_uuid_by_name($avatar_name);
		if($uuid) return false; //

		return true;
	}

	static function get_wpavatar_by_name($avatar_name) {
		$post_id = false;
		error_log("searching posts for $avatar_name");
		$args = array(
			'post_type'		=>	'avatar',
			'order_by' => 'ID',
			'meta_query'	=>	array(
				array(
					'key' => 'avatar_name',
					'value'	=>	esc_sql($avatar_name),
				)
			)
		);
		$my_query = new WP_Query( $args );
		if( $my_query->have_posts() )
		$post_id = $my_query->post->ID;
		wp_reset_postdata();

		return $post_id;
	}

	static function get_uuid_by_name($avatar_name) {
		if(!W4OS_DB_CONNECTED) return false;
		if(empty($avatar_name)) return false;
		if(!preg_match('/^' . W4OS_PATTERN_NAME . '$/', $avatar_name)) return false;

		global $w4osdb;
		$parts = explode(' ', $avatar_name);
		$FirstName=$parts[0];
		$LastName=$parts[1];

		$check_uuid = $w4osdb->get_var(sprintf(
			"SELECT PrincipalID FROM UserAccounts
			WHERE (FirstName = '%s' AND LastName = '%s')
			",
			$FirstName,
			$LastName,
		));

		if($check_uuid) return $check_uuid;
		else return false;
	}

	static function ajax_check_name_availability() {
		$avatar_name = esc_attr($_GET['avatar_name']);

		if (self::check_name_availability($avatar_name)) echo 'true';
		else echo 'false';
		die;
	}

	static function current_avatar_name() {
		global $post;
		if(!empty($_REQUEST['post'])) {
			$post_id = esc_attr($_REQUEST['post']);
			$post = get_post($post_id);
		}
		if($post)	return $post->post_title;
	}

}

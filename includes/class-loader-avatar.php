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
class W4OS_Loader_Avatar extends W4OS_Loader {

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

		$this->actions = array(
			array (
				'hook' => 'init',
				'callback' => 'register_post_types',
			),
		);
		$this->filters = array(
			array (
				'hook' => 'rwmb_meta_boxes',
				'callback' => 'add_fields',
			),
			array (
				'hook' => 'wp_insert_post_data',
				'callback' => 'automatic_title',
				'accepted_args' => 4,
			),
			// array (
			// 	'hook' => 'post_row_actions',
			// 	'add_row_action_links',
			// 	'accepted_args' => 2,
			// ),
		);

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			(empty($hook['component'])) && $hook['component'] = __CLASS__;
			(empty($hook['priority'])) && $hook['priority'] = 10;
			(empty($hook['accepted_args'])) && $hook['accepted_args'] = 1;
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			(empty($hook['component'])) && $hook['component'] = __CLASS__;
			(empty($hook['priority'])) && $hook['priority'] = 10;
			(empty($hook['accepted_args'])) && $hook['accepted_args'] = 1;
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

	}

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
	    // if(!empty($user->display_name)) {
	    //   $default_first_name = W4OS_Loader_Avatar::sanitize_name(preg_replace('/ .*/', '', $user->display_name));
	    //   $default_last_name = W4OS_Loader_Avatar::sanitize_name(preg_replace('/[^ ]* /', '', $user->display_name));
	    // } else {
	    //   $default_first_name = W4OS_Loader_Avatar::sanitize_name($user->first_name);
	    //   $default_last_name = W4OS_Loader_Avatar::sanitize_name($user->last_name);
	    // }
			$default_first_name = W4OS_Loader_Avatar::sanitize_name(
				(empty($user->display_name)) ? $user->first_name : preg_replace('/ .*/', '', $user->display_name)
			);
			$default_last_name = W4OS_Loader_Avatar::sanitize_name(
				(empty($user->display_name)) ? $user->last_name : preg_replace('/[^ ]* /', '', $user->display_name)
			);

	  }
	  $meta_boxes['avatar'] = [
	    'title'      => __( 'Profile', 'w4os' ),
	    'id'         => 'avatar-profile-fields',
	    'post_types' => ['avatar'],
	    'context'    => 'after_title',
	    'style'      => 'seamless',
	    'validation' => [
	        'rules' => [
	            $prefix . 'first_name' => [
	                'maxlength' => 64,
	            ],
	        ],
	    ],
	    'validation' => [
	        'rules' => [
	            $prefix . 'last_name' => [
	                'maxlength' => 64,
	            ],
	        ],
	    ],
	    'fields'     => [
	      'first_name' => [
	        'name'     => __( 'First Name', 'w4os' ),
	        'id'       => $prefix . 'first_name',
	        'type'     => 'text',
	        'required' => true,
	        'readonly' => (!W4OS::is_new_post()),
	        'columns'  => 6,
	        'std' => $default_first_name,
	        'sanitize_callback' => __CLASS__ . '::sanitize_name',
	      ],
	      'last_name' => [
	        'name'     => __( 'Last Name', 'w4os' ),
	        'id'       => $prefix . 'last_name',
	        'type'     => 'text',
	        'required' => true,
	        'readonly' => (!W4OS::is_new_post()),
	        'columns'  => 6,
	        'std' => $default_last_name,
	        'sanitize_callback' => __CLASS__ . '::sanitize_name',
	      ],
				[
					'name'       => __( 'Owner', 'w4os' ),
					'id'         => $prefix . 'owner',
					'type'       => 'user',
					'field_type' => 'select_advanced',
					'columns'    => 4,
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
	        'std'           => W4OS::current_user_email(),
					'admin_columns' => [
						'position'   => 'after avatar_owner',
						'sort'       => true,
						'searchable' => true,
					],
	        'columns'       => 4,
	        'readonly' => (!W4OS::is_new_post()),
	        'desc' => __('Optional. If set, the avatar will be linked to any matching WP user account.'),
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
	          'name'    => (W4OS::is_new_post()) ? __( 'Password', 'w4os' ) : __('Change password'),
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
	        'options' => W4OS_Loader_Avatar::w4os_get_models_options(),
	      ],
	    ]);
	  } else {
	    // $meta_boxes['avatar']['fields']['first_name']['disabled'] = true;
	    // $meta_boxes['avatar']['fields']['first_name']['readonly'] = true;
	    // $meta_boxes['avatar']['fields']['last_name']['disabled'] = true;
	    // $meta_boxes['avatar']['fields']['last_name']['readonly'] = true;
	    // $meta_boxes['avatar']['fields']['email']['disabled'] = true;
	    // $meta_boxes['avatar']['fields']['email']['readonly'] = true;
	    $meta_boxes['avatar']['fields'] = array_merge( $meta_boxes['avatar']['fields'], [
	      [
	        'name'        => __( 'UUID', 'w4os' ),
	        'id'          => $prefix . 'uuid',
	        'type'        => 'text',
	        'placeholder' => __( 'Wil be set by the server', 'w4os' ),
	        'disabled'    => true,
	        'readonly'    => true,
	        'visible'     => [
	          'when'     => [['avatar_uuid', '!=', '']],
	          'relation' => 'or',
	        ],
	      ],
				[
						'name'          => __( 'Born', 'w4os' ),
						'id'            => $prefix . 'born',
						'type'          => 'datetime',
						'admin_columns' => [
								'position' => 'before date',
								'sort'     => true,
						],
				],
	      [
	        'name'    => __( 'Profile Picture', 'w4os' ),
	        'id'      => $prefix . 'profile_picture',
	        'type'    => 'image_select',
	        'options' => W4OS_Loader_Avatar::w4os_get_profile_picture(),
	        'readonly'    => true,
	      ],
	    ]);
	  }

	  return $meta_boxes;
	}

	static function automatic_title( $data, $postarr, $unsanitized_postarr, $update ) {
	  if(!$update) return $data;
	  if('avatar' !== $data['post_type']) return $data;

	  $avatar_name = trim(@$postarr['avatar_first_name'] . " " . @$postarr['avatar_last_name']);
	  if(empty($avatar_name)) {
	    $avatar_name = trim(get_post_meta($postarr['ID'], 'avatar_first_name', true) . " " . get_post_meta($postarr['ID'], 'avatar_last_name', true));
	  }
	  if(!empty($avatar_name)) $data['post_title'] = $avatar_name;

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

	  $return = substr(preg_replace('/[^[:alnum:]]/', '', $return), 0, 64);
	  if($value != $return) {
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

}

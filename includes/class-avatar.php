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

	public $ID;
	public $name;
	public $uuid;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @param [int|WP_Post|array] avatar post, or post id, or array with known proporties
	 */
	public function __construct($args = NULL) {
		global $w4osdb;

		if(is_numeric($args)) {
			$this->ID = $args;
			$post = get_post($this->ID);
		} else if(is_object($args) &! is_wp_error($args)) {
			$this->ID = $args->ID;
			$post = $args;
		} else {
			$post = false;
		}
		if($post) {
			// A WP avatar exists for this grid account
			$this->ID = $post->ID;
			$this->uuid = get_post_meta($post->ID, 'avatar_uuid', true);
			$this->name = get_post_meta($post->ID, 'avatar_name', true);
			$this->email = get_post_meta($post->ID, 'avatar_email', true);
			$this->user = get_post_meta($post->ID, 'avatar_owner', true);
			$this->FirstName = strstr($this->name, " ", true);
			$this->LastName = trim(strstr($this->name, " "));
			$this->lastseen = get_post_meta($post->ID, 'avatar_lastseen', true);
			$this->born = get_post_meta($post->ID, 'avatar_born', true);

			$this->get_simulator_data();
			// $this->meta = get_post_meta($post->ID);
		} else if (is_array($args)) {
			// No WP avatar for this grid account
			if( (isset($args['FirstName']) && isset($args['LastName']))) {
				$this->FirstName = $args['FirstName'];
				$this->LastName = $args['LastName'];
				$this->name = "$this->FirstName $this->LastName";
			} else if (isset($args['avatar_name'])) {
				$this->name = $args['avatar_name'];
			}
			$this->user_id = isset($args['avatar_owner']) ? $args['avatar_owner'] : NULL;
			if(isset($args['email'])) {
				$this->email = $args['email'];
				if(empty($this->user_id)) {
					$owner = get_user_by('email', $this->email);
					if($owner) $this->user_id = $owner->ID;
				}
			}
			if(isset($args['PrincipalID'])) $this->uuid = $args['PrincipalID'];
			// error_log("args " . print_r($args, true));
		}
	}

	function get_simulator_data() {
		if(!W4OS_DB_CONNECTED) return false;
		global $w4osdb;

		if(!w4os_empty($this->uuid)) {
			$condition = "PrincipalID = '$this->uuid'";
		} else if (!empty($this->name)) {
			$condition = "FirstName = '$this->FirstName' AND LastName = '$this->LastName'";
		// } else if (! is_email($this->email)) {
		// 	// careful:: in this case, only use if 1 single result
		// 	$condition = "Email = '$this->email' AND count = 1";
		}
		if(!empty($condition)) {
			$matches = $w4osdb->get_results("SELECT * FROM UserAccounts
				LEFT JOIN userprofile ON PrincipalID = userUUID
				LEFT JOIN GridUser ON PrincipalID = UserID
				WHERE active = 1 AND $condition"
			);
			if(count($matches) > 1) {
				$message = sprintf(
					__('Simulator database contains more than one avatar matching %s. This should never happen and requires an immediate fix.', 'w4os'),
					"$this->FirstName $this->LastName $this->uuid",
				);
				w4os_transient_admin_notice($message, 'warning');
				error_log($message);
			} else if (count($matches) == 1) {
				$grid_data = array_shift($matches);
				$this->uuid = $grid_data->UserID;
				$this->FirstName = $grid_data->FirstName;
				$this->LastName = $grid_data->LastName;
				$this->name = "$this->FirstName $this->LastName";
				$this->email = $grid_data->Email;
				$this->lastseen = $grid_data->Login;
				$this->born = $grid_data->Created;
			}
		}
	}

	function create_post() {
		$post_id = wp_insert_post(array(
			'ID' => (isset($this->ID)) ? $this->ID : 0,
			'post_type' => 'avatar',
			'post_status' => $this->avatar_status(),
			'post_author' => (empty($this->user_id)) ? 0 : $this->user_id,
			'post_title' => $this->name,
			'post_date_gmt' => date( 'Y-m-d H:i:s', ((!empty($this->born) && $this->born > 0) ? $this->born : current_time('timestamp', true)) ),
			'meta_input' => array(
				'avatar_email' => $this->email,
				'avatar_owner' => (empty($this->user_id)) ? NULL : $this->user_id,
				'avatar_uuid' => $this->uuid,
				'avatar_born' => $this->born,
				'avatar_lastseen' => $this->lastseen,
				'avatar_name' => $this->name,
				'avatar_firstname' => $this->FirstName,
				'avatar_lastname' => $this->LastName,
			),
		));
		return $post_id;
	}

	/**
	 * Sync avatar info from OpenSimulator
	 * @param  object $user_or_id   user object or user id
	 * @param  key    $uuid         if set, create link with avatar and update info
	 *                              if not set, update avatar info if link exists
	 * @return object       [description]
	 */
	function sync_single_avatar() {
		$user = get_user_by('email', $this->email);
		$user_id = ($user) ? $user->ID : NULL;

		$postarr = array(
			'ID' => $this->ID,
			'post_author' => ($user) ? $user->ID : 0,
			'meta_input' => array(
				'avatar_lastseen' => $this->lastseen,
				'avatar_email' => $this->email,
				'avatar_owner' => ($user) ? $user->ID : NULL,
			),
		);
		// if($this->is_service()) $postarr['post_status'] =  'service';
		// if($this->is_model()) $postarr['post_status'] =  'model';
		error_log(print_r($this, true));
		$postarr['post_status'] = $this->avatar_status();

		// if(isset($this->lastseen)) $postarr['meta_input'][
		// if(isset($this->email)) $postarr['meta_input']['avatar_email'] = $this->email;
		//
		if(!empty($postarr)) {
			$postarr['ID'] = $this->ID;
			// error_log(__FUNCTION__ . ' ' . $this->name . ' ' . print_r($postarr, true));
			$result = wp_update_post( $postarr, true, true );
			if($result) {
				// error_log("$this->name succeeded " . print_r(get_post_meta($this->ID), true));
			} else {
				error_log($result>get_error_message());
			}
		}

		return;

		if(!W4OS_DB_CONNECTED) return false;
	  global $w4osdb;

		if(is_numeric($post)) {
			$post_id = $post;
			$post = get_post($post_id);
		}
		if(empty($post) || is_wp_error($post)) return false;
		if($post->post_type != 'avatar') return false;

		return;

	  if(w4os_empty($uuid)) {
	    $condition = "Email = '$post->user_email'";
	  } else {
	    $condition = "PrincipalID = '$uuid'";
	  }

	  $avatars=$w4osdb->get_results("SELECT * FROM UserAccounts
	    LEFT JOIN userprofile ON PrincipalID = userUUID
	    LEFT JOIN GridUser ON PrincipalID = UserID
	    WHERE active = 1 AND $condition"
	  );
	  if(empty($avatars)) return false;

	  $avatar_row = array_shift($avatars);
	  if(w4os_empty($uuid)) $uuid = $avatar_row->PrincipalID;

	  if(w4os_empty($uuid)) {
	    w4os_profile_dereference($post);
	    return false;
	  }

	  $post->add_role('grid_user');

	  update_user_meta( $post->ID, 'w4os_uuid', $uuid );
	  update_user_meta( $post->ID, 'w4os_firstname', $avatar_row->FirstName );
	  update_user_meta( $post->ID, 'w4os_lastname', $avatar_row->LastName );
	  update_user_meta( $post->ID, 'w4os_avatarname', trim($avatar_row->FirstName . ' ' . $avatar_row->LastName) );
	  update_user_meta( $post->ID, 'w4os_created', $avatar_row->Created);
	  update_user_meta( $post->ID, 'w4os_lastseen', $avatar_row->Login);
	  update_user_meta( $post->ID, 'w4os_profileimage', $avatar_row->profileImage );
	  return $uuid;
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
			array (
				'hook' => 'save_post',
				'callback' => 'save_avatar_slug',
			),
			array (
				'hook' => 'post_updated',
				'callback' => 'update_password',
				'accepted_args' => 3,
			),

			array (
				'hook' => 'init',
				'callback' => 'remove_avatar_delete_cap',
			),
			array(
				'hook' => 'wp_trash_post',
				'callback' => 'avatar_deletion_warning',
			),
			array(
				'hook' => 'admin_head',
				'callback' => 'remove_avatar_edit_delete_action',
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

			array (
				'hook' => 'views_edit-avatar',
				'callback' => 'display_synchronization_status',
			),

			array(
				'hook' => 'post_row_actions',
				'callback' => 'remove_avatar_delete_row_actions',
				'accepted_args' => 2,
			),

			// array (
			// 	'hook' => 'load-edit.php',
			// 	'callback' => 'add_in_admin_footer',
			// ),

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

	// static function add_in_admin_footer() {
	// 	$screen = get_current_screen();
	// 	if( 'edit-avatar' !== $screen->id ) return;
	//
	// 	add_action( 'in_admin_footer', function(){
	// 		echo '<p>Goodbye from <strong>in_admin_footer</strong>!</p>';
	// 	});
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

	  $meta_boxes['avatar'] = [
	    'title'      => __( 'Profile', 'w4os' ),
	    'id'         => 'avatar-profile-fields',
	    'post_types' => ['avatar'],
	    'context'    => 'after_title',
	    'style'      => 'seamless',
			'fields'     => [
				'name' => [
				// 		// 'name'     => __( 'Avatar Name', 'w4os' ),
				// 		'id'       => $prefix . 'name',
				// 		'type'     => 'hidden',
				// 		'std'	=> 'saved ' . self::current_avatar_name(),
				// 		// 'callback' => __CLASS__ . '::current_avatar_name',
				// ],
				// 'saved_name' => [
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
						'filterable' => true,
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
						'desc'	=> __('(not implemented)', 'w4os'),
						'disabled' => true,
						'readonly' => true,
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
						// 'required' => W4OS::is_new_post() &! current_user_can('administrator'),
	      ],
	      [
	          'name'    => __( 'Confirm password', 'w4os' ),
	          'id'      => $prefix . 'password_2',
	          'type'    => 'password',
	          'columns' => 4,
						// 'required' => W4OS::is_new_post() &! current_user_can('administrator'),
	      ],
	      [
	          'name'    => __( 'Same password as WP user', 'w4os' ),
	          'id'      => $prefix . 'use_wp_password',
	          'type'    => 'switch',
	          'style'   => 'rounded',
	          'std'     => false,
						'desc'	=> __('(not implemented)', 'w4os'),
						'disabled' => true,
						'readonly' => true,
	          'columns' => 4,
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
				'std' => self::generate_name(),
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
			$meta_boxes['avatar']['fields']['uuid'] = [
				'name'        => __( 'UUID', 'w4os' ),
				'id'          => $prefix . 'uuid',
				'type'        => 'text',
				'placeholder' => __( 'Wil be set by the server', 'w4os' ),
				// 'disabled'    => true,
				'readonly'    => true,
				// 'save_field' => false,
				// 'visible'     => [
				// 	'when'     => [['avatar_uuid', '!=', '']],
				// 	'relation' => 'or',
				// ],
			];

			$meta_boxes['avatar']['fields']['lastseen'] = [
				'name'          => __( 'Last seen', 'w4os' ),
				'id'            => $prefix . 'lastseen',
				'type'          => 'datetime',
				'timestamp'     => true,
				// 'disabled'      => true,
				'readonly'      => true,
				// 'save_field' => false,
				'admin_columns' => [
					'position' => 'before date',
					'sort'     => true,
				],
				// 'visible'       => [
				// 	'when'     => [['avatar_uuid', '!=', '']],
				// 	'relation' => 'or',
				// ],
			];
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

	function get_uuid() {
		return get_post_meta($this->post, 'avatar_uuid', true);
	}

	static function update_password( $post_ID, $post_after, $post_before ) {
		if($post_after->post_type !== 'avatar') return;
		if(empty($_POST['avatar_password']) || $_POST['avatar_password'] != $_POST['avatar_password_2']) return;
		$avatar = new W4OS3_Avatar($post_after);
		if(w4os_empty($avatar->uuid)) return;
		global $w4osdb;

		$password=stripcslashes($_POST['avatar_password']);
		$salt = md5(w4os_gen_uuid());
	  $hash = md5(md5($password) . ":" . $salt);

		$new = array(
			'UUID' => $avatar->uuid,
			'passwordHash'   => $hash,
			'passwordSalt'   => $salt,
		);
		$w4osdb->replace( 'auth', $new );

		if(is_wp_error($new)) {
			error_log($new->get_error_message());
			wp_redirect($_POST['referredby']);
			die();
		}
	}

	static function insert_post_data( $data, $postarr, $unsanitized_postarr, $update ) {
	  if(!$update) return $data;
	  if('avatar' !== $data['post_type']) return $data;

		$avatar = new self ($postarr['ID']);

		$avatar_name = trim(@$postarr['avatar_name']);
		if(!empty($avatar->name)) {
			$avatar_name = $avatar->name;
			unset($data['avatar_name']);
		}
		if(!empty($avatar_name)) {
			$data['post_title'] = $avatar_name;
		}

		if(empty($avatar_name) && W4OS::is_new_post()) {
			$avatar_name = 'TEMPORARY UNDEFINED';
		}

		$uuid = $avatar->uuid;
		if(w4os_empty($uuid)) {
			$uuid = self::get_uuid_by_name($avatar_name);
		// } else {
		// 	unset($data['avatar_uuid']);
		}
		if(w4os_empty($uuid) && $avatar_name != 'TEMPORARY UNDEFINED') {
			// We must create one
			$avatar->name = $avatar_name;
			$uuid = $avatar->create($avatar, $data, $postarr);
			if( $_REQUEST['action'] == 'trash' && ( $_REQUEST['post'] == $avatar->ID || in_array($avatar->ID, $_REQUEST['post']) ) ) {
				return $data;
			} else if(!$uuid || w4os_empty($uuid) || empty($avatar_name)) {
				if(isset($_POST['referredby'])) {
					wp_redirect($_POST['referredby']);
					die();
				} else {
					w4os_transient_admin_notice("Could not create $avatar->name and redirect failed<pre>"
					. "\nuuid " . print_r($uuid, true)
					. "\navatar " . print_r($avatar, true)
					. "\ndata " . print_r($data, true)
					. "\npostarr " . print_r($postarr, true)
					. "\nREQUEST " . print_r($_REQUEST, true)
					. '</pre>'
					, 'error');
				}
			} else {
				update_post_meta($avatar->ID, 'avatar_uuid', $uuid);
			}
		}
		$data['post_status'] = $avatar->avatar_status();
	  return $data;
	}

	static function save_avatar_slug( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) return;
		$post = get_post($post_id);
		if('avatar' !== $post->post_type) return;

		remove_action( 'save_post', __CLASS__ . '::' . __FUNCTION__ );

		$slug = sanitize_title($post->post_title);
		wp_update_post( array( 'ID' => $post_id, 'post_name' => $slug ));

		add_action( 'save_post', __CLASS__ . '::' . __FUNCTION__ );
	}

	function update($data = []) {
	}

	function create($avatar = NULL, $data = [], $postarr = []) {
		if(!W4OS_DB_CONNECTED) return;
		if(!is_object($avatar)) return; // We might need to handle $this in the future
		if( $_REQUEST['action'] == 'trash' && ( $_REQUEST['post'] == $avatar->ID || in_array($avatar->ID, $_REQUEST['post']) ) ) return;

		global $w4osdb;
		$errors = false;

		$uuid = self::get_uuid_by_name($avatar->name);
		if ( $uuid ) {
	    w4os_notice(__("This user already has an avatar.", 'w4os'), 'fail');
			error_log('This user already has an avatar');
	    return $uuid;
		}

		if(!self::check_name_availability($avatar->name)) {
			w4os_notice(__("This name is not available.", 'w4os'), 'error') ;
			return false;
		}

		$parts = explode(' ', $avatar->name);
		$FirstName=$parts[0];
		$LastName=$parts[1];

		$model = esc_attr(empty($postarr['avatar_model']) ? W4OS_DEFAULT_AVATAR : $postarr['avatar_model']);

		if (isset($postarr['avatar_password']) && $postarr['avatar_password'] != $postarr['avatar_password_2']) {
			w4os_notice(__("Passwords don't match.", 'w4os'), 'error') ;
			return false;

			// TODO: also check if same pwd as user if use_wp_password is set
			// $owner = get_user($postarr['avatar_owner']);
			// if ( ! wp_check_password($params['w4os_password_1'], $owner->user_pass, $owner->ID)) {
			// 		$errors=true; w4os_notice(__("The password does not match.", 'w4os'), 'error') ;
			// 		return false;
			// }
		}
		$password=stripcslashes($postarr['avatar_password']);

		$newavatar_uuid = w4os_gen_uuid();
		$check_uuid = $w4osdb->get_var("SELECT PrincipalID FROM UserAccounts WHERE PrincipalID = '$newavatar_uuid'");
	  if ( $check_uuid ) {
	    w4os_notice(__( 'This should never happen! Generated a random UUID that already existed. Sorry. Try again.', 'w4os' ), 'fail');
	    return false;
	  }

		$salt = md5(w4os_gen_uuid());
	  $hash = md5(md5($password) . ":" . $salt);
		$created = current_time('timestamp', true);
		$HomeRegionID = $w4osdb->get_var("SELECT UUID FROM regions WHERE regionName = '" . W4OS_DEFAULT_HOME . "'");
	  if(empty($HomeRegionID)) $HomeRegionID = '00000000-0000-0000-0000-000000000000';

		$result = $w4osdb->insert (
	    'UserAccounts', array (
				'PrincipalID' => $newavatar_uuid,
				'ScopeID' => W4OS_NULL_KEY,
				'FirstName'   => $FirstName,
				'LastName'   => $LastName,
				'Email' => $postarr['avatar_email'],
				'ServiceURLs' => 'HomeURI= InventoryServerURI= AssetServerURI=',
				'Created' => $created,
	    )
	  );
		if ( !$result ) w4os_notice(__("Error while creating user", 'w4os'), 'fail');
		else $result = $w4osdb->insert (
			'auth', array (
				'UUID' => $newavatar_uuid,
				'passwordHash'   => $hash,
				'passwordSalt'   => $salt,
				'webLoginKey' => W4OS_NULL_KEY,
			)
		);
		if ( !$result ) w4os_notice(__("Error while setting password", 'w4os'), 'fail');
		else $result = $w4osdb->insert (
	    'GridUser', array (
	      'UserID' => $newavatar_uuid,
	      'HomeRegionID' => $HomeRegionID,
	      'HomePosition' => '<128,128,21>',
	      'LastRegionID' => $HomeRegionID,
	      'LastPosition' => '<128,128,21>',
	    )
	  );
	  if ( !$result ) w4os_notice(__("Error while setting home region", 'w4os'), 'fail');
		else {
			$model_firstname=strstr($model, " ", true);
			$model_lastname=trim(strstr($model, " "));
			$model_uuid = $w4osdb->get_var("SELECT PrincipalID FROM UserAccounts WHERE FirstName = '$model_firstname' AND LastName = '$model_lastname'");
			if(w4os_empty($model_uuid))
			error_log(sprintf(
				'%s could not find model %s',
				__CLASS__ . '->' . __FUNCTION__ . '()',
				"$model_firstname $model_lastname"
			));

			$inventory_uuid = w4os_gen_uuid();
		  $result = $w4osdb->insert (
		    'inventoryfolders', array (
		      'folderName' => 'My Inventory',
		      'type' => 8,
		      'version' => 1,
		      'folderID' => $inventory_uuid,
		      'agentID' => $newavatar_uuid,
		      'parentFolderID' => W4OS_NULL_KEY,
		    )
		  );
		}
		if ( !$result ) w4os_notice(__("Error while creating user inventory", 'w4os'), 'fail');
		else {
			$bodyparts_uuid = w4os_gen_uuid();
			$bodyparts_model_uuid = w4os_gen_uuid();
			$currentoutfit_uuid = w4os_gen_uuid();
			$folders = array(
				array('Textures', 0, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Sounds', 1, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Calling Cards', 2, 2, w4os_gen_uuid(), $inventory_uuid ),
				array('Landmarks', 3, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Photo Album', 15, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Clothing', 5, 3, w4os_gen_uuid(), $inventory_uuid ),
				array('Objects', 6, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Notecards', 7, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Scripts', 10, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Body Parts', 13, 5, $bodyparts_uuid, $inventory_uuid ),
				array('Trash', 14, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Animations', 20, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Gestures', 21, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Lost And Found', 16, 1, w4os_gen_uuid(), $inventory_uuid ),
				array('Current Outfit', 46, 1, $currentoutfit_uuid, $inventory_uuid ),
				// array('My Outfits', 48, 1, $myoutfits_uuid, $inventory_uuid ),
				// array("$model_firstname $model_lastname", 47, 1, $myoutfits_model_uuid, $myoutfits_uuid ),
				// array('Friends', 2, 2, w4os_gen_uuid(), $inventory_uuid ),
				// array('Favorites', 23, w4os_gen_uuid(), $inventory_uuid ),
				// array('All', 2, 1, w4os_gen_uuid(), $inventory_uuid ),
			);
			if(!w4os_empty($model_uuid)) {
				$folders[] = array("$model_firstname $model_lastname outfit", -1, 1, $bodyparts_model_uuid, $bodyparts_uuid );
			}
			foreach($folders as $folder) {
	      $name = $folder[0];
	      $type = $folder[1];
	      $version = $folder[2];
	      $folderid = $folder[3];
	      $parentid = $folder[4];
	      if ($result) $result = $w4osdb->insert (
	        'inventoryfolders', array (
	          'folderName' => $name,
	          'type' => $type,
	          'version' => $version,
	          'folderID' => $folderid,
	          'agentID' => $newavatar_uuid,
	          'parentFolderID' => $parentid,
	        )
	      );
	      if( !$result ) {
					w4os_notice(__("Error while adding folder $folder", 'w4os'), 'fail');
					break;
				}
	    }
		}

		// if ( $result ) {
		//   $result = $w4osdb->get_results("SELECT folderName,type,version FROM inventoryfolders WHERE agentID = '$model_uuid' AND type != 8");
		//   if($result) {
		//     foreach($result as $row) {
		//       $result = $w4osdb->insert (
		//         'inventoryfolders', array (
		//           'folderName' => $row->folderName,
		//           'type' => $row->type,
		//           'version' => $row->version,
		//           'folderID' => w4os_gen_uuid(),
		//           'agentID' => $newavatar_uuid,
		//           'parentFolderID' => $inventory_uuid,
		//         )
		//       );
		//       if( ! $result ) break;
		//     }
		//   }
		// }

		if ( $result &! w4os_empty($model_uuid) ) {
			// TODO: add default ruth values if no model found

	    $model = $w4osdb->get_results("SELECT Name, Value FROM Avatars WHERE PrincipalID = '$model_uuid'");
	    // w4os_notice(print_r($result, true), 'code');
	    // foreach($result as $row) {
	    //   w4os_notice(print_r($row, true), 'code');
	    //   w4os_notice($row->Name . " = " . $row->Value);
	    // }

	    // foreach($avatars_prefs as $var => $val) {
	    if($model) {
	      foreach($model as $row) {
	        unset($newitem);
	        unset($newitems);
	        unset($newvalues);
	        $Name = $row->Name;
	        $Value = $row->Value;
	        if (strpos($Name, 'Wearable') !== FALSE) {
	          // Must add a copy of item in inventory
	          $uuids = explode(":", $Value);
	          $item = $uuids[0];
	          $asset = $uuids[1];
	          $destinventoryid = $w4osdb->get_var("SELECT inventoryID FROM inventoryitems WHERE assetID='$asset' AND avatarID='$newavatar_uuid'");
	          if(!$destinventoryid) {
	            $newitem = $w4osdb->get_row("SELECT * FROM inventoryitems WHERE assetID='$asset' AND avatarID='$model_uuid'", ARRAY_A);
	            $destinventoryid = w4os_gen_uuid();
	            $newitem['inventoryID'] = $destinventoryid;
	            $newitems[] = $newitem;
	            $Value = "$destinventoryid:$asset";
	          }
	        } else if (strpos($Name, '_ap_') !== FALSE) {
	          $items = explode(",", $Value);
	          foreach($items as $item) {
	            $asset = $w4osdb->get_var("SELECT assetID FROM inventoryitems WHERE inventoryID='$item'");
	            $destinventoryid = $w4osdb->get_var("SELECT inventoryID FROM inventoryitems WHERE assetID='$asset' AND avatarID='$newavatar_uuid'");
	            if(!$destinventoryid) {
	              $newitem = $w4osdb->get_row("SELECT * FROM inventoryitems WHERE assetID='$asset' AND avatarID='$model_uuid'", ARRAY_A);
	              $destinventoryid = w4os_gen_uuid();
	              $newitem['inventoryID'] = $destinventoryid;
	              // $Value = $destinventoryid;
	              $newitems[] = $newitem;
	              $newvalues[] = $destinventoryid;
	            }
	          }
	          if($newvalues) $Value = implode(",", $newvalues);
	        }
	        if(!empty($newitems)) {
	          foreach($newitems as $newitem) {
	            // $destinventoryid = w4os_gen_uuid();
	            // w4os_notice("Creating inventory item '$Name' for $firstname");
	            $newitem['parentFolderID'] = $bodyparts_model_uuid;
	            $newitem['avatarID'] = $newavatar_uuid;
	            $result = $w4osdb->insert ('inventoryitems', $newitem);
	            if( !$result ) w4os_notice(__("Error while adding inventory item", 'w4os'), 'fail');
	            // w4os_notice(print_r($newitem, true), 'code');
	            // echo "<pre>" . print_r($newitem, true) . "</pre>"; exit;

	            // Adding aliases in "Current Outfit" folder to avoid FireStorm error message
	            $outfit_link=$newitem;
	            $outfit_link['assetType']=24;
	            $outfit_link['assetID']=$newitem['inventoryID'];
	            $outfit_link['inventoryID'] = w4os_gen_uuid();
	            $outfit_link['parentFolderID'] = $currentoutfit_uuid;
	            $result = $w4osdb->insert ('inventoryitems', $outfit_link);
	            if( !$result ) w4os_notice(__("Error while adding inventory outfit link", 'w4os'), 'fail');
	          }
	          // } else {
	          //   w4os_notice("Not creating inventory item '$Name' for $firstname");
	        }
					$result = $w4osdb->insert (
						'Avatars', array (
							'PrincipalID' => $newavatar_uuid,
							'Name' => $Name,
							'Value' => $Value,
						)
					);
	        if( !$result ) w4os_notice(__("Error while adding avatar", 'w4os'), 'fail');
	      }
	    } else {
				error_log(sprintf(
					'%s could find model %s\'s inventory items',
					__FUNCTION__,
					"$model_firstname $model_lastname"
				));
			}
	  }

	  if( ! $result ) {
			// TODO: delete sql rows created during the process

			$message = sprintf( __("Errors occurred while creating avatar %s", 'w4os'), "$FirstName $LastName" );
			w4os_notice(sprintf( $message, "$FirstName $LastName" ), 'fail');
			error_log(sprintf( $message, "$FirstName $LastName" ));
			return false;
	  }

		return $newavatar_uuid;
		// w4os_notice(sprintf( __('Avatar %s created successfully.', 'w4os' ), "$FirstName $LastName" ), 'success' );
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
			esc_sql($FirstName),
			esc_sql($LastName),
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
			if(is_array($_REQUEST['post'])) return;
			$post_id = esc_attr($_REQUEST['post']);
			$post = get_post($post_id);
		}
		if($post)	return $post->post_title;
	}

	static function generate_name() {

		// Try WP User name first
		$user = wp_get_current_user();
		if($user) {
			$name = self::sanitize_name(
				(empty($user->display_name))
				? "$user->first_name $user->last_name"
				: $user->display_name
			);
			if(self::check_name_availability($name)) return $name;
	  }

		// Fallback to random name
		$generator = new \CodeNameGenerator\CodeNameGenerator();
		for ($i = 0; $i < 10; $i++) {
			// We don't want to run forever, even if it's unlikely
			$name = $generator->generate();
			$name = self::sanitize_name(ucwords(preg_replace("/([^-]+)-([^-]+)(-.*)?/", '$1 $2', $name)));
			if(self::check_name_availability($name)) return $name;
		}

		return $name;
	}

	static function count() {
		// TODO: count broken assets
		// SELECT inventoryname, inventoryID, assetID, a.id FROM inventoryitems LEFT JOIN assets AS a ON id = assetID WHERE a.id IS NULL;

		if(! W4OS_DB_CONNECTED) return;
	  global $wpdb, $w4osdb;
		if(!isset($wpdb)) return false;
		if(!isset($w4osdb)) return false;

		$count['wp_users'] = count_users()['total_users'];
		$count['grid_accounts'] = 0;	// Deprecated in 3.0
		$count['wp_linked'] = 0;			// Deprecated in 3.0
		$count['wp_only'] = NULL;			// Deprecated in 3.0
		$count['grid_only'] = NULL;		// Deprecated in 3.0
		$count['sync'] = 0;						// Deprecated in 3.0?

		$count['models'] = $w4osdb->get_var(sprintf(
			"SELECT count(*) FROM UserAccounts
			WHERE FirstName = '%s'
			OR LastName = '%s'
			",
			get_option('w4os_model_firstname'),
			get_option('w4os_model_lastname'),
		));

		$count['tech'] = $w4osdb->get_var(sprintf(
			"SELECT count(*) FROM UserAccounts
			WHERE (Email IS NULL OR Email = '')
			AND FirstName != '%s'
			AND LastName != '%s'
			",
			esc_sql(get_option('w4os_model_firstname')),
			esc_sql(get_option('w4os_model_lastname')),
		));

		/**
		 * Will be deprecated in 3.0
		 * @var [type]
		 */
		$accounts = W4OS3_Avatar::get_avatars_ids_and_uuids();
	  foreach ($accounts as $key => $account) {
			if( ! isset($account['w4os_uuid']) ) $account['w4os_uuid'] = NULL;
			if(!w4os_empty($account['w4os_uuid'])) $count['wp_linked']++;
			if( ! isset($account['PrincipalID']) ) $account['PrincipalID'] = NULL;

			if( ! w4os_empty($account['PrincipalID']) ) {
				$count['grid_accounts']++;
				if($account['PrincipalID'] == $account['w4os_uuid']) {
					$count['sync']++;
				} else {
					error_log("grid only " . print_r($account, true));
					$count['grid_only'] += 1;
				}
			} else {
				$account['PrincipalID'] = NULL;
				if(isset($account['w4os_uuid']) &! w4os_empty($account['w4os_uuid'])) {
					$count['wp_only']++;
				}
			}
	  }
		// End deprecated

	  return $count;
	}

	static function get_avatars_ids_and_uuids() {
		if(! W4OS_DB_CONNECTED) return;
		global $wpdb, $w4osdb;
		if(!isset($wpdb)) return false;
		if(!isset($w4osdb)) return false;

		$GridAccounts = $w4osdb->get_results(sprintf(
			"SELECT CONCAT(FirstName, ' ', LastName) as avatar_name, PrincipalID, Email as email FROM UserAccounts
			WHERE active = 1
			AND (FirstName != 'GRID' OR LastName != 'SERVICES')
			AND FirstName != ''
			AND LastName != ''
			",
			esc_sql(get_option('w4os_model_firstname')),
			esc_sql(get_option('w4os_model_lastname')),
		), OBJECT_K );
		// AND Email is not NULL AND Email != ''

		foreach (	$GridAccounts as $key => $row ) {
			// if(empty($row->email)) continue;
			// $GridAccounts[$row->email] = (array)$row;
			$accounts[$key] = (array)$row;
		}

		$avatars = $wpdb->get_results(
			"SELECT post_title as avatar_name, mail.meta_value as email, ID, user.meta_value as user_id, uuid.meta_value AS w4os_uuid
			FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta as mail ON ID = mail.post_id AND mail.meta_key = 'avatar_email'
			LEFT JOIN $wpdb->postmeta as user ON ID = user.post_id AND user.meta_key = 'avatar_owner'
			LEFT JOIN $wpdb->postmeta as uuid ON ID = uuid.post_id AND uuid.meta_key = 'avatar_uuid'
			WHERE post_type = 'avatar' AND ( post_status='publish' OR post_status='model' OR post_status='bot' OR post_status='service' ) ",
		OBJECT_K );

		foreach (	$avatars as $key => $row ) {
			if(empty($key)) continue;
			// $WPGridAccounts[$row->email] = (array)$row;
			if (empty($accounts[$key])) {
				$accounts[$key] = (array)$row;
			} else {
				$accounts[$key] = array_merge( $accounts[$key], (array)$row );
			}
		}

		return $accounts;
	}

	static function sync_avatars() {
		if(! W4OS_DB_CONNECTED) return;
		global $wpdb, $w4osdb;
		if(!isset($wpdb)) return false;
		if(!isset($w4osdb)) return false;

	  $accounts = W4OS3_Avatar::get_avatars_ids_and_uuids();
		$messages=array();
		$users_created=[];
		$users_updated=[];
		foreach ($accounts as $key => $account) {
			// $user = @get_user_by('ID', $account['user_id']);
	    // First cleanup NULL_KEY and other empty UUIDs
	    if(!isset($account['PrincipalID']) || w4os_empty($account['PrincipalID'])) $account['PrincipalID'] = NULL;
	    if(!isset($account['w4os_uuid']) || w4os_empty($account['w4os_uuid'])) $account['w4os_uuid'] = NULL;

			if(isset($account['ID'])) {
				$avatar = new W4OS3_Avatar($account['ID']);
			} else {
				$avatar = new W4OS3_Avatar($account);
			}

	    if( isset($account['PrincipalID']) &! w4os_empty($account['PrincipalID']) ) {
				if ( $account['PrincipalID'] == $account['w4os_uuid'] ) {
					// already linked, just resync
					error_log("$avatar->name already linked, resync");
					$avatar->get_simulator_data();
					$avatar->sync_single_avatar();
				} else if ( isset($account['user_id']) &! empty($account['user_id']) ) {
					// wrong reference, but an avatar exists for this WP user, replace reference
					error_log("$avatar->name wrong reference, but an avatar exists in WP, replace wrong reference");
					// $result = W4OS_Avatar::sync_single_avatar($account['user_id'], $account['PrincipalID']);
					// if(W4OS_Avatar::sync_single_avatar($account['user_id'], $account['PrincipalID']))
					// $users_updated[] = sprintf('<a href=%s>%s %s</a>', get_edit_user_link($newid), $account['FirstName'], $account['LastName']);
					// else
					// $errors[] = '<p class=error>' .  sprintf(__('Error while updating %s %s (%s) %s', 'w4os'), $account['FirstName'], $account['LastName'], $account['email'], $result) . '</p>';
				} else {
					// not present in WP, create a post
					error_log("$avatar->name not present in WP, create a post");

					$avatar->get_simulator_data();
					if(!$avatar->create_post()) {
						error_log("could not create avatar post for $avatar->name");
					}

					// TODO: create user if none and wp_create_users is enabled
					// $user = get_user_by('email', $account['email']);
					// if($user) {
					// 	// assign to existing user
					// 	error_log("assign $avatar->name to $user->display_name");
					// } else if (W4OS::get_option('w4os_settings:create_wp_users', 'nothing')) {
					// 	// create user if create_wp_users is true
					// 	error_log("TODO: create user $avatar->name");
					// 	// 	$newid = wp_insert_user(array(
					// 	// 		'user_login' => w4os_create_user_login($account['FirstName'], $account['LastName'], $account['email']),
					// 	// 		'user_pass' => wp_generate_password(),
					// 	// 		'user_email' => $account['email'],
					// 	// 		'first_name' => $account['FirstName'],
					// 	// 		'last_name' => $account['LastName'],
					// 	// 		'role' => 'grid_user',
					// 	// 		'display_name' => trim($account['FirstName'] . ' ' . $account['LastName']),
					// 	// 	));
					// 	// 	if(is_wp_error( $newid )) {
					// 	// 		$errors[] = $newid->get_error_message();
					// 	// 	} else if(W4OS_Avatar::sync_single_avatar($newid, $account['PrincipalID'])) {
					// 	// 		$users_created[] = sprintf('<a href=%s>%s %s</a>', get_edit_user_link($newid), $account['FirstName'], $account['LastName']);
					// 	// 	} else {
					// 	// 		$errors[] = '<p class=error>' .  sprintf(__('Error while updating newly created user %s for %s %s (%s) %s', 'w4os'), $newid, $account['FirstName'], $account['LastName'], $account['email'], $result) . '</p>';
					// 	// 	}
					// }

				}
			} else if(isset($account['w4os_uuid']) &! w4os_empty($account['w4os_uuid'])) {
				// Avatar does not exit (anymore) on grid, delete it
				wp_delete_post( $avatar->ID, true );
				error_log("$avatar->name does not exist anymore on the grid, it has been deleted from WordPress");
				// w4os_profile_dereference($account['user_id']);
				// $users_dereferenced[] = sprintf('<a href=%s>%s</a>', get_edit_user_link($account['user_id']), $account['user_id']);
			} else {
			// // No linked account, but none referenced so we should not interfer
			// 	w4os_profile_dereference($account['user_id']);
			}
		}

		if(!empty($users_updated)) $messages[] = sprintf(_n(
			'%d reference updated',
			'%d references updated',
			count($users_updated),
			'w4os',
		), count($users_updated)) . ': ' . join(', ', $users_updated);
		if(!empty($users_created)) $messages[] = '<p>' . sprintf(_n(
	    '%d new WordPress account created',
	    '%d new WordPress accounts created',
	    count($users_created),
	    'w4os',
	  ), count($users_created)) . ': ' . join(', ', $users_created);
	  if(!empty($users_dereferenced)) $messages[] = sprintf(_n(
	    '%d broken reference removed',
	    '%d broken references removed',
	    count($users_dereferenced),
	    'w4os',
	  ), count($users_dereferenced));

		// // add_action('admin_init', 'w4os_profile_sync_all');
		// w4os_profile_sync_all();
		update_option('w4os_sync_users', NULL);
		// // return '<pre>' . print_r($messages, true) . '</pre>';
		if(!empty($errors)) $messages[] = '<p class=sync-errors><ul><li>' . join('</li><li>', $errors) . '</p>';
		// $messages[] = w4os_array2table($accounts, 'accounts', 2);
		if(!empty($messages)) return '<div class=messages><p>' . join('</p><p>', $messages) . '</div>';
	}

	static function display_synchronization_status($views = NULL) {
		$count = self::count();

		$messages = [];
		if($count['grid_only']  > 0 ) {
			$messages[] = sprintf(_n(
				'%d grid account has no linked WP account. Syncing will create a new WP account.',
				'%d grid accounts have no linked WP account. Syncing will create new WP accounts.',
				$count['grid_only'],
				'w4os'
			), $count['grid_only']);
		}
		if($count['wp_only']  > 0 ) {
			$messages[] = sprintf(_n(
				'%d WordPress avatar has no related account in OpenSimulator database (corrupt reference or deleted). Syncing accounts will delete it from WordPress database too.',
				'%d WordPress avatars have no related account in OpenSimulator database corrupt reference or deleted). Syncing accounts will remove these from WordPress database too.',
				$count['wp_only'],
				'w4os'
			), $count['wp_only']);
		}
		// if($count['tech'] > 0) {
		// 	$messages[] = sprintf(_n(
		// 		"%d grid avatar (other than models) has no email address, it is handled as a service account and is not displayed in avatars list.",
		// 		"%d grid avatars (other than models) have no email address, they are handled as service accounts and are not displayed in avatars list.",
		// 		$count['tech'],
		// 		'w4os'
		// 	), $count['tech'])
		// 	. ' '
		// 	. sprintf(
		// 		__('Users avatars need an email address for %s and %s to work properly.', 'w4os'),
		// 		'<em>OpenSimulator</em>',
		// 		'<em>w4os</em>',
		// 	);
		// }
		if($count['grid_only'] + $count['wp_only'] > 0) {
			echo '<p class=description>
			<form method="post" action="options.php" autocomplete="off">
			<input type="hidden" input-hidden" id="w4os_sync_users" name="w4os_sync_users" value="1">';
			settings_fields( 'w4os_status' );
			submit_button(__('Synchronize users now', 'w4os'));
			_e('Synchronization is made at plugin activation and is handled automatically afterwards, but in certain circumstances it may be necessary to initiate it manually to get an immediate result, especially if users have been added or deleted directly from the grid administration console.', 'w4os');
			echo '</form></p>';
		}
		if(!empty($sync_result))
		echo '<p class=info>' . $sync_result . '</p>';
		// include(plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/table-header-avatar.php');

		foreach($messages as $message) {
			echo "<p>" . $message . "</p>";
		}
		return $views;
	}

	function is_model() {
		return ( $this->FirstName == get_option('w4os_model_firstname') || $this->FirstName == get_option('w4os_model_firstname') );
	}

	function is_service() {
		return ( !$this->is_model() && empty($this->email) );
	}

	function avatar_status() {
		if($this->is_model()) return 'model';
		if($this->is_service()) return 'service';
		return 'publish';
	}

	/**
	 * Remove avatar delete capabilities for very add_role
	 * (this has to be done on OpenSimulator side, or the avatars will be created again)
	 *
	 * Call the function when your plugin/theme is activated.
	 */
	static function remove_avatar_delete_cap() {
		global $wp_roles;

		$remove_caps = array(
			'delete_avatar',
			'delete_avatars',
		);

		$roles = $wp_roles->roles;
		$editable_roles = apply_filters('editable_roles', $roles);

		foreach($roles as $role_slug => $role_array) {
			$role = get_role( $role_slug );

			foreach ( $remove_caps as $cap ) {
				$role->remove_cap( $cap );
			}
		}
	}

	static function remove_avatar_delete_row_actions( $actions, $post ) {
	    if( $post->post_type === 'avatar' ) {
	        unset( $actions['clone'] );
	        unset( $actions['trash'] );
	    }
	    return $actions;
	}

	static function avatar_deletion_warning($post_id) {
	    if( get_post_type($post_id) === 'avatar' ) {
				w4os_transient_admin_notice(__('Avatars can not be deleted from WordPress.', 'w4os'), 'error');
	      // wp_die('The post you were trying to delete is protected.');
	    }
	}

	static function remove_avatar_edit_delete_action() {
	    $current_screen = get_current_screen();

	    // Hides the "Move to Trash" link on the post edit page.
	    if ( 'post' === $current_screen->base &&
	    'avatar' === $current_screen->post_type ) :
	    ?>
	        <style>#delete-action { display: none; }</style>
	    <?php
	    endif;
	}

}

function wpb_custom_post_status(){
	register_post_status('model', array(
		'label'                     => _x( 'Model', 'avatar' ),
		'public'                    => false,
		'post_type'									=> 'avatar',
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => false, // actuelly means "count in section 'all' of status list"
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Model <span class="count">(%s)</span>', 'Models <span class="count">(%s)</span>' ),
	) );
	register_post_status('service', array(
		'label'                     => _x( 'Service Account', 'avatar' ),
		'public'                    => false,
		'post_type'									=> 'avatar',
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => false,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Service Account <span class="count">(%s)</span>', 'Service Accounts <span class="count">(%s)</span>' ),
	) );
}
add_action( 'init', 'wpb_custom_post_status' );

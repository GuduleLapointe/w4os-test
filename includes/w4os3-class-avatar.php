<?php if ( ! defined( 'W4OS_PLUGIN' ) ) die;

add_action( 'init', 'w4os_register_post_type_avatar' );
function w4os_register_post_type_avatar() {
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

add_filter( 'rwmb_meta_boxes', 'w4os3_register_fields_avatar_profile' );
function w4os3_register_fields_avatar_profile( $meta_boxes ) {
  $prefix = 'avatar_';
  $user = wp_get_current_user();
  if($user) {
    if(!empty($user->display_name)) {
      $default_first_name = w4os3_sanitize_avatar_name(preg_replace('/ .*/', '', $user->display_name));
      $default_last_name = w4os3_sanitize_avatar_name(preg_replace('/[^ ]* /', '', $user->display_name));
    } else {
      $default_first_name = w4os3_sanitize_avatar_name($user->first_name);
      $default_last_name = w4os3_sanitize_avatar_name($user->last_name);
    }
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
        'disabled' => (!w4os_is_new_post()),
        'readonly' => (!w4os_is_new_post()),
        'columns'  => 6,
        'std' => $default_first_name,
        'sanitize_callback' => 'w4os3_sanitize_avatar_name',
      ],
      'last_name' => [
        'name'     => __( 'Last Name', 'w4os' ),
        'id'       => $prefix . 'last_name',
        'type'     => 'text',
        'required' => true,
        'disabled' => (!w4os_is_new_post()),
        'readonly' => (!w4os_is_new_post()),
        'columns'  => 6,
        'std' => $default_last_name,
        'sanitize_callback' => 'w4os3_sanitize_avatar_name',
      ],
      [
          'name'       => __( 'Owner', 'w4os' ),
          'id'         => $prefix . 'owner',
          'type'       => 'user',
          'field_type' => 'select_advanced',
          'columns'    => 4,
      ],
      'email' => [
        'name'          => __( 'E-mail', 'w4os' ),
        'id'            => $prefix . 'email',
        'type'          => 'email',
        'std'           => w4os_current_user_email(),
        'admin_columns' => [
          'position'   => 'after author',
          'sort'       => true,
          'searchable' => true,
        ],
        'columns'       => 4,
        'disabled' => (!w4os_is_new_post()),
        'readonly' => (!w4os_is_new_post()),
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
          'name'    => (w4os_is_new_post()) ? __( 'Password', 'w4os' ) : __('Modify password'),
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
  if(w4os_is_new_post()) {
    $meta_boxes['avatar']['fields'] = array_merge( $meta_boxes['avatar']['fields'], [
      [
        'name'    => __( 'Model', 'w4os' ),
        'id'      => $prefix . 'model',
        'type'    => 'image_select',
        'options' => w4os_get_models_options(),
        'disabled'    => true,
        'readonly'    => true,
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
        'name'    => __( 'Profile Picture', 'w4os' ),
        'id'      => $prefix . 'profile_picture',
        'type'    => 'image_select',
        'options' => w4os_get_profile_picture(),
        'disabled'    => true,
        'readonly'    => true,
      ],
    ]);
  }

  return $meta_boxes;
}

<?php if ( ! defined( 'W4OS_PLUGIN' ) ) die;


function w4os_create_user_login($firstname = '', $lastname = '', $email = '') {
	// makes more sense to try name part of the mail first, as it's a login the user is used to.
	if(! empty($email)) {
		$explode=explode('@', $email);
		$user_login = $explode[0];
		if(! get_user_by('user_login', $user_login)) return $user_login;
	}

	// If already taken, use name instead
  $user_login = sanitize_title($firstname) . '.' . sanitize_title($lastname);
  if(! get_user_by('user_login', $user_login)) return $user_login;

	// If name taken, try adding numbers
	// We must stop somewhere, 100 users with same name is quite unlikely
  $base = $user_login;
  $i = 1;
  while($i < 100) {
    $user_login = "$base-$i";
    if(! get_user_by('user_login', $user_login)) return $user_login;
    $i++;
  }
  return false;
}

function w4os_sync_users() {
	if(! W4OS_DB_CONNECTED) return;
	global $wpdb, $w4osdb;
	if(!isset($wpdb)) return false;
	if(!isset($w4osdb)) return false;

  $accounts = W4OS3_Avatar::get_avatars_ids_and_uuids();
	$messages=array();
	$users_created=[];
	$users_updated=[];
	foreach ($accounts as $key => $account) {
		$user = @get_user_by('ID', $account['user_id']);
    // First cleanup NULL_KEY and other empty UUIDs
    if(!isset($account['PrincipalID']) || w4os_empty($account['PrincipalID'])) $account['PrincipalID'] = NULL;
    if(!isset($account['w4os_uuid']) || w4os_empty($account['w4os_uuid'])) $account['w4os_uuid'] = NULL;

    if( isset($account['PrincipalID']) &! w4os_empty($account['PrincipalID']) ) {
			if ( $account['PrincipalID'] == $account['w4os_uuid'] ) {
				// already linked, just resync
				w4os_profile_sync($account['user_id']);
			} else if ( isset($account['user_id']) &! empty($account['user_id']) ) {
				// wrong reference, but an avatar exists for this WP user, replace reference
				$result = w4os_profile_sync($account['user_id'], $account['PrincipalID']);
				if(w4os_profile_sync($account['user_id'], $account['PrincipalID']))
				$users_updated[] = sprintf('<a href=%s>%s %s</a>', get_edit_user_link($newid), $account['FirstName'], $account['LastName']);
				else
				$errors[] = '<p class=error>' .  sprintf(__('Error while updating %s %s (%s) %s', 'w4os'), $account['FirstName'], $account['LastName'], $account['email'], $result) . '</p>';
			} else {
				// No user with this email, create one
				$newid = wp_insert_user(array(
					'user_login' => w4os_create_user_login($account['FirstName'], $account['LastName'], $account['email']),
					'user_pass' => wp_generate_password(),
					'user_email' => $account['email'],
					'first_name' => $account['FirstName'],
					'last_name' => $account['LastName'],
					'role' => 'grid_user',
					'display_name' => trim($account['FirstName'] . ' ' . $account['LastName']),
				));
				if(is_wp_error( $newid )) {
					$errors[] = $newid->get_error_message();
				} else if(w4os_profile_sync($newid, $account['PrincipalID'])) {
					$users_created[] = sprintf('<a href=%s>%s %s</a>', get_edit_user_link($newid), $account['FirstName'], $account['LastName']);
				} else {
					$errors[] = '<p class=error>' .  sprintf(__('Error while updating newly created user %s for %s %s (%s) %s', 'w4os'), $newid, $account['FirstName'], $account['LastName'], $account['email'], $result) . '</p>';
				}
			}
		} else if(isset($account['w4os_uuid']) &! w4os_empty($account['w4os_uuid'])) {
			w4os_profile_dereference($account['user_id']);
			$users_dereferenced[] = sprintf('<a href=%s>%s</a>', get_edit_user_link($account['user_id']), $account['user_id']);
		// } else {
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

function register_w4os_sync_users_async_cron()
{
	if ( false === as_next_scheduled_action( 'w4os_sync_users' ) ) {
		as_schedule_cron_action(time(), '0 * * * *', 'W4OS3_Avatar::sync_avatars');
	}
}
add_action('init','register_w4os_sync_users_async_cron');

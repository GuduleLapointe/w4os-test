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


function register_w4os_sync_users_async_cron()
{
	if ( false === as_next_scheduled_action( 'w4os_sync_users' ) ) {
		as_schedule_cron_action(time(), '0 * * * *', 'W4OS3_Avatar::sync_avatars');
	}
}
add_action('init','register_w4os_sync_users_async_cron');

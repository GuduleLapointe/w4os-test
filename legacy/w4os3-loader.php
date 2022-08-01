<?php if ( ! defined( 'W4OS_PLUGIN' ) ) die;

// require_once __DIR__ . '/w4os3-class-avatar.php';

function w4os3_sanitize_login_uri($value, $field, $old_value, $object_id) {
  if($value != $old_value)
  return $value;
}

function w4os3_sanitize_avatar_name($value, $field = [], $old_value = NULL, $object_id = NULL) {
  return $value;
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

function w4os_current_user_email() {
  $current_user = wp_get_current_user();
  return $current_user->user_email;
}

function w4os_is_new_post($args = null){
    global $pagenow;
    //make sure we are on the backend
    if (!is_admin()) return false;
    return in_array( $pagenow, array( 'post-new.php' ) );
    //   return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
}

function w4os_get_profile_picture() {
  $options = array(
    w4os_get_asset_url(),
  );
  return $options;
}

function w4os_get_models_options() {
  global $w4osdb;
  $results = [];

  $models=$w4osdb->get_results("SELECT FirstName, LastName, profileImage, profileAboutText
    FROM UserAccounts LEFT JOIN userprofile ON PrincipalID = userUUID
    WHERE active = true
    AND (FirstName = '" . get_option('w4os_model_firstname') . "'
    OR LastName = '" . get_option('w4os_model_lastname') . "')
    ORDER BY FirstName, LastName"
  );
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

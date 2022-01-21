<?php namespace OpenSimulator\Helpers;

if ( ! defined( 'W4OS_PLUGIN' ) ) die;

$url = getenv('REDIRECT_URL');

if(get_option('w4os_provide_economy_helpers') == true &! empty(W4OS_GRID_INFO['economy']) ) {
  $economy = parse_url(W4OS_GRID_INFO['economy'])['path'];
  if(preg_match(":^$economy(currency.php|landtool.php):", $url)) {
    $helper = preg_replace(":^$economy:", "", $url);
    require($helper);
    die();
  }
}

if ( get_option('w4os_provide_offline_messages') == true &! empty(W4OS_GRID_INFO['message']) ) {
  $message = parse_url(W4OS_GRID_INFO['message'])['path'];
  if(preg_match(":^$message/(SaveMessage|RetrieveMessages|offlineim)/:", "$url/")) {
    require('offline.php');
  } else if($message == $url) {
    die(); // ignore but don't trigger an error
  }
}

if ( get_option('w4os_provide_search') == true ) {
  if(! empty(get_option('w4os_search_url'))) {
    $search = parse_url(get_option('w4os_search_url'))['path'];
    if(preg_match(":^$search/:", "$url/")) {
      // error_log("search $search");
      require('query.php');
      die();
    }
    $parser = preg_replace(':^//:', '/', dirname($search) . '/parser.php');
    if(preg_match(":^$parser/:", "$url/")) {
      require('parser.php');
      die();
    }

    if(! empty(get_option('w4os_hypevents_url'))) {
      $hypevents = preg_replace(':^//:', '/', dirname($search) . '/eventsparser.php');
      if(preg_match(":^$hypevents:", "$url/")) {
        require('eventsparser.php');
        die();
      }
    }
  }

  if(! empty(get_option('w4os_search_register'))) {
    $register = parse_url(get_option('w4os_search_register'))['path'];
    if(preg_match(":^$register:", "$url/")) {
      error_log("register $register");
      require('register.php');
    }
  }
}

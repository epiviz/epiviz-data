<?php

require_once('base_facebook.php');
require_once('facebook.php');
require_once('../config/OauthSettings.php');
require_once('../config/LoginDataManager.php');

session_start();

$location = 'index.php';
$query = '';
if (array_key_exists('location', $_GET)) {
  $location = $_GET['location'];
}

$settings = new OauthSettings('../config/config.json');
$facebook_settings = $settings->get('facebook');

$facebook = new Facebook(array(
  'appId' => $facebook_settings['id'],
  'secret' => $facebook_settings['secret']));

$user = $facebook->getUser();

if ($user) {
  echo '<h1>'.$user.'</h1>';
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }

  if (!empty($user_profile)) {
    # User info ok? Print it (Here we will be adding the login and registering routines)

    $login_mgr = new LoginDataManager();
    $oauth_uid = $user_profile['id'];
    $oauth_provider = 'facebook';
    $email = $user_profile['email'];
    $full_name = $user_profile['name'];
    $oauth_username = $user_profile['username'];
    $user = $login_mgr->getOrInsertUser($oauth_uid, $oauth_provider, $email, $full_name, $oauth_username);

    $_SESSION['id'] = $user['id'];
    $_SESSION['oauth_id'] = $oauth_uid;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;
    $_SESSION['oauth_provider'] = $oauth_provider;
    $_SESSION['oauth_username'] = $oauth_username;
    // header("Location: ../../" . $location);
    header("Location: " . $location);
  }

} else {
  # There's no active session, let's generate one
  $login_url = $facebook->getLoginUrl(array('scope' => 'email'));
  header("Location: " . $login_url);
}


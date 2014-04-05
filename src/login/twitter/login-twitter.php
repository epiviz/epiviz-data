<?php

require_once('twitteroauth.php');
require_once('../config/OauthSettings.php');
require_once('../config/LoginDataManager.php');
session_start();

$location = 'index.php';
$query = '';
if (array_key_exists('location', $_GET)) {
  $location = $_GET['location'];
}

$settings = new OauthSettings('../config/config.json');
$twitter_settings = $settings->get('twitter');
if (!empty($_GET['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
  // We've got everything we need
  $twitteroauth = new TwitterOAuth($twitter_settings['id'], $twitter_settings['secret'], $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
  $twitteroauth->host = 'https://api.twitter.com/1.1/';
  // Request the access token
  $access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);
  // Save it in a session var
  $_SESSION['access_token'] = $access_token;
  // Get the user's info
  $user_info = $twitteroauth->get('account/verify_credentials');
  if (isset($user_info->error)) {
    // Something's wrong, go back to square 1
    header('Location: login-twitter.php');
  } else {
    $login_mgr = new LoginDataManager();
    $oauth_uid = $user_info->id;
    $oauth_provider = 'twitter';
    $email = null;
    $full_name = $user_info->name;
    $oauth_username = $user_info->screen_name;
    $user = $login_mgr->getOrInsertUser($oauth_uid, $oauth_provider, $email, $full_name, $oauth_username);

    $_SESSION['id'] = $user['id'];
    $_SESSION['oauth_id'] = $oauth_uid;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;
    $_SESSION['oauth_provider'] = $oauth_provider;
    $_SESSION['oauth_username'] = $oauth_username;

    // header("Location: ../../".$location);
    header("Location: ".$location);
  }
  exit;
}

$twitteroauth = new TwitterOAuth($twitter_settings['id'], $twitter_settings['secret']);

// Request authentication tokens, the parameter is the URL we will be redirected to
$request_token = $twitteroauth->getRequestToken('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

// Save them into the session
$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

if ($twitteroauth->http_code == 200) {
  // Generate the URL and redirect
  $url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
  header('Location: ' . $url);
  exit;
} else {
  header('Location: ../../../login.php');
  exit;
}

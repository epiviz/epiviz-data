<?php
# start a new PHP session
session_start();

// we need to know it
// $CURRENT_URL = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

// For now, EpiViz uses only HTTP
$CURRENT_URL = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

// change the following paths if necessary
$config = dirname(__FILE__) . '/src/oauth/hybridauth/config.php';
require_once("src/oauth/hybridauth/Hybrid/Auth.php");

require_once('src/data/DBSettings.php');
require_once("src/oauth/LoginDataManager.php");

try {
  $hybridauth = new Hybrid_Auth($config);
} catch (Exception $e) {
  echo "Ooophs, we got an error: " . $e->getMessage();
}

$provider = "";

// handle logout request
if (isset($_GET["logout"])) {
  $provider = $_GET["logout"];

  $adapter = $hybridauth->getAdapter($provider);

  $adapter->logout();

  header("Location: login.php");

  die();
} elseif (isset($_GET["connected_with"]) && $hybridauth->isConnectedWith($_GET["connected_with"])) {
  // if the user select a provider and authenticate with it
  // then the widget will return this provider name in "connected_with" argument
  $provider = $_GET["connected_with"];

  $adapter = $hybridauth->getAdapter($provider);

  $user_data = $adapter->getUserProfile();

  $adapted_user_data = array(
      'email' => $user_data->email,
      'full_name' => trim($user_data->firstName) . ' ' . trim($user_data->lastName),
      'oauth_username' => null,
      'website_url' => $user_data->webSiteURL,
      'profile_url' => $user_data->profileURL,
      'photo_url' => $user_data->photoURL,
      'display_name' => $user_data->displayName,
      'description' => $user_data->description,
      'first_name' => $user_data->firstName,
      'last_name' => $user_data->lastName,
      'gender' => $user_data->gender,
      'language' => $user_data->language,
      'age' => $user_data->age,
      'birth_day' => $user_data->birthDay,
      'birth_month' => $user_data->birthMonth,
      'birth_year' => $user_data->birthYear,
      'email_verified' => $user_data->emailVerified,
      'phone' => $user_data->phone,
      'address' => $user_data->address,
      'country' => $user_data->country,
      'region' => $user_data->region,
      'city' => $user_data->city,
      'zip' => $user_data->zip
  );

  $login_mgr = new LoginDataManager();
  $user = $login_mgr->getOrInsertUser($user_data->identifier, $provider, $adapted_user_data);
  $_SESSION['user'] = $user;

  $location = 'login.php';
  if (array_key_exists('location', $_GET)) {
    $location = $_GET['location'];
  }
  header("Location: " . $location);

  die();
} // if user connected to the selected provider

// if not, include unauthenticated user view
include "inc_unauthenticated_user.php";

<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 1/30/13
 * Time: 3:36 PM
 */

if (array_key_exists('logout', $_GET)) {
  session_start();

  $location = 'index.php';
  if (array_key_exists('location', $_GET)) {
    $location = $_GET['location'];
  }

  unset($_SESSION['id']);
  unset($_SESSION['username']);
  unset($_SESSION['oauth_provider']);
  session_destroy();
  header('Location: login.php?location=' . urlencode($location));
}

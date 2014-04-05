<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 1/31/13
 * Time: 10:47 AM
 */

session_start();

$location = 'index.php';
$query = '';
if (array_key_exists('location', $_GET)) {
  $location = $_GET['location'];
}

if (array_key_exists('debug', $_GET) && $_GET['debug'] == 'true') {
  $_SESSION['id'] = 0;
  $_SESSION['oauth_id'] = 0;
  $_SESSION['full_name'] = 'Debugger';
  $_SESSION['email'] = null;
  $_SESSION['oauth_provider'] = 'debug';
  $_SESSION['oauth_username'] = 'debugger';
}

if (isset($_SESSION['id'])) {
  // Redirect to main page
  header('Location: ' . $location);
  exit;
}

if (array_key_exists('login', $_GET)) {
  // Redirect to appropriate oauth provider
  $oauth_provider = $_GET['oauth_provider'];
  switch ($oauth_provider) {
    case 'twitter':
      header('Location: src/login/twitter/login-twitter.php?location='.urlencode($location));
      exit;
      break;
    case 'facebook':
      header('Location: src/login/facebook/login-facebook.php?location='.urlencode($location));
      exit;
      break;
    default:
      break;
  }
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="description" content="EpiViz is a scientific information visualization tool for genetic and epigenetic data, used to aid in the exploration and understanding of correlations between various genome features.">
  <title>EpiViz</title>
  <link rel="shortcut icon" href="img/epiviz_icon.png"/>
  <style type="text/css">
    body, html {
      height: 100%;
      margin: 0px;
    }

    #outer {
      text-align: center;
      display: table;
      height: 100%;
      #position: relative;
      overflow: visible;
      width: 100%;
    }

    #inner img,
    #inner a img {
      border: none;
    }

    #middle {
      #position: absolute;
      #top: 50%;
      display: table-cell;
      vertical-align: middle;
      width: 100%;
      text-align: center;
    }

    #inner {
      #position: relative;
      #top: -50%;
      margin-left: auto;
      margin-right: auto;

    }
  </style>
</head>
<body>
<div id="outer">
  <div id="middle">
    <div id="inner">
      <img src="img/epiviz_logo.png" alt="EpiViz" />
      <br/>
      <a href="?login&amp;oauth_provider=twitter&amp;location=<?php echo urlencode($location); ?>"><img src="img/twitterlogin.png" alt="Sign in with Twitter"/></a>
      <br/>
      <a href="?login&amp;oauth_provider=facebook&amp;location=<?php echo urlencode($location); ?>"><img src="img/facebooklogin.png" alt="Sign in with Facebook"/></a>
      <br/>
    </div>
  </div>
</div>
</body>
</html>

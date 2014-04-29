<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
<head>

  <link rel="shortcut icon" href="src/oauth/img/epiviz_icon.png"/>
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
      <img src="img/epiviz_logo.png" width="230" height="52" alt="EpiViz" />
      <br/>
      <!-- CODE REQUIRED BY THE WIDGET -->
      <link media="screen" rel="stylesheet" href="src/oauth/widget/css/colorbox.css"/>
      <script src="src/oauth/widget/js/jquery.min.js"></script>
      <script src="src/oauth/widget/js/jquery.colorbox.js"></script>
      <script>
        $(document).ready(function () {
          $(".widget_link").colorbox({iframe: true, innerWidth: 380, innerHeight: 200});
        });
      </script>
      <!-- /WIDGET -->
      <a href="src/oauth/widget?_ts=<?php echo time(); ?>&return_to=<?php echo urlencode($CURRENT_URL); ?>"
         class='widget_link' title="EpiViz Sign In"><img src="img/epiviz_signin.png" alt="OAuth Sign in" width="157" height="37" /></a>
    </div>
  </div>
</div>
</body>
</html>
<?php
// start a new session (required for Hybridauth)
session_start();

// change the following paths if necessary
$config = dirname(__FILE__) . '/../hybridauth/config.php';
require_once("../hybridauth/Hybrid/Auth.php");

try {
  $hybridauth = new Hybrid_Auth($config);
} // if sometin bad happen
catch (Exception $e) {
  $message = "";

  switch ($e->getCode()) {
    case 0 :
      $message = "Unspecified error.";
      break;
    case 1 :
      $message = "Hybriauth configuration error.";
      break;
    case 2 :
      $message = "Provider not properly configured.";
      break;
    case 3 :
      $message = "Unknown or disabled provider.";
      break;
    case 4 :
      $message = "Missing provider application credentials.";
      break;
    case 5 :
      $message = "Authentication failed. The user has canceled the authentication or the provider refused the connection.";
      break;

    default:
      $message = "Unspecified error!";
  }
  ?>
  <style>
    PRE {
      background: #EFEFEF none repeat scroll 0 0;
      border-left: 4px solid #CCCCCC;
      display: block;
      padding: 15px;
      overflow: auto;
      width: 740px;
    }

    HR {
      width: 100%;
      border: 0;
      border-bottom: 1px solid #ccc;
      padding: 50px;
    }
  </style>
  <table width="100%" border="0">
    <tr>
      <td align="center"><br/><img src="images/icons/alert.png"/></td>
    </tr>
    <tr>
      <td align="center"><br/>

        <h3>Something bad happen!</h3><br/></td>
    </tr>
    <tr>
      <td align="center">&nbsp;<?php echo $message; ?>
        <hr/>
      </td>
    </tr>
    <tr>
      <td>
        <b>Exception</b>: <?php echo $e->getMessage(); ?>
        <pre><?php echo $e->getTraceAsString(); ?></pre>
      </td>
    </tr>
  </table>
  <?php
  // diplay error and RIP
  die();
}

$provider = @ $_GET["provider"];
$return_to = @ $_GET["return_to"];

if (!$return_to) {
  echo "Invalid params!";
}

if (!empty($provider) && $hybridauth->isConnectedWith($provider)) {
  $return_to = $return_to . (strpos($return_to, '?') ? '&' : '?') . "connected_with=" . $provider;
  ?>
  <script language="javascript">
    if (window.opener) {
      try {
        window.opener.parent.$.colorbox.close();
      } catch (err) {
      }
      window.opener.parent.location.href = "<?php echo $return_to; ?>";
    }

    window.self.close();
  </script>
  <?php
  die();
}

if (!empty($provider)) {
  $params = array();

  if ($provider == "OpenID") {
    $params["openid_identifier"] = @ $_REQUEST["openid_identifier"];
  }

  if (isset($_REQUEST["redirect_to_idp"])) {
    $adapter = $hybridauth->authenticate($provider, $params);
  } else {
    // here we display a "loading view" while tryin to redirect the user to the provider
    ?>
    <table width="100%" border="0">
      <tr>
        <td align="center" height="190px" valign="middle"><img src="images/loading.gif"/></td>
      </tr>
      <tr>
        <td align="center"><br/>

          <h3>Loading...</h3><br/></td>
      </tr>
      <tr>
        <td align="center">Contacting <b><?php echo ucfirst(strtolower(strip_tags($provider))); ?></b>. Please wait.
        </td>
      </tr>
    </table>
    <script>
      window.location.href = window.location.href + "&redirect_to_idp=1";
    </script>
  <?php
  }

  die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>HybridAuth Social Sing-on</title>
  <style>
    .idpico {
      cursor: pointer;
      cursor: hand;
    }

    #openidm {
      margin: 7px;
    }
  </style>
  <script src="js/jquery.min.js"></script>
  <script>
    var idp = null;

    $(function () {
      $(".idpico").click(
          function () {
            idp = $(this).data("idp");

            switch (idp) {
              case "google"  :
              case "twitter" :
              case "facebook":
              case "linkedin" :
              case "live":
              case "github":
                start_auth("?provider=" + idp);
                break;
              case "openid" :
                $("#openidm").html("Please enter your OpenID URL");
                $("#openidun").css("width", "350");
                var openidimg = $("#openidimg");
                openidimg.attr("src", "images/icons/" + idp + ".png");
                openidimg.attr("width", "157");
                openidimg.attr("height", "37");
                $("#idps").hide();
                $("#openidid").show();
                break;

              default:
                alert("u no fun");
            }
          }
      );

      $("#openidbtn").click(
          function () {
            var un = $("#openidun").val();
            var oi = un;

            if (!un) {
              alert("Please put your username or blog name on this input field.");
              return;
            }

            switch (idp) {
              case "wordpress" :
                oi = "http://" + un + ".wordpress.com";
                break;
              case "livejournal" :
                oi = "http://" + un + ".livejournal.com";
                break;
              case "blogger" :
                oi = "http://" + un + ".blogspot.com";
                break;
              case "flickr" :
                oi = "http://www.flickr.com/photos/" + un + "/";
                break;
            }

            start_auth("?provider=OpenID&openid_identifier=" + encodeURIComponent(oi));
          }
      );

      $("#backtolist").click(
          function () {
            $("#idps").show();
            $("#openidid").hide();

            return false;
          }
      );
    });

    function start_auth(params) {
      start_url = params + "&return_to=<?php echo urlencode( $return_to ); ?>" + "&_ts=" + (new Date()).getTime();
      window.open(
          start_url,
          "hybridauth_social_sing_on",
          "location=0,status=0,scrollbars=0,width=800,height=500"
      );
    }
  </script>
</head>
<body>
<div id="idps">
  <table style="width: 100%; text-align: center;" border="0">
    <tr>
      <td><img class="idpico" data-idp="google" src="images/icons/google.png" width="157" height="37" title="google"/></td>
      <td><img class="idpico" data-idp="twitter" src="images/icons/twitter.png" width="157" height="37" title="twitter"/></td>
    </tr>
    <tr>
      <td><img class="idpico" data-idp="facebook" src="images/icons/facebook.png" width="157" height="37" title="facebook"/></td>
      <td><img class="idpico" data-idp="openid" src="images/icons/openid.png" width="157" height="37" title="openid"/></td>
    </tr>
    <tr>
      <td><img class="idpico" data-idp="live" src="images/icons/windows.png" width="157" height="37" title="live"/></td>
      <td><img class="idpico" data-idp="github" src="images/icons/github.png" width="157" height="37" title="github"/></td>
    </tr>
    <tr>
      <td><img class="idpico" data-idp="linkedin" src="images/icons/linkedin.png" width="157" height="37" title="linkedin"/></td>
    </tr>
  </table>
</div>
<div id="openidid" style="display:none;">
  <table style="width: 100%; text-align: center;" border="0">
    <tr>
      <td><img id="openidimg" src="images/loading.gif"/></td>
    </tr>
    <tr>
      <td><h3 id="openidm">Please enter your user or blog name</h3></td>
    </tr>
    <tr>
      <td><input type="text" name="openidun" id="openidun" style="padding: 2px; margin:3px;border: 1px solid #999;width:240px;"/></td>
    </tr>
    <tr>
      <td>
        <input type="submit" value="Login" id="openidbtn" style="height:25px;width:70px;"/>
        <br/>
        <small><a href="#" id="backtolist">back</a></small>
      </td>
    </tr>
  </table>
</div>
</body>
</html>

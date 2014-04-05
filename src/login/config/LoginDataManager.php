<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 1/30/13
 * Time: 1:39 PM
 */
require_once('../../data/DBSettings.php');

class LoginDataManager {

  public function getUser($oauth_uid, $oauth_provider) {
    $db = DBSettings::db();

    $query = "SELECT id, email, oauth_uid, oauth_provider, full_name, oauth_username FROM users WHERE oauth_uid = '$oauth_uid' and oauth_provider = '$oauth_provider'";
    $rows = $db->query($query);

    if ($rows->rowCount() == 0) {
      return null;
    }

    $r = $rows->fetch(PDO::FETCH_NUM);
    $result = array(
      'id' => $r[0],
      'email' => $r[1],
      'oauth_uid' => $r[2],
      'oauth_provider' => $r[3],
      'full_name' => $r[4],
      'oauth_username' => $r[5]
    );

    return $result;
  }

  public function getOrInsertUser($oauth_uid, $oauth_provider, $email, $full_name, $oauth_username) {

    $db = DBSettings::db();

    $result = $this->getUser($oauth_uid, $oauth_provider);

    if ($result != null) {
      return $result;
    }

    if ($email == null) {
      $email = 'NULL';
    }
    $insert = "INSERT INTO users (email, oauth_uid, oauth_provider, full_name, oauth_username) VALUES ('$email', '$oauth_uid', '$oauth_provider', '$full_name', '$oauth_username')";
    $db->exec($insert);

    return $this->getUser($oauth_uid, $oauth_provider);
  }
}

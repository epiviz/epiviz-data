<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 1/30/13
 * Time: 1:39 PM
 */

class LoginDataManager {

  public function getUser($oauth_uid, $oauth_provider) {
    $db = DBSettings::db();

    $stmt = $db->prepare("SELECT `id`, `email`, `oauth_uid`, `oauth_provider`, `full_name`, `oauth_username`, `website_url`," .
      "`profile_url`, `photo_url`, `display_name`, `description`, `first_name`, `last_name`, `gender`, `language`, `age`," .
      "`birth_day`, `birth_month`, `birth_year`, `email_verified`, `phone`, `address`, `country`, `region`, `city`, `zip`" .
      "FROM `epiviz`.`users` WHERE `oauth_uid` = :oauthuid and `oauth_provider` = :oauthprovider;");
    $stmt->execute(array('oauthuid' => $oauth_uid, 'oauthprovider' => $oauth_provider));

    if ($stmt->rowCount() == 0) {
      return null;
    }

    $r = $stmt->fetch(PDO::FETCH_NUM);
    $result = array(
      'id' => $r[0],
      'email' => $r[1],
      'oauth_uid' => $r[2],
      'oauth_provider' => $r[3],
      'full_name' => $r[4],
      'oauth_username' => $r[5],
      'website_url' => $r[6],
      'profile_url' => $r[7],
      'photo_url' => $r[8],
      'display_name' => $r[9],
      'description' => $r[10],
      'first_name' => $r[11],
      'last_name' => $r[12],
      'gender' => $r[13],
      'language' => $r[14],
      'age' => $r[15],
      'birth_day' => $r[16],
      'birth_month' => $r[17],
      'birth_year' => $r[18],
      'email_verified' => $r[19],
      'phone' => $r[20],
      'address' => $r[21],
      'country' => $r[22],
      'region' => $r[23],
      'city' => $r[24],
      'zip' => $r[25]
    );

    return $result;
  }

  public function getOrInsertUser($oauth_uid, $oauth_provider, $user_data) {

    $db = DBSettings::db();

    $result = $this->getUser($oauth_uid, $oauth_provider);

    if ($result != null) {
      return $result;
    }

    $joined_keys = '(`oauth_uid`, `oauth_provider`, `' . implode('`, `', array_keys($user_data)) . '`)';
    $joined_values = "('$oauth_uid', '$oauth_provider'";
    foreach ($user_data as $v) {
      $joined_values .= ', ';
      $joined_values .= ($v === null) ? 'NULL' : "'$v'";
    }
    $joined_values .= ')';

    $insert = "INSERT INTO users $joined_keys VALUES $joined_values";

    print_r($insert);

    $db->exec($insert);

    return $this->getUser($oauth_uid, $oauth_provider);
  }
}

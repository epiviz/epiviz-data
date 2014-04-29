<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 1/29/13
 * Time: 4:13 PM
 */
class DBSettings {
  const dbname = 'epiviz';
  const server = 'localhost';
  const username = 'root';
  const password = 'tuculeana';

  private static $db = null;

  public static function db() {
    if (DBSettings::$db != null) {
      return DBSettings::$db;
    }

    $server = DBSettings::server;
    $username = DBSettings::username;
    $password = DBSettings::password;
    $dbname = DBSettings::dbname;

    // Open a persistent database connection, for performance improvement
    DBSettings::$db = new PDO('mysql:host='.$server.';dbname='.$dbname.';charset=utf8', $username, $password, array(PDO::ATTR_PERSISTENT => true));

    return DBSettings::$db;
  }
}

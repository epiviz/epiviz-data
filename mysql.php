<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 5/6/14
 * Time: 1:23 PM
 */

require_once('src/data/util.php');

class MySQLProxy {
  private static $connections = array();

  public static function getConnection($server, $dbname, $username, $password) {
    if (!array_key_exists($server, MySQLProxy::$connections)) {
      $server_dbs = array();
      MySQLProxy::$connections[$server] = &$server_dbs;
    } else {
      $server_dbs = &MySQLProxy::$connections[$server];
    }

    if (!array_key_exists($dbname, $server_dbs)) {
      $server_dbs[$dbname] = new MySQLProxy(
          new PDO(
              'mysql:host='.$server.';dbname='.$dbname.';charset=utf8',
              $username, $password, array(PDO::ATTR_PERSISTENT => true)));
    }

    return $server_dbs[$dbname];
  }

  private $db;

  public function __construct($db) {
    $this->db = $db;
  }

  public function query($query) {
    $statement = $this->db->prepare($query);
    if ($statement->execute()) {
      if ($statement->rowCount() == 0) {
        return array('results' => array());
      }

      $first_row = $statement->fetch(PDO::FETCH_ASSOC);
      return array('columns' => array_keys($first_row), 'results' => array_merge(array(array_values($first_row)), $statement->fetchAll(PDO::FETCH_NUM)));
    } else {
      return array('error' => $statement->errorInfo());
    }
  }
}

$server = idx($_REQUEST, 'server');
$dbname = idx($_REQUEST, 'db');
$user = idx($_REQUEST, 'u');
$pwd = idx($_REQUEST, 'p');
$query = idx($_REQUEST, 'q');

$proxy = MySQLProxy::getConnection($server, $dbname, $user, $pwd);
header('Access-Control-Allow-Origin: *');
echo json_encode($proxy->query($query));

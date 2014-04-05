<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 2/6/13
 * Time: 2:07 PM
 */

require_once('DBSettings.php');
require_once('DBBasics.php');

class Workspace {

  private $db;

  public function __construct() {
    $this->db = DBSettings::db();
  }

  public function save($user_id, $id, $name, $content) {
    if ($id) {
      $update =
         "UPDATE workspaces_v2 "
        ."SET name='$name', content='$content' "
        ."WHERE user_id=$user_id AND id='$id';";

      $this->db->exec($update);
      return $id;
    } else {
      $id = DBBasics::generateSmallGUID();
      $insert =
        "INSERT INTO workspaces_v2 (id, id_v1, user_id, name, content) "
       ."VALUES ('$id', NULL, $user_id, '$name', '$content'); ";

      $this->db->exec($insert);

      return $id;
    }
  }

  public function getWorkspaces($user_id, $q = '', $requestWorkspaceId) {
    $matches = preg_split('/[^\w^\s]+/', $q);

    $q = join('', $matches);
    $q = str_replace('_', '\\_', $q);

    $base_query =
      "SELECT id, id_v1, user_id, name, content "
        ."FROM workspaces_v2 "
        ."WHERE ";

    $query = $base_query . "(user_id = $user_id AND (name LIKE '%$q%' OR id LIKE '%$q%')) ";
    if ($requestWorkspaceId) {
      $query .= "OR (id = '$requestWorkspaceId') OR (id_v1 = '$requestWorkspaceId') ";
    }
    $query .= "ORDER BY name;";

    $rows = $this->db->query($query);

    $result = array();

    if ($rows->rowCount() == 0) {
      return $result;
    }

    while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
      if (0 + $r[2] == 0 + $user_id) {
        $result[] = array('id' => $r[0], 'id_v1' => $r[1], 'name' => $r[3], 'content' => $r[4]);
      } else {
        $result[] = array('id' => null, 'id_v1' => null, 'name' => $r[3], 'content' => $r[4]);
      }
    }
    return $result;
  }

  public function deleteWorkspace($user_id, $workspace_id) {
    $query =
      "DELETE FROM workspaces_v2 WHERE id='$workspace_id' AND user_id='$user_id';";

    $success = !($this->db->exec($query) === false);

    return array('success' => $success);
  }
}

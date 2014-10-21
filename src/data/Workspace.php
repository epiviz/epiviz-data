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

  private $updateWorkspaceStmt;
  private $insertWorkspaceStmt;
  private $getWorkspacesStmt;
  private $getWorkspaceByIdStmt;
  private $deleteWorkspaceStmt;

  public function __construct() {
    $this->db = DBSettings::db();

    $this->updateWorkspaceStmt = $this->db->prepare(
      "UPDATE workspaces_v2 "
      ."SET name= :name, content= :content "
      ."WHERE user_id= :user AND id= :id; ");

    $this->insertWorkspaceStmt = $this->db->prepare(
      "INSERT INTO workspaces_v2 (id, id_v1, user_id, name, content) "
      ."VALUES (:id, NULL, :user, :name, :content); ");

    $this->getWorkspacesStmt = $this->db->prepare(
      "SELECT id, id_v1, user_id, name, content "
      ."FROM workspaces_v2 "
      ."WHERE (user_id = :user AND (name LIKE :qname OR id LIKE :qid)) "
      ."ORDER BY name; ");

    $this->getWorkspaceByIdStmt = $this->db->prepare(
      "SELECT id, id_v1, user_id, name, content "
      ."FROM workspaces_v2 "
      ."WHERE (user_id = :user AND (name LIKE :qname OR id LIKE :qid)) OR (id = :id OR id_v1 = :idv1); ");

    $this->deleteWorkspaceStmt = $this->db->prepare(
      "DELETE FROM workspaces_v2 WHERE id= :id AND user_id= :user; ");
  }

  public function save($user_id, $id, $name, $content) {
    $stmt = null;
    if ($id) {
      $stmt = $this->updateWorkspaceStmt;
    } else {
      $id = DBBasics::generateSmallGUID();
      $stmt = $this->insertWorkspaceStmt;
    }
    $stmt->execute(array(
      'name' => $name,
      'content' => $content,
      'user' => $user_id,
      'id' => $id
    ));
    return $id;
  }

  public function getWorkspaces($user_id, $q = '', $requestWorkspaceId) {
    $matches = preg_split('/[^\w^\s]+/', $q);

    $q = join('', $matches);
    $q = str_replace('_', '\\_', $q);

    $params = array(
      'user' => $user_id,
      'qname' => '%' . $q . '%',
      'qid' => '%' . $q . '%');
    $stmt = $this->getWorkspacesStmt;
    if ($requestWorkspaceId) {
      $params['id'] = $requestWorkspaceId;
      $params['idv1'] = $requestWorkspaceId;
      $stmt = $this->getWorkspaceByIdStmt;
    }

    $stmt->execute($params);

    $result = array();

    if ($stmt->rowCount() == 0) {
      return $result;
    }

    while (($r = ($stmt->fetch(PDO::FETCH_NUM))) != false) {
      if (0 + $r[2] == 0 + $user_id) {
        $result[] = array('id' => $r[0], 'id_v1' => $r[1], 'name' => $r[3], 'content' => $r[4]);
      } else {
        $result[] = array('id' => null, 'id_v1' => null, 'name' => $r[3], 'content' => $r[4]);
      }
    }
    return $result;
  }

  public function deleteWorkspace($user_id, $workspace_id) {
    $stmt = $this->deleteWorkspaceStmt;
    $success = !($stmt->execute(array('id' => $workspace_id, 'user' =>  $user_id)) === false);

    return array('success' => $success);
  }
}

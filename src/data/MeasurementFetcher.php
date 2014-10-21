<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 11/3/12
 * Time: 1:51 PM
 */

require_once('util.php');
require_once('DBSettings.php');

class MeasurementFetcher {
  private $tables;
  private $tablesColumns = array();
  private $queryFormat;

  public function __construct() {
    $this->queryFormat =
      'SELECT %1$s FROM %2$s WHERE id BETWEEN '
      .'(SELECT MIN(id) FROM %2$s WHERE chr = :sequence2 AND start < :end2 AND end >= :start2) AND '
      .'(SELECT MAX(id) FROM %2$s WHERE chr = :sequence3 AND start < :end3 AND end >= :start3) ORDER BY id ASC; ';

    $this->db = DBSettings::db();
  }

  private function getTables() {
    if (!$this->tables) {
      $rows = $this->db->query('SHOW TABLES;');
      $tables = array();
      while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
        $tables[] = $r[0];
      }
      $this->tables = array_flip($tables);
    }
    return $this->tables;
  }

  private function getTableColumns($table_name) {
    if (!array_key_exists($table_name, $this->tablesColumns)) {
      $rows = $this->db->query("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_NAME`='$table_name';");
      $columns = array();
      while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
        $columns[] = $r[0];
      }
      $this->tablesColumns[$table_name] = array_flip($columns);
    }
    return $this->tablesColumns[$table_name];
  }

  private function queryDb($query) {
    $rows = $this->db->query($query);

    return $rows;
  }

  public function getRows($datasource, $chr, $start, $end, $metadata_cols, $get_id = true, $get_end = true, $get_strand = true, $use_offset = false) {

    $fields = 'id, start';
    $metadata_cols_index = 2;
    $strand_col_index = 2;
    if ($get_end) { $fields .= ', end'; ++$metadata_cols_index; ++$strand_col_index; }
    if ($get_strand) { $fields .= ', strand'; ++$metadata_cols_index; }

    $params = array(
      'sequence2' => $chr,
      'sequence3' => $chr,
      'start2' => $start,
      'start3' => $start,
      'end2' => $end,
      'end3' => $end);

    $values = array(
      'id' => $get_id ? array() : null,
      'start' => array(),
      'end' => $get_end ? array() : null,
      'strand' => $get_strand ? array() : null
    );

    // Compress the sent data so that the message is sent a faster over the internet
    $min_id = null;
    $last_start = null;
    $last_end = null;

    // Make sure that the given data source is in the list of tables (to prevent SQL injection)
    $tables = $this->getTables();
    if (array_key_exists($datasource, $tables)) {
      if (is_array($metadata_cols)) {
        $safe_metadata_cols = array();
        $columns = $this->getTableColumns($datasource);
        foreach ($metadata_cols as $col) {
          if (array_key_exists($col, $columns)) {
            $fields .= ', ' . $col;
            $safe_metadata_cols[] = $col;
          }
        }
        $metadata_cols = $safe_metadata_cols;

        $values['metadata'] = array();
        foreach ($metadata_cols as $col) {
          $values['metadata'][$col] = array();
        }
      }

      $stmt = $this->db->prepare(sprintf($this->queryFormat, $fields, $datasource));
      $stmt->execute($params);
    }

    while (!empty($stmt) && ($r = ($stmt->fetch(PDO::FETCH_NUM))) != false) {
      if ($min_id === null) { $min_id = 0 + $r[0]; }
      if ($get_id) { $values['id'][] = 0 + $r[0]; }

      $start = 0 + $r[1];
      $end = $get_end ? 0 + $r[2] : null;

      if ($use_offset) {
        if ($last_start !== null) {
          $start -= $last_start;
          if ($get_end) { $end -= $last_end; }
        }

        $last_start = 0 + $r[1];
        if ($get_end) { $last_end = 0 + $r[2]; }
      }

      $values['start'][] = $start;
      if ($get_end) { $values['end'][] = $end; }
      if ($get_strand) { $values['strand'][] = $r[$strand_col_index]; }
      if (is_array($metadata_cols)) {
        $col_index = $metadata_cols_index;
        foreach ($metadata_cols as $col) {
          $values['metadata'][$col][] = $r[$col_index++];
        }
      }
    }
    $data = array(
      'values' => $values,
      'globalStartIndex' => $min_id,
      'useOffset' => $use_offset
    );
    return $data;
  }

  public function getValues($datasource, $measurement, $chr, $start, $end) {
    $data = array(
      'globalStartIndex' => null,
      'values' => array()
    );

    $tables = $this->getTables();
    if (!array_key_exists($datasource, $tables)) {
      return $data;
    }

    $columns = $this->getTableColumns($datasource);
    if (!array_key_exists($measurement, $columns)) {
      return $data;
    }

    $stmt = $this->db->prepare(sprintf($this->queryFormat, 'id, ' . $measurement, $datasource));
    $stmt->execute(array(
        'sequence2' => $chr,
        'sequence3' => $chr,
        'start2' => $start,
        'start3' => $start,
        'end2' => $end,
        'end3' => $end));

    $min_id = null;
    while (!empty($stmt) && ($r = ($stmt->fetch(PDO::FETCH_NUM))) != false) {
      if ($min_id === null) { $min_id = 0 + $r[0]; }
      $data['values'][] = round(0 + $r[1], 3);
    }
    $data['globalStartIndex'] = $min_id;
    return $data;
  }
}

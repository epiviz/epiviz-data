<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 11/3/12
 * Time: 1:51 PM
 */

require_once('util.php');
require_once('DBSettings.php');

class MeasurementFetcher {
  private $queryFormat = null;

  public function __construct() {
    $this->queryFormat =
      'SELECT %1$s FROM %2$s WHERE id BETWEEN '
     .'(SELECT MIN(id) FROM %2$s WHERE chr = \'%3$s\' AND start<%5$s AND end>=%4$s) AND '
     .'(SELECT MAX(id) FROM %2$s WHERE chr = \'%3$s\' AND start<%5$s AND end>=%4$s) ORDER BY id ASC ';

    $this->db = DBSettings::db();
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

    if (is_array($metadata_cols)) {
      foreach ($metadata_cols as $col) {
        $fields .= ', ' . $col;
      }
    }

    $query = sprintf($this->queryFormat, $fields, $datasource, $chr, $start, $end);
    $rows = $this->queryDb($query);

    $values = array(
      'id' => $get_id ? array() : null,
      'start' => array(),
      'end' => $get_end ? array() : null,
      'strand' => $get_strand ? array() : null
    );

    if (is_array($metadata_cols)) {
      $values['metadata'] = array();
      foreach ($metadata_cols as $col) {
        $values['metadata'][$col] = array();
      }
    }

    // Compress the sent data so that the message is sent a faster over the internet
    $min_id = null;
    $last_start = null;
    $last_end = null;
    while (!empty($rows) && ($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
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
    $query = sprintf($this->queryFormat, 'id, ' . $measurement, $datasource, $chr, $start, $end);
    $rows = $this->queryDb($query);

    $data = array(
      'values' => array()
    );

    $min_id = null;
    while (!empty($rows) && ($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
      if ($min_id === null) { $min_id = 0 + $r[0]; }
      $data['values'][] = round(0 + $r[1], 3);
    }
    $data['globalStartIndex'] = $min_id;
    return $data;
  }
}

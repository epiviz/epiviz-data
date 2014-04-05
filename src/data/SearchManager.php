<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 4/18/13
 * Time: 2:10 PM
 */

require_once('util.php');

require_once('DBSettings.php');
require_once('MeasurementFetcher.php');
require_once('MeasurementIndex.php');

class SearchManager {

  public function __construct() {
    $this->db = DBSettings::db();
  }

  private function queryDb($query) {
    $rows = $this->db->query($query);

    return $rows;
  }

  private function doSearch($q, $max_results) {
    $pattern = '/[^\w\_\-]/';
    $q = preg_replace($pattern, '', $q);
    $q = str_replace('_', '\\_', $q);

    // Search through probes
    $sql =
      'SELECT probe, gene, chr, start, end, 0 AS tmp_order FROM probes '
     .'WHERE probe LIKE \''.$q.'%\' '
     .'UNION ALL '
     .'SELECT probe, gene, chr, start, end, 1 AS tmp_order FROM probes '
     .'WHERE probe LIKE \'%'.$q.'%\' AND probe NOT LIKE \''.$q.'%\' '
     .'ORDER BY tmp_order, probe, chr, start, end LIMIT '.((int)($max_results / 2)).';';

    $rows = $this->queryDb($sql);

    $result = array();
    while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
      list($probe, $gene, $chr, $start, $end) = array($r[0], $r[1], $r[2], 0 + $r[3], 0 + $r[4]);

      $result[] = array('probe' => $probe, 'gene' => $gene, 'seqName' => $chr, 'start' => 0 + $start, 'end' => 0 + $end);
    }

    // Search through genes

    $max_results -= count($result);

    if ($max_results == 0) {
      return $result;
    }

    $sql = $sql =
      'SELECT gene, chr, start, end, 0 AS tmp_order FROM genes '
     .'WHERE gene LIKE \''.$q.'%\' '
     .'UNION ALL '
     .'SELECT gene, chr, start, end, 1 AS tmp_order FROM genes '
     .'WHERE gene LIKE \'%'.$q.'%\' AND gene NOT LIKE \''.$q.'%\' '
     .'ORDER BY tmp_order, gene, chr, start, end LIMIT '.$max_results.';';

    $rows = $this->queryDb($sql);

    while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
      list($gene, $chr, $start, $end) = array($r[0], $r[1], 0 + $r[2], 0 + $r[3]);

      $result[] = array('gene' => $gene, 'seqName' => $chr, 'start' => 0 + $start, 'end' => 0 + $end);
    }

    return $result;
  }

  public function search($args) {
    $query = idx($args, 'q', null);
    $max_results = idx($args, 'maxResults', 10);

    return $this->doSearch($query, $max_results);
  }
}

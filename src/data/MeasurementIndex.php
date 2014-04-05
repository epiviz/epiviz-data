<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 1/29/13
 * Time: 1:40 PM
 */

require_once('DBSettings.php');

class MeasurementIndex {

  private $geneExpressionIndex = null;
  private $bpIndex = null;
  private $blockIndex = null;
  private $barcodeIndex = null;
  private $geneIndex = null;

  public function __construct() {
  }

  public function getGeneExpressionIndex() {
    if ($this->geneExpressionIndex === null) {
      $this->geneExpressionIndex = $this->fetchGeneIndex();
    }
    return $this->geneExpressionIndex;
  }
  public function getBpIndex() {
    if ($this->bpIndex === null) {
      $this->bpIndex = $this->fetchBpIndex();
    }
    return $this->bpIndex;
  }
  public function getBlockIndex() {
    if ($this->blockIndex === null) {
      $this->blockIndex = $this->fetchBlockIndex();
    }
    return $this->blockIndex;
  }
  public function getBarcodeIndex() {
    if ($this->barcodeIndex === null) {
      $this->barcodeIndex = $this->fetchBarcodeIndex();
    }
    return $this->barcodeIndex;
  }
  public function getGeneIndex() {
    if ($this->geneIndex === null) {
      $this->geneIndex = array(
        'id' => array('genes'),
        'name' => array('Genes'),
        'type' => array('range'),
        'datasourceId' => array('genes'),
        'datasourceGroup' => array('genes'),
        'defaultChartType' => array('Genes Track'),
        'annotation' => array(null),
        'minValue' => array(null),
        'maxValue' => array(null),
        'metadata' => array(array('gene', 'entrez', 'exon_starts', 'exon_ends'))
      );
    }
    return $this->geneIndex;
  }

  private function fetchGeneIndex() {
    $result = array(
      'id' => array(),
      'name' => array(),
      'type' => array(),
      'datasourceId' => array(),
      'datasourceGroup' => array(),
      'defaultChartType' => array(),
      'annotation' => array(),
      'minValue' => array(),
      'maxValue' => array(),
      'metadata' => array()
    );

    $db = DBSettings::db();
    $queryGenes = 'SELECT measurement_id, measurement_name, location, column_name, min_value, max_value FROM gene_data_index;';
    $rows = $db->query($queryGenes);

    while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
      $result['id'][] = $r[3];
      $result['name'][] = $r[1];
      $result['type'][] = 'feature';
      $result['datasourceId'][] = $r[2];
      $result['datasourceGroup'][] = 'affymetrix_probeset';
      $result['defaultChartType'][] = 'Scatter Plot';
      $result['annotation'][] = null;
      $result['minValue'][] = 0 + $r[4];
      $result['maxValue'][] = 0 + $r[5];
      $result['metadata'][] = array('probe');
    }

    return $result;
  }

  private function fetchBlockIndex() {
    $result = array(
      'id' => array(),
      'name' => array(),
      'type' => array(),
      'datasourceId' => array(),
      'datasourceGroup' => array(),
      'defaultChartType' => array(),
      'annotation' => array(),
      'minValue' => array(),
      'maxValue' => array(),
      'metadata' => array()
    );

    $db = DBSettings::db();
    $queryBlocks = 'SELECT measurement_id, measurement_name, location FROM block_data_index;';
    $rows = $db->query($queryBlocks);

    while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
      $result['id'][] = $r[2];
      $result['name'][] = $r[1];
      $result['type'][] = 'range';
      $result['datasourceId'][] = $r[2];
      $result['datasourceGroup'][] = $r[2];
      $result['defaultChartType'][] = 'Blocks Track';
      $result['annotation'][] = null;
      $result['minValue'][] = null;
      $result['maxValue'][] = null;
      $result['metadata'][] = null;
    }

    return $result;
  }

  private function fetchBpIndex() {
    $result = array(
      'id' => array(),
      'name' => array(),
      'type' => array(),
      'datasourceId' => array(),
      'datasourceGroup' => array(),
      'defaultChartType' => array(),
      'annotation' => array(),
      'minValue' => array(),
      'maxValue' => array(),
      'metadata' => array()
    );

    $db = DBSettings::db();
    $queryBp = 'SELECT measurement_id, measurement_name, location, column_name, min_value, max_value, max_window_size FROM bp_data_index;';
    $rows = $db->query($queryBp);

    while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
      $result['id'][] = $r[3];
      $result['name'][] = $r[1];
      $result['type'][] = 'feature';
      $result['datasourceId'][] = $r[2];
      $result['datasourceGroup'][] = $r[2];
      $result['defaultChartType'][] = 'Line Track';
      $result['annotation'][] = null;
      $result['minValue'][] = 0 + $r[4];
      $result['maxValue'][] = 0 + $r[5];
      $result['metadata'][] = null;
    }

    return $result;
  }

  private function fetchBarcodeIndex() {
    $result = array(
      'id' => array(),
      'name' => array(),
      'type' => array(),
      'datasourceId' => array(),
      'datasourceGroup' => array(),
      'defaultChartType' => array(),
      'annotation' => array(),
      'minValue' => array(),
      'maxValue' => array(),
      'metadata' => array()
    );
    $db = DBSettings::db();
    $tissues = array();

    $query = 'SELECT tissue, subtype, dbid, `table` FROM gene_expression_barcode_sample_info';
    $rows = $db->query($query);
    while (($r = ($rows->fetch(PDO::FETCH_NUM))) != false) {
      if (!array_key_exists($r[0], $tissues)) {
        $tissues[$r[0]] = array();

        // Add tissue to list of measurements
        $result['id'][] = $r[0];
        $result['name'][] = $r[0];
        $result['type'][] = 'feature';
        $result['datasourceId'][] = 'gene_expression_barcode_tissue';
        $result['datasourceGroup'][] = 'gexp_barcode';
        $result['defaultChartType'][] = 'Heatmap';
        $result['annotation'][] = array('tissue' => $r[0]);
        $result['minValue'][] = 0;
        $result['maxValue'][] = 1;
        $result['metadata'][] = array('probe', 'gene');
      }

      if (!array_key_exists($r[1], $tissues[$r[0]])) {
        $tissues[$r[0]][$r[1]] = true;

        // Add subtype to list of measurements
        $result['id'][] = $r[0] . '___' . $r[1];
        $result['name'][] = $r[0] . ' ' . $r[1];
        $result['type'][] = 'feature';
        $result['datasourceId'][] = 'gene_expression_barcode_subtype';
        $result['datasourceGroup'][] = 'gexp_barcode';
        $result['defaultChartType'][] = 'Heatmap';
        $result['annotation'][] = array('tissue' => $r[0], 'subtype' => $r[1]);
        $result['minValue'][] = 0;
        $result['maxValue'][] = 1;
        $result['metadata'][] = array('probe', 'gene');
      }

      // Add sample to list of measurements
      $result['id'][] = $r[2];
      $result['name'][] = $r[2];
      $result['type'][] = 'feature';
      $result['datasourceId'][] = $r[3];
      $result['datasourceGroup'][] = 'gexp_barcode';
      $result['defaultChartType'][] = 'Heatmap';
      $result['annotation'][] = array('tissue' => $r[0], 'subtype' => $r[1]);
      $result['minValue'][] = 0;
      $result['maxValue'][] = 1;
      $result['metadata'][] = array('probe', 'gene');
    }

    return $result;
  }
}

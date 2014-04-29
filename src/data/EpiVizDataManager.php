<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 11/19/13
 * Time: 11:15 AM
 */

require_once('util.php');

require_once('DBSettings.php');
require_once('MeasurementFetcher.php');
require_once('MeasurementIndex.php');
require_once('SearchManager.php');

require_once('Workspace.php');

class EpiVizDataManager {
  private $measurementIndex = null;
  private $dataFetcher = null;

  private $workspace = null;

  public function __construct() {
    $this->measurementIndex = new MeasurementIndex();
    $this->dataFetcher = new MeasurementFetcher();
    $this->workspace = new Workspace();
    $this->searcher = new SearchManager();
  }

  public function execute($args) {
    $action = $args['action'];
    $chr = idx($args, 'seqName');
    $start = idx($args, 'start');
    $end = idx($args, 'end');
    $metadata_cols = idx($args, 'metadata');

    $request_id = idx($args, 'requestId', 0);

    session_start();
    $user = idx($_SESSION, 'user', null);

    switch ($action) {
      case 'search':
        $results = $this->searcher->search($args);
        return array(
          'requestId' => 0 + $request_id,
          'type' => 'response',
          'data' => $results
        );
      case 'getMeasurements':

        $result = array(
          'id' => array(),
          'name' => array(),
          'type' => array(),
          'datasourceId' => array(),
          'datasourceGroup' => array(),
          // omit dataprovider: it's established at JS level
          // omit formula in here: only use it in workspaces
          'defaultChartType' => array(),
          'annotation' => array(),
          'minValue' => array(),
          'maxValue' => array(),
          'metadata' => array()
        );

        $geneExpressionMeasurements = $this->measurementIndex->getGeneExpressionIndex();
        foreach ($geneExpressionMeasurements as $key => $arr) {
          $result[$key] = array_merge($result[$key], $arr);
        }

        $bpMeasurements = $this->measurementIndex->getBpIndex();
        foreach ($bpMeasurements as $key => $arr) {
          $result[$key] = array_merge($result[$key], $arr);
        }

        $blockMeasurements = $this->measurementIndex->getBlockIndex();
        foreach ($blockMeasurements as $key => $arr) {
          $result[$key] = array_merge($result[$key], $arr);
        }

        $geneMeasurements = $this->measurementIndex->getGeneIndex();
        foreach ($geneMeasurements as $key => $arr) {
          $result[$key] = array_merge($result[$key], $arr);
        }

        $barcodeMeasurements = $this->measurementIndex->getBarcodeIndex();
        foreach ($barcodeMeasurements as $key => $arr) {
          $result[$key] = array_merge($result[$key], $arr);
        }

        return array(
          'requestId' => 0 + $request_id,
          'type' => 'response',
          'data' => $result
        );

      case 'getRows':
        $datasourceId = $args['datasource'];

        // Decide which columns to fetch from the database

        $get_id = false;
        $get_end = true;
        $get_strand = true;
        $use_offset = true;

        if ($datasourceId != 'genes') {
          $get_strand = false;
          $bpMeasurements = $this->measurementIndex->getBpIndex();
          foreach ($bpMeasurements['datasourceId'] as $id) {
            if ($datasourceId == $id) {
              $get_end = false;
            }
          }
        }

        $result = $this->dataFetcher->getRows($datasourceId, $chr, $start, $end, $metadata_cols, $get_id, $get_end, $get_strand, $use_offset);

        return array(
          'requestId' => 0 + $request_id,
          'type' => 'response',
          'data' => $result
        );

      case 'getValues':
        $datasourceId = $args['datasource'];
        $measurement = $args['measurement'];
        $result = $this->dataFetcher->getValues($datasourceId, $measurement, $chr, $start, $end);

        return array(
          'requestId' => 0 + $request_id,
          'type' => 'response',
          'data' => $result
        );

      case 'saveWorkspace':
        if ($user === null) { return array('requestId'=> 0+$request_id, 'type'=>'response', 'data'=>null); }

        $id = $_REQUEST['id'];
        $name = $_REQUEST['name'];
        $content = $_REQUEST['content'];
        $ws_id =  $this->workspace->save($user['id'], $id, $name, $content);
        return array(
          'requestId' => 0 + $request_id,
          'type' => 'response',
          'data' => $ws_id
        );
      case 'deleteWorkspace':
        if ($user === null) { return array('requestId'=> 0+$request_id, 'type'=>'response', 'data'=>array('success'=>false)); }
        $workspace_id = $_REQUEST['id'];
        $result = $this->workspace->deleteWorkspace($user['id'], $workspace_id);

        return array(
          'requestId' => 0 + $request_id,
          'type' => 'response',
          'data' => $result
        );
      case 'getWorkspaces':
        $user_id = ($user === null) ? -1 : $user['id'];
        $q = idx($args, 'q', '');
        $requestWorkspace = idx($args, 'ws', null);
        $workspaces = $this->workspace->getWorkspaces($user_id, $q, $requestWorkspace);

        return array(
          'requestId' => 0 + $request_id,
          'type' => 'response',
          'data' => $workspaces
        );

      case 'getSeqInfos':
        // Source: http://www.ncbi.nlm.nih.gov/projects/genome/assembly/grc/human/data/index.shtml
        $result = array(
          array('chr1', 1, 248956422),
          array('chr2', 1, 242193529),
          array('chr3', 1, 198295559),
          array('chr4', 1, 190214555),
          array('chr5', 1, 181538259),
          array('chr6', 1, 170805979),
          array('chr7', 1, 159345973),
          array('chr8', 1, 145138636),
          array('chr9', 1, 138394717),
          array('chr10', 1, 133797422),
          array('chr11', 1, 135086622),
          array('chr12', 1, 133275309),
          array('chr13', 1, 114364328),
          array('chr14', 1, 107043718),
          array('chr15', 1, 101991189),
          array('chr16', 1, 90338345),
          array('chr17', 1, 83257441),
          array('chr18', 1, 80373285),
          array('chr19', 1, 58617616),
          array('chr20', 1, 64444167),
          array('chr21', 1, 46709983),
          array('chr22', 1, 50818468),
          array('chrX', 1, 156040895),
          array('chrY', 1, 57227415)
        );

        return array(
          'requestId' => 0 + $request_id,
          'type' => 'response',
          'data' => $result
        );

      default:
        break;
    }

    return array(
      'requestId' => 0 + $request_id,
      'type' => 'response',
      'data' => null
    );
  }
}

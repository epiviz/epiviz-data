<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 10/25/12
 * Time: 8:56 PM
 */

require_once('src/data/EpiVizDataManager.php');

$mgr = new EpiVizDataManager();

header('Access-Control-Allow-Origin: *');
echo json_encode($mgr->execute($_REQUEST));

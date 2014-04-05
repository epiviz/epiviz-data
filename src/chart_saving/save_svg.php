<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 5/8/13
 * Time: 6:14 PM
 */

require_once('ChartSaver.php');

$svg_content = $_REQUEST['svg'];
$svg_format = $_REQUEST['format'];

$svg_header = file_get_contents('svg_template.svg');

$svg_css = file_get_contents('svg.css');

// Insert style stuff after the svg header
$css_begin_tag = '<style type="text/css">';
$i = strpos($svg_content, $css_begin_tag);

$svg = $svg_header
  .substr($svg_content, 0, $i+strlen($css_begin_tag))
  .$svg_css
  .substr($svg_content, $i+strlen($css_begin_tag));

$id_begin = 'svg id="';
$i = strpos($svg_content, $id_begin) + strlen($id_begin);
$id = substr($svg_content, $i, strpos($svg_content, '"', $i + strlen($id_begin) + 1) - $i);

$svg = str_replace('#'.$id, '', $svg);
$svg = str_replace('.base-chart', '', $svg);

$saver = new ChartSaver($svg);

$saver->output($svg_format);

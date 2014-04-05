<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 5/8/13
 * Time: 6:05 PM
 */

require_once('../data/DBBasics.php');

class ChartSaver {

  private $svg;
  private $contentTypes = array(
    'svg' => 'text/xml',
    'pdf' => 'application/pdf',
    'ps'  => 'application/postscript',
    'png' => 'image/png',
    'eps' => 'application/eps'
  );
  private $inkscapeOptionMapping = array(
    'pdf' => 'A',
    'ps'  => 'P',
    'png' => 'e',
    'eps' => 'E'
  );

  public function __construct($svg) {
    $this->svg = $svg;
  }

  public function output($format) {
    $result = null;
    switch ($format) {
      case 'svg':
        $result = $this->toSVG();
        break;
      case 'pdf':
      case 'ps':
      case 'png':
        $result = $this->convertTo($format);
        break;
      case 'eps':
        $result = $this->toEPS();
        break;
    }

    header('Content-type: '.$this->contentTypes[$format]);
    // It will be called chart.[format]
    header('Content-Disposition: attachment; filename="chart.'.$format.'"');

    echo $result;
  }

  private function toSVG() {
    return $this->svg;
  }

  private function toEPS() {
    $format = 'eps';

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      // Windows:
      return $this->convertTo($format);
    }

    // Linux
    // First, convert to PS
    $ps = $this->convertTo('ps');

    $cmd = '/opt/local/bin/ps2eps -q';
    $descriptorspec = array(
      0 => array('pipe', 'r'),
      1 => array('pipe', 'w')
    );
    $cwd = null;
    $env = array(
      'PATH' => '/opt/local/bin:/usr/bin:/usr/local/bin'
    );

    $process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

    if (is_resource($process)) {

      fwrite($pipes[0], $ps);
      fclose($pipes[0]);

      $content = stream_get_contents($pipes[1]);
      fclose($pipes[1]);

      $return_value = proc_close($process);

      return $content;
    }

    return '';
  }

  private function convertTo($format) {
    // Transform into the given format

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      // Windows:
      $temp_file_name = DBBasics::generateGUID();
      $input_file = $temp_file_name.'.svg';
      $output_file = $temp_file_name.'.'.$format;
      file_put_contents($input_file, $this->svg);
      exec('"c:\Program Files (x86)\Inkscape\inkscape.exe" -'.
        $this->inkscapeOptionMapping[$format].' '.
        $output_file.' -f '.$input_file);
      $out = file_get_contents($output_file);
      unlink($output_file);
      unlink($input_file);
      //echo($out);
      return $out;
    }

    // Linux
    // $cmd = '/usr/bin/rsvg-convert -x 2 -y 2 -f '.$format;
    $cmd = '/usr/bin/rsvg-convert -f '.$format;
    $descriptorspec = array(
      0 => array('pipe', 'r'),
      1 => array('pipe', 'w')
    );

    $process = proc_open($cmd, $descriptorspec, $pipes);

    if (is_resource($process)) {

      fwrite($pipes[0], $this->svg);
      fclose($pipes[0]);

      $content = stream_get_contents($pipes[1]);
      fclose($pipes[1]);

      $return_value = proc_close($process);

      return $content;
    }

    return '';
  }
}

<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 2/6/13
 * Time: 10:36 AM
 */
class DBBasics {

  public static function generateGUID() {
    $uuid = DBBasics::generateStandardGUID();
    $uuid = str_replace('-', '', $uuid);
    $uuid = trim($uuid, '{}');

    return $uuid;
  }

  public static function generateStandardGUID() {
    if (function_exists('com_create_guid')) {
      return com_create_guid();
    } else {
      mt_srand((double)microtime() * 10000); //optional for php 4.2.0 and up.
      $charid = strtoupper(md5(uniqid(rand(), true)));
      $hyphen = chr(45); // "-"
      $uuid = chr(123) // "{"
        . substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12)
        . chr(125);
      // "}"
      return $uuid;
    }
  }


  public static function randUniqid($in, $to_num = false, $pad_up = false, $passKey = null) {
    $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if ($passKey !== null) {
      // Although this function's purpose is to just make the
      // ID short - and not so much secure,
      // you can optionally supply a password to make it harder
      // to calculate the corresponding numeric ID

      for ($n = 0; $n < strlen($index); $n++) {
        $i[] = substr($index, $n, 1);
      }

      $passhash = hash('sha256', $passKey);
      $passhash = (strlen($passhash) < strlen($index))
        ? hash('sha512', $passKey)
        : $passhash;

      for ($n = 0; $n < strlen($index); $n++) {
        $p[] = substr($passhash, $n, 1);
      }

      array_multisort($p, SORT_DESC, $i);
      $index = implode($i);
    }

    $base = strlen($index);

    if ($to_num) {
      // Digital number  <<--  alphabet letter code
      $in = strrev($in);
      $out = 0;
      $len = strlen($in) - 1;
      for ($t = 0; $t <= $len; $t++) {
        $bcpow = bcpow($base, $len - $t);
        $out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
      }

      if (is_numeric($pad_up)) {
        $pad_up--;
        if ($pad_up > 0) {
          $out -= pow($base, $pad_up);
        }
      }
      $out = sprintf('%F', $out);
      $out = substr($out, 0, strpos($out, '.'));
    } else {
      // Digital number  -->>  alphabet letter code
      if (is_numeric($pad_up)) {
        $pad_up--;
        if ($pad_up > 0) {
          $in += pow($base, $pad_up);
        }
      }

      $out = "";
      for ($t = floor(log($in, $base)); $t >= 0; $t--) {
        //$bcp = bcpow($base, $t);
        $bcp = pow($base, $t);
        $a = floor($in / $bcp) % $base;
        $out = $out . substr($index, $a, 1);
        $in = $in - ($a * $bcp);
      }
      $out = strrev($out); // reverse
    }

    return $out;
  }

  public static function generateSmallGUID() {
    return DBBasics::randUniqid(hexdec(bin2hex(openssl_random_pseudo_bytes(8))));
  }
}

<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 4/2/13
 * Time: 9:57 PM
 */

function idx($arr, $key, $default = null) {
  if (array_key_exists($key, $arr)) {
    return $arr[$key];
  }
  return $default;
}

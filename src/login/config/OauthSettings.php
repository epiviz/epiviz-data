<?php
/**
 * Created by Florin Chelaru ( florinc [at] umd [dot] edu )
 * Date: 4/4/14
 * Time: 8:34 PM
 */

class OauthSettings {

  private $settings = null;

  public function __construct($config_file_location) {
    $this->settings = json_decode(file_get_contents($config_file_location), true);
  }

  /**
   * @param string $provider_name An oauth provider name (like facebook or twitter)
   * @return array An array containing two keys: id and secret for the given oauth provider
   */
  public function get($provider_name) {
    return $this->settings['oauth'][$provider_name];
  }
} 
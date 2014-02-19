<?php

namespace ultimo\util\config\mvc\phptpl\helpers;

class Config extends \ultimo\phptpl\mvc\Helper {
  
  /**
   * Helper initial function. Returns the entire or a specific value from the
   * view configuration.
   * @param string $name The name of the view configuration to get.
   * @param string $key The key in the view configuration to get the value of,
   * null to return the entire configuration.
   * @return mixed The view configuration as array, or the value of the
   * specified key. Null if the config or the key does not exist.
   */
  public function __invoke($name, $key=null) {
    $config = $this->module->getPlugin('config')->getViewConfig($name);
    
    if ($key !== null) {
      return $config[$key];
    }
    
    return $config;
  }
}
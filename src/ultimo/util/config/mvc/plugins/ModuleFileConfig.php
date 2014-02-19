<?php

namespace ultimo\util\config\mvc\plugins;

class ModuleFileConfig implements \ultimo\mvc\plugins\ModulePlugin {
  
  /**
   * The module the plugin is for.
   * @var \ultimo\mvc\Module
   */
  protected $module;
  
  /**
   * The type of file config to use.
   * @var string
   */
  protected $type;
  
  /**
   * The extension of the config files.
   * @var string
   */
  protected $extension;
  
  /**
   * Cached module file configs, a hashtable with config names as key and
   * FileConfig objects as value.
   * @var array
   */
  protected $configs = array();
  
  /**
   * Cached merged module configs. A two dimensional hashtable with environment
   * names as the first key, the config names as the second key and config
   * arrays as values.
   * @var array
   */
  protected $configArrays = array();
  
  /**
   * Cached view file configs, a hashtable with config file paths as key and
   * FileConfig objects as value.
   * @var array
   */
  protected $viewConfigs = array();
  
  /**
   * Cached merged view configs. A two dimensional hashtable with environment
   * names as the first key, the config names as the second key and config
   * arrays as values.
   * @var array
   */
  protected $viewConfigArrays = array();
  
  /**
   * Constructor.
   * @param \ultimo\mvc\Module $module
   * @param string $type The type of file config to use.
   * @param string $extension The extension of the config files.
   */
  public function __construct(\ultimo\mvc\Module $module, $type, $extension) {
    $this->module = $module;
    $this->type = $type;
    $this->extension = $extension;
  }
  
  /**
   * Returns the module config with the specified name.
   * @param string $name The name of the configuration to get.
   * @return array The config with the specified name.
   */
  public function getConfig($name) {
    // retrieve the environment
    $environment = $this->module->getApplication()->getEnvironment();
    
    // make sure there is an entry for the enviroment for the cached config
    // arrays
    if (!isset($this->configArrays[$environment])) {
      $this->configArrays[$environment] = array();
    }
    
    // check if the requested config is cached
    if (!isset($this->configArrays[$environment][$name])) {
      // not cached, build the config array
      $config = array();
      
      // check if the file config for this specific module is cached
      if (!array_key_exists($name, $this->configs)) {
        // not cached, create a new file config object with the requested type
        // pointing to the file with the requested name and extension specified
        // in the 'configs' directory in the module, and cache it
        try {
          $this->configs[$name] = new $this->type($this->module->getBasePath() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . $name . '.' . $this->extension);
        } catch (\ultimo\util\config\exceptions\FileConfigException $e) {
          $this->configs[$name] = null;
        }
      }
      
      // if the config file exists in this specific module, get the config array
      // for the requested environment
      if ($this->configs[$name] !== null) {
        $sectionConfig = $this->configs[$name]->getSection($environment);
        
        if ($sectionConfig !== null) {
          $config = $sectionConfig;
        }
      }
      
      // if the module has a parent, merge the config array with the parents config array
      if ($this->module->getParent() !== null) {
        $config = $this->mergeConfigs($this->module->getParent()->getPlugin('config')->getConfig($name), $config);
      }
      
      // merge with the config arrays of each partial module
      foreach ($this->module->getPartials() as $partial) {
        $config = $this->mergeConfigs($partial->getPlugin('config')->getConfig($name), $config);
      }
      
      // cache the created config array
      $this->configArrays[$environment][$name] = $config;
    }
    
    // return the cached config array
    return $this->configArrays[$environment][$name];
  }
  
  /**
   * Returns the view config with the specified name.
   * @param string $name The name of the configuration to get.
   * @return array The config with the specified name.
   */
  public function getViewConfig($name) {
    // retrieve the environment
    $environment = $this->module->getApplication()->getEnvironment();
    
    // make sure there is an entry for the enviroment for the cached config
    // arrays
    if (!isset($this->viewConfigArrays[$environment])) {
      $this->viewConfigArrays[$environment] = array();
    }
    
    // check if the requested config is cached
    if (!isset($this->viewConfigArrays[$environment][$name])) {
      // not cached, build the config array
      $config = array();
      
      // search for file configs with the specified name in the 'configs'
      // directory in all view base paths
      $view = $this->module->getView();
      
      foreach (array_reverse($view->getBasePaths()) as $basePath) {
        $configPath = $basePath['path'] . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . $name . '.' . $this->extension;
        
        // check if the file config for this specific module is cached
        if (!array_key_exists($configPath, $this->viewConfigs)) {
          // not cached, create a new file config object with the requested type
          // pointing to the file with the requested name and extension specified
          // in the 'configs' directory in the module, and cache it
          try {
            $this->viewConfigs[$configPath] = new $this->type($configPath);
          } catch (\ultimo\util\config\exceptions\FileConfigException $e) {
            $this->viewConfigs[$configPath] = null;
          }
        }
        
        // if the config file exists, get the config array for the requested environment
        if ($this->viewConfigs[$configPath] !== null) {
          $section = $this->viewConfigs[$configPath]->getSection($environment);
          if ($section !== null) {
            $config = $this->mergeConfigs($config, $section);
          }
        }
      }
      
      // cache the created config array
      $this->viewConfigArrays[$environment][$name] = $config;
    }
    
    // return the cached config array
    return $this->viewConfigArrays[$environment][$name];
  }
  
  /**
   * Merges two multi dimensional config arrays
   * @param array $attrs1 The config to merge into.
   * @param array $attrs2 The config to merge with.
   * @return array The merged config.
   */
  public function mergeConfigs(array $config1, array $config2) {
    foreach ($config2 as $key => &$value) {
      if (is_array($value) && array_key_exists($key, $config1) && is_array($config1[$key])) {
        $config1[$key] = $this->mergeConfigs($config1[$key], $value);
      } else {
        $config1[$key] = $value;
      }
    }
    
    return $config1;
  }
  
  public function onControllerCreated(\ultimo\mvc\Controller $controller) { }
}
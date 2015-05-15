<?php

namespace ultimo\util\config\mvc\plugins;

class FileConfigPlugin implements \ultimo\mvc\plugins\ApplicationPlugin {
  
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
   * The MVC application.
   * @var \ultimo\mvc\Application The MVC application;
   */
  protected $application;
  
  /**
   * Constructor.
   * @param string $type The type of file config to use.
   * @param string $extension The extension of the config files.
   */
  public function __construct($type, $extension) {
    $this->type = $type;
    $this->extension = $extension;
  }
  
  public function onPluginAdded(\ultimo\mvc\Application $application) { 
    $this->application = $application;
  }
  
  /**
   * Appends a ModuleFileConfig plugin to the created module. Also adds the
   * helpers directory.
   */
  public function onModuleCreated(\ultimo\mvc\Module $module) {
    $module->addPlugin(new ModuleFileConfig($module, $this->type, $this->extension), 'config');
    
    // add the view helpers directory, if the view is phptpl
    $view = $module->getView();
    if ($view instanceof \ultimo\phptpl\Engine) {
      $helperPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'phptpl' . DIRECTORY_SEPARATOR . 'helpers';
      
      $nsElems = explode('\\', __NAMESPACE__);
      array_pop($nsElems);
      array_push($nsElems, 'phptpl', 'helpers');
      $helperNamespace = '\\' . implode('\\', $nsElems);
      $view->addHelperPath($helperPath, $helperNamespace);
    }
  }
  
  public function onRoute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) { }
  
  public function onRouted(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request=null) { }
  
  public function onDispatch(\ultimo\mvc\Application $application) { }
  
  public function onDispatched(\ultimo\mvc\Application $application) { }
  
  /**
   * Returns the module config with the specified name.
   * @param string $name The name of the configuration to get.
   * @return array The config with the specified name.
   */
  public function getConfig($name) {
    // retrieve the environment
    $environment = $this->application->getEnvironment();
    
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
          $this->configs[$name] = new $this->type($this->application->getApplicationDir() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . $name . '.' . $this->extension);
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
  
      // cache the created config array
      $this->configArrays[$environment][$name] = $config;
    }
    
    // return the cached config array
    return $this->configArrays[$environment][$name];
  }
}
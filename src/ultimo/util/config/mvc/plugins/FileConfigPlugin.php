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
   * Constructor.
   * @param string $type The type of file config to use.
   * @param string $extension The extension of the config files.
   */
  public function __construct($type, $extension) {
    $this->type = $type;
    $this->extension = $extension;
  }
  
  public function onPluginAdded(\ultimo\mvc\Application $application) { }
  
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
}
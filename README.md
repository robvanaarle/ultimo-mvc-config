# Ultimo MVC Config
Module file configurations for Ultimo MVC.

Includes a Ultimo MVC Phptpl helper to access configurations from the View. The configuration values belonging to the section with the name equal to the application environment are returned. Both module configurations as view configurations inherit of the configurations of parent modules. For views also the configuration of the parent views are inherited. 

## Requirements

* PHP 5.3
* Ultimo MVC
* Ultimo MVC Phptpl (optional)

## Usage
### Register plugin
	$application->addPlugin(new \ultimo\util\config\mvc\plugins\FileConfigPlugin('\ultimo\util\config\IniConfig', 'ini'));

### Module configuration
#### &lt;module&gt;/configs/api.ini
	[production]
    username = "Username"
	password = "Secret"

    [development : production]
    username = "dev-user"
    password = "dev-pass"

#### Access configuration values from Controller
	$mainConfig = $this->module->getPlugin('api')->getConfig('username');


### View configuration

#### &lt;module&gt;views/&lt;theme&gt;/configs/messages.ini
	[production]
    items_per_page = 10
    title = "Some title"

    [development : production]
    ; Inherit production config, but show more items per page
    items_per_page = 99999

#### Access configuration values from View
	<h1><?php echo $this->config('messages', 'title') ?></h1>
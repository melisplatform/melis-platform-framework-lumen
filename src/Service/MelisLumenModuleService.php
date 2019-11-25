<?php
namespace MelisPlatformFrameworkLumen\Service;

use MelisComposerDeploy\MelisComposer;
use MelisComposerDeploy\Service\MelisComposerService;
use Symfony\Component\Console\Input\StringInput;

class MelisLumenModuleService
{
    /**
     * @var string
     */
    private $serviceProvidersPath = __DIR__ . "/../../../../../thirdparty/Lumen/bootstrap/service.providers.php";
    /**
     * @var string
     */
    const MODULE_PATH = __DIR__ . "/../../../../../thirdparty/Lumen/module";
    /**
     * @var string
     */
    const TEMPLATE_ROUTE_FILE = __DIR__ . "/../../install/moduleTemplate/routes/web.php";
    /**
     * @var string
     */
    const TEMPLATE_SERVICE_PROVIDER = __DIR__ . "/../../install/moduleTemplate/src/Providers/TemplateProvider.php";
    /**
     * @var string
     */
    const TEMPLATE_CONTROLLER = __DIR__ . "/../../install/moduleTemplate/src/Controllers/IndexController.php";
    /**
     * @var string
     */
    const TEMPLATE_VIEWS = [
        'index' => [
            'phpTag' => true,
            'html' => __DIR__ . "/../../install/moduleTemplate/views/tool/index.blade.php"
        ],
        'header' => [
            'html' => __DIR__ . "/../../install/moduleTemplate/views/tool/header.blade.php"
        ],
    ];
    /**
     * @var string
     */
    private $moduleName = "";
    /**
     * @var string
     */
    public $moduleDir = "";
    /**
     * return service providers file path
     * @return string
     */
    public function getServiceProvidersPath()
    {
        return $this->serviceProvidersPath;
    }

    /**
     * @return string
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }

    /**
     * @return string
     */
    public function getTemplateRouteFile()
    {
        return $this->templateRouteFile;
    }

    public function getTemplateServiceProvider()
    {
        return $this->templateServiceProvider;
    }
    /**
     * @param $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function setModuleDir($moduleDir)
    {
        $this->moduleDir = $moduleDir;
    }

    public function getModuleDir()
    {
        return $this->moduleDir;
    }
    /**
     * Can add service provider class in file service.providers.php
     * @param $newClass string
     */
    public function add($newClass)
    {
        // get service provider file path
        $serviceProviders = require $this->getServiceProvidersPath();
        // check if class exists
        if (class_exists($newClass)) {
            // add only when class is not yet listed
            if (!in_array($newClass,$serviceProviders)){
                array_push($serviceProviders,$newClass);
                // prepend and append some string of the array value
                $providers = array_map(function($serviceProviders){
                    return "\t" .$serviceProviders . "::class,";
                },$serviceProviders);
                // make a online string for service providers
                $providers = implode("\t" . PHP_EOL,$providers);
                // comments
                $comments = "/**\n * load here your service provider class for better maintainability\n *  - classes here must be loaded from composer (autoload)\n */";
                // file contents
                $string = "<?php \n" . $comments ."\n"  .
                    "return [\n" . $providers . "\n];";
                // check if file is not writable then make it writable
                if (!is_writable($this->getServiceProvidersPath())) {
                    chmod($this->getServiceProvidersPath(),0777);
                }

                // update file contents
                return $this->writeFile($this->getServiceProvidersPath(),$string);
            }
        }
        
        return false;
    }
    private function writeFile($file,$content)
    {
        if (file_exists($file)) {
            $hanlder = fopen($file,"w");
            fwrite($hanlder,$content);
            fclose($hanlder);
            return true;
        }

        return false;
    }
    public function createModule($moduleName)
    {
        // first create the "module" folder if not existed
        if (!file_exists(self::MODULE_PATH)){
            mkdir(self::MODULE_PATH,0777);
        }
        // set module name
        $this->setModuleName($moduleName);
        // create the module based from moduleName
        $moduleDir = self::MODULE_PATH . DIRECTORY_SEPARATOR . $moduleName;
        if (file_exists($moduleDir)) {
            // error if modulename is already used
            die('Module '. $moduleName . " is already used, choose another module name");
        } else {
            // create folder
            mkdir($moduleDir,0777);
        }
        // set module dir
        $this->setModuleDir($moduleDir);
        // construct other folders
        $this->constructFolderStructure();
        // process routes
        $this->createRouteFile();
        // process service provider
        $providerName = $this->createServiceProviderFile();
        // process controller
        $this->createControllerFile();
        // process view files
        $this->createViewFiles();
        // add to lumen service.provders.php
        $this->add($providerName);

        return [
            "message" => 'Module ' . $moduleName . " created successfully"
        ];
    }

    /**
     * create other important folders
     *
     * @param $moduleDir
     */
    private function constructFolderStructure()
    {
        $foldersToCreate = [
//            'config',
//            'language',
            'routes',
            'http',
            'providers',
            'views',
          //  'models'
        ];
        // create folders
        foreach ($foldersToCreate as $i => $val) {
            mkdir($this->getModuleDir() . DIRECTORY_SEPARATOR . $val, 0777);
        }
    }
    private function createRouteFile()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR . "routes";
        // get the template route
        $templateRoutes = file_get_contents(self::TEMPLATE_ROUTE_FILE);
        // replace module_name in file
        $data = "<?php \n" . str_replace('[module_name]',$this->getModuleName(),$templateRoutes);
        // create a file
        $this->createFile($pathToCreate . DIRECTORY_SEPARATOR . "web.php",$data);

    }
    private function createServiceProviderFile()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "providers";
        // create directory
        if (!file_exists($pathToCreate)) {
            mkdir($pathToCreate,077);
        }
        // get the template service provider
        $templateServiceProvider = file_get_contents(self::TEMPLATE_SERVICE_PROVIDER);
        // replace module_name in file
        $data =  "<?php \n" . str_replace('[module_name]',$this->getModuleName(),$templateServiceProvider);
        // create a file
        $this->createFile($pathToCreate . DIRECTORY_SEPARATOR . $this->getModuleName() ."Provider.php",$data);

        return "LumenModule\\" . $this->getModuleName() . "\\Providers\\" . $this->getModuleName() . "Provider";

    }
    private function createControllerFile()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "http" . DIRECTORY_SEPARATOR . "Controllers";
        // create directory
        if (!file_exists($pathToCreate)) {
            mkdir($pathToCreate,077);
        }
        // get the template controller
        $tmpController = file_get_contents(self::TEMPLATE_CONTROLLER);
        // replace module_name in file
        $data =  "<?php \n" . str_replace('[module_name]',$this->getModuleName(),$tmpController);
        // create a file
        $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  ."IndexController.php",$data);
    }
    private function createViewFiles()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "views" . DIRECTORY_SEPARATOR . "tool";
        // create directory
        if (!file_exists($pathToCreate)) {
            mkdir($pathToCreate,077);
        }
        // get the view templates
        foreach (self::TEMPLATE_VIEWS as $idx => $val) {
            $tmpView = file_get_contents($val['html']);
            $phpTag = "";
            if (isset($val['phpTag'])) {
                $phpTag = "<?php \n";
            }
            // replace module_name in file
            $data =  $phpTag . str_replace('[module_name]',$this->getModuleName(),$tmpView);
            // create a file
            $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  ."$idx.blade.php",$data);
        }
    }
    private function createFile($filePath,$contents)
    {
        // open a file or create
        $file = fopen($filePath, "w");
        // write file
        fwrite($file,$contents);
        // close file stream
        fclose($file);
    }

}
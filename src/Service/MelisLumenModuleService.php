<?php
namespace MelisPlatformFrameworkLumen\Service;

use Illuminate\Support\Facades\DB;
use MelisComposerDeploy\MelisComposer;
use MelisComposerDeploy\Service\MelisComposerService;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Component\Console\Input\StringInput;
use Zend\Session\Container;

class MelisLumenModuleService
{
    /**
     * @var string
     */
    const MODULE_NAMESPACE = "LumenModule";
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
    const TEMPLATE_MODEL = __DIR__ . "/../../install/moduleTemplate/src/Model/ModelTemplate.php";
    /**
     * @var string
     */
    const TEMPLATE_CONFIG_FILE = [
        'table' => __DIR__ . "/../../install/moduleTemplate/config/tmp.table.config.php"
    ];
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
    private $serviceProvidersPath = __DIR__ . "/../../../../../thirdparty/Lumen/bootstrap/service.providers.php";
    /**
     * @var string
     */
    private $moduleName;
    /**
     * @var string
     */
    public $moduleDir;

    /**
     * @var array
     */
    public $toolCreatorSession;
    /**
     * @var array 
     */
    private $configs;
    /**
     * @var string
     */
    private $modelName;

    public function __construct()
    {
        // set tool creator session
        $this->toolCreatorSession = app('MelisToolCreatorSession')['melis-toolcreator'];
        // set model name
        $this->setModelname(str_replace('_',null,ucwords($this->getTableName(),'_')) . "Table");
    }

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

    public function getToolCreatorSession()
    {
        return $this->toolCreatorSession;
    }
    public function getModelName()
    {
        return $this->modelName;
    }
    public function setModelname($modelName)
    {
        $this->modelName = $modelName;
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
                // check if file is not writable then make MelisLumenModuleService it writable
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
        // set module name
        $this->setModuleName($moduleName);
        // create config files
        $this->createConfigFiles();
        // create module directory
        $moduleDir = $this->createModuleDir();
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
        // process locale translations
        $this->createTranslationFiles();
        // process configs
        $this->createConfigFiles();
        // process view files
        $this->createViewFiles();
        // process model
        $this->createModelFile();
        // add to lumen service.provders.php
        $this->add($providerName);

        return [
            "message" => 'Module ' . $moduleName . " created successfully"
        ];
    }
    private function createModuleDir()
    {
        // first create the "module" folder if not existed
        if (!file_exists(self::MODULE_PATH)){
            mkdir(self::MODULE_PATH,0777);
        }
        // create the module based from moduleName
        $moduleDir = self::MODULE_PATH . DIRECTORY_SEPARATOR . $this->getModuleName();
        if (file_exists($moduleDir)) {
            // error if modulename is already used
            die('Module '. $this->getModuleName() . " is already used, choose another module name");
        } else {
            // create folder
            mkdir($moduleDir,0777);
        }

        return $moduleDir;
    }

    /**
     * create other important folders
     *
     * @param $moduleDir
     */
    private function constructFolderStructure()
    {
        $foldersToCreate = [
            'config',
            'language',
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
        $data = "<?php \n" . str_replace('[module_name]',strtolower($this->getModuleName()),$templateRoutes);
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

        return self::MODULE_NAMESPACE . "\\" . $this->getModuleName() . "\\Providers\\" . $this->getModuleName() . "Provider";

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
            $tmpView = str_replace('[?]', '?',$tmpView);
            // replace module_name in file
            $data =  $phpTag . str_replace('[module_name]',$this->getModuleName(),$tmpView);
            // create a file
            $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  ."$idx.blade.php",$data);
        }
    }
    private function createTranslationFiles()
    {
        $locales = $this->getMelisLanguages();
        $translations = $this->getToolTranslations();
        foreach ($locales as $i => $locale) {
            $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "language" . DIRECTORY_SEPARATOR . explode('_',$locale)[0];
            // create directory
            if (!file_exists($pathToCreate)) {
                mkdir($pathToCreate,077);
            }
            $phpTag = "<?php \n";
            // replace module_name in file
            $tmpData =  "";
            foreach ($translations[$locale] as $key => $val) {
                $tmpData .= "\t'".$key . "' => '" . $val . "',\n";
            }
            $data = $phpTag . "\n return [\n" . $tmpData . " ];";
            // create a file
            $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  ."messages.php",$data);
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

    private function createConfigFiles()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "config";
        // create directory
        if (!file_exists($pathToCreate)) {
            mkdir($pathToCreate,077);
        }
        $columns = $this->getTableColumns();
        $searchables = $this->getSearchableColumns();
        // get the template configs
        foreach (self::TEMPLATE_CONFIG_FILE as $fileName => $val) {
            $tmpConfig = file_get_contents($val);
            // replace module_name in file
            $partialContent = str_replace('[tool_columns]',$columns,$tmpConfig);
            $partialContent = str_replace('[tool_searchables]',$searchables,$partialContent);
            $data =  "<?php \n" . str_replace('[module_name]', $this->getModuleName(),$partialContent);
            // create a file
            $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  . $fileName. ".config.php",$data);
        }
    }
    private function createModelFile()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "http" . DIRECTORY_SEPARATOR . "Model";
        // create directory
        if (!file_exists($pathToCreate)) {
            mkdir($pathToCreate,077);
        }
        // get the primary key of the table
        $primarykey = DB::connection('melis')->select(DB::raw("SHOW KEYS FROM `" . $this->getTableName() . "` WHERE Key_name = 'PRIMARY'"))[0]->Column_name;
        // get the template controller
        $tmpModel = file_get_contents(self::TEMPLATE_MODEL);
        // construct model name
        $modelName = str_replace('_',null,ucwords($this->getTableName(),'_')) . "Table";
        // replace mode_name
        $tmpModel = str_replace('[model_name]',$modelName,$tmpModel);
        // set primary key
        $tmpModel = str_replace('[primary_key]',$primarykey,$tmpModel);
        // replace table_name
        $tmpModel = str_replace('[table_name]',$this->getTableName(),$tmpModel);
        // replace module_name in file
        $data =  "<?php \n" . str_replace('[module_name]',$this->getModuleName(),$tmpModel);
        // create a file
        $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  . $modelName . ".php",$data);

    }
    private static function p($text)
    {
        echo "<pre>";
        print_r($text);
        echo "</pre>";
    }
    /**
     * @return array
     */
    private function getToolTranslations()
    {
        $translations = [];
        $arraykeys = [
            'tcf-title',
            'tcf-desc',
        ];
        // get melis_core_language
        $localesHasTranslations = [];
        $coreLanguage = $this->getMelisLanguages();
        // self::p($this->toolCreatorSession);
        foreach ($coreLanguage as $i => $locale) {
            if (!empty($this->getToolCreatorSession()['step2'][$locale]['tcf-title'])){
                array_push($localesHasTranslations,$locale);
            }
            $translations[$locale] = [
                "tr_" . strtolower($this->getModuleName()) . "_title" => $this->constructTranslations($this->getToolCreatorSession()['step2'],$locale,$localesHasTranslations,'tcf-title'),
                "tr_" . strtolower($this->getModuleName()) . "_desc" => $this->constructTranslations($this->getToolCreatorSession()['step2'],$locale,$localesHasTranslations,'tcf-desc'),
            ];
        }

        foreach ($this->getToolCreatorSession()['step6'] as $i => $val) {
            if (is_array($val)) {
                $step6Translations[$i] = $val['pri_tbl'];
                if (isset($val['lang_tbl'])) {
                    $step6Translations[$i] = $val['lang_tbl'] ;
                }
            }
        }
        // column list translations
        $tmpTrans = [];
        $excludedField = [
            'tcf-lang-local',
            'tcf-tbl-type'
        ];
        foreach ($coreLanguage as $i => $coreLocale) {
            foreach ($step6Translations as $locale => $val2) {
                foreach ($val2 as $dbField => $fieldVal) {
                    $field = str_replace('tcinputdesc','tooltip',$dbField);
                    if (empty($fieldVal)) {
                        // check for other translations
                        if ($coreLocale != $locale) {
                            if(!empty($step6Translations[$coreLocale][$dbField])) {
                                if (!in_array($dbField,$excludedField)) {
                                    if (isset($tmpTrans[$locale]) && is_array($tmpTrans[$locale])) {
                                        $tmp = [];
                                        $tmp[$locale] = [
                                            'tr_' . strtolower($this->getModuleName()) . "_" . $field  => $step6Translations[$coreLocale][$dbField]
                                        ];
                                        $tmpTrans[$locale] = array_merge($tmpTrans[$locale],$tmp[$locale]);
                                    } else {
                                        $tmpTrans[$locale] = [
                                            'tr_' . strtolower($this->getModuleName()) . "_". $field  => $step6Translations[$coreLocale][$dbField]
                                        ];
                                    }
                                }
                            }
                        }
                    } else {
                        if (!in_array($dbField,$excludedField)) {
                            if (isset($tmpTrans[$locale]) && is_array($tmpTrans[$locale])) {
                                $tmp = [];
                                $tmp[$locale] = [
                                    'tr_' . strtolower($this->getModuleName()) . "_" . $field  => $fieldVal
                                ];
                                $tmpTrans[$locale] = array_merge($tmpTrans[$locale],$tmp[$locale]);
                            } else {
                                $tmpTrans[$locale] = [
                                    'tr_' . strtolower($this->getModuleName()) . "_" .$field  => $fieldVal
                                ];
                            }
                        }
                    }
                }
            }
        }
        $translations = array_merge_recursive($translations,$tmpTrans);

        return $translations;
    }
    public function getMelisLanguages()
    {
        $data = DB::connection('melis')->table('melis_core_lang')->select('lang_locale')->get()->all();
        $tmp = [];
        foreach ($data as $val) {
            array_push($tmp,$val->lang_locale);
        }
        return $tmp;
    }

    public function constructTranslations($translations, $locale, $availableTranslations, $searchKey)
    {
        $translation = null;
        if (!empty($translations[$locale][$searchKey])) {
            array_push($availableTranslations,$locale);
            $translation = $translations[$locale][$searchKey];
        } else {
            // get the last
            $availableLocale = $availableTranslations[0] ?? null;
            $translation = $translations[$availableLocale][$searchKey];
        }

        return $translation;
    }

    public function getTableColumns()
    {
        $columns = $this->getToolCreatorSession()['step4']['tcf-db-table-cols'];
        $partialContent = null;
        $columnsWidth = round(90/count($columns));
        foreach ($columns as $i => $val) {
            $partialContent .= "\t\t\t'$val'" . " => [\n \t\t\t\t' text' => __('" . $this->getModuleName() ."::messages.tr_" . strtolower($this->getModuleName()) ."_" . $val . "'),\n\t\t\t\t 'css' => ['width' => '" . $columnsWidth . "%'],\n\t\t\t\t 'sortable' => true  \n\t\t\t],\n";
        }

        return "[\n  " . $partialContent ." \t\t],";
    }

    public function getSearchableColumns()
    {
        $columns = $this->getToolCreatorSession()['step4']['tcf-db-table-cols'];
        $partialContent = null;
        foreach ($columns as $i => $val) {
            $partialContent .= "'". $val . "',";
        }

        return  "[" . $partialContent . "],";
    }
    public function getTableName()
    {
       return $this->getToolCreatorSession()['step3']['tcf-db-table'];
    }



}
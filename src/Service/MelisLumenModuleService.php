<?php
namespace MelisPlatformFrameworkLumen\Service;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
    const TEMPLATE_SERVICE = __DIR__ . "/../../install/moduleTemplate/src/Service/TemplateService.php";
    /**
     * @var string
     */
    const TEMPLATE_CONFIG_FILE = [
        'table' => __DIR__ . "/../../install/moduleTemplate/config/tmp.table.config.php",
        'form' => __DIR__ . "/../../install/moduleTemplate/config/tmp.form.config.php",
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
        'tmp-modal' => [
            'html' => __DIR__ . "/../../install/moduleTemplate/views/tool/temp-modal.blade.php"
        ],
        'modal-content' => [
            'phpTag' => true,
            'html' => __DIR__ . "/../../install/moduleTemplate/views/tool/modal-content.blade.php"
        ],
    ];
    const ASSETS = [
        'js' => [
            'fileName' => 'tool.js',
            'file' => __DIR__ . "/../../install/moduleTemplate/assets/js/tool-script-template.js"
        ]
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

    /**
     * @var string
     */
    private $tablePrimaryKey;
    /**
     * MelisLumenModuleService constructor.
     */
    public function __construct()
    {
        // set tool creator session
        $this->toolCreatorSession = app('MelisToolCreatorSession')['melis-toolcreator'];
        if (! empty($this->toolCreatorSession)) {
            // set module name
            $this->setModuleName($this->toolCreatorSession['step1']['tcf-name']);
            // set model name
            $this->setModelname(str_replace('_',null,ucwords($this->getTableName(),'_')) . "Table");
            // set table primary key
            $this->setTablePrimaryKey(DB::connection('melis')->select(DB::raw("SHOW KEYS FROM `" . $this->getTableName() . "` WHERE Key_name = 'PRIMARY'"))[0]->Column_name);
        } else {
            die("Run first melis tool creator with an option of create a tool with framework");
        }

    }

    public function getTablePrimaryKey()
    {
        return $this->tablePrimaryKey;
    }
    public function setTablePrimaryKey($primaryKey)
    {
        $this->tablePrimaryKey = $primaryKey;
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
            if (!in_array($newClass, $serviceProviders)){
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
    public function createModule()
    {
        /*
         * return if the tool creator is not for framework and lumen
         */
        if (!$this->getToolCreatorSession()['step1']['tcf-create-framework-tool'] && $this->getToolCreatorSession()['step1']['tcf-tool-framework'] != "lumen")
            return;

        // create module directory
        $this->createModuleDir();
        // construct other folders
        $this->constructFolderStructure();
        // process routes
        $this->createRouteFile();
        // process service provider
        $this->createServiceProviderFile();
        // process controller
        $this->createControllerFile();
        // process locale translations
        $this->createTranslationFiles();
        // process configs
        $this->createConfigFiles();
        // process assets
        $this->createAssetsFile();
        // process view files
        $this->createViewFiles();
        // process model
        $this->createModelFile();
        // proccess service
        $this->createServiceFile();

        return [
            "message" => 'Module ' . $this->getModuleName() . " created successfully"
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
        // set module dir
        $this->setModuleDir($moduleDir);
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
            'views'
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

        $providerName = self::MODULE_NAMESPACE . "\\" . $this->getModuleName() . "\\Providers\\" . $this->getModuleName() . "Provider";
        // add to lumen service.provders.php
        $this->add($providerName);

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
        $tmpController = str_replace('[primary_key]',$this->getTablePrimaryKey(),$tmpController);
        // replace module_name in file
        $data =  "<?php \n" . str_replace('[module_name]',$this->getModuleName(),$tmpController);
        // create a file
        $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  ."IndexController.php",$data);
    }

    /**
     * create view files for the lumen module
     */
    private function createViewFiles()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "views" . DIRECTORY_SEPARATOR . "tool";
        // create directory
        if (!file_exists($pathToCreate)) {
            mkdir($pathToCreate,077);
        }
        // get the view templates
        foreach (self::TEMPLATE_VIEWS as $idx => $val) {
            // override the modal content if the tool is a tabulation
            if ($idx == "modal-content") {
                if ($this->toolIsTab()) {
                    $val['html'] = __DIR__  . "/../../install/moduleTemplate/views/tool/tabulation-content.blade.php";
                }
            }
            // get html content
            $tmpView = file_get_contents($val['html']);
            $phpTag = "";
            if (isset($val['phpTag'])) {
                $phpTag = "<?php \n";
            }
            $tmpView = str_replace('[?]', '?',$tmpView);
            if (!$this->toolIsTab()){
                $tmpView = str_replace('[tool_type]', "data-toggle=\"modal\" data-target=\"#{{ " . strtolower($this->getModuleName()) ."  }}Modal\"");
            }
            // replace module_name in file
            $data =  $phpTag . str_replace('[module_name]',$this->getModuleName(),$tmpView);
            // create a file
            $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  ."$idx.blade.php",$data);
        }
    }

    /**
     * create translation files for lumen module
     */
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
                $tmpData .= "\t\"".$key . "\" => \"" . preg_replace("/\r|\n/", "", $val) . "\",\n";
            }
            $tmpData = str_replace('$',"\\$",$tmpData);
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
        $formFields = $this->getFormFields();
        // get the template configs
        foreach (self::TEMPLATE_CONFIG_FILE as $fileName => $val) {
            $tmpConfig = file_get_contents($val);
            // replace module_name in file
            $partialContent = str_replace('[tool_columns]',$columns,$tmpConfig);
            $partialContent = str_replace('[tool_searchables]',$searchables,$partialContent);
            $partialContent = str_replace('[elements]',$formFields ,$partialContent);
            // tooltype to open
            $partialContent = str_replace('[tool_type]',$this->toolType(),$partialContent);
            $data =  "<?php \n" . str_replace('[module_name]', $this->getModuleName(),$partialContent);
            // create a file
            $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  . $fileName. ".config.php",$data);
        }
    }
    private function toolType()
    {
        if (!$this->toolIsTab()) {
            return "data-toggle=\"modal\" data-target=\"#" . strtolower($this->getModuleName())  . "Modal\"";
        }
        return null;
    }
    private function createModelFile()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "http" . DIRECTORY_SEPARATOR . "Model";
        // create directory
        if (!file_exists($pathToCreate)) {
            mkdir($pathToCreate,077);
        }
        // get the template controller
        $tmpModel = file_get_contents(self::TEMPLATE_MODEL);
        // construct model name
        $modelName = str_replace('_',null,ucwords($this->getTableName(),'_')) . "Table";
        // replace mode_name
        $tmpModel = str_replace('[model_name]',$modelName,$tmpModel);
        // set primary key
        $tmpModel = str_replace('[primary_key]',$this->getTablePrimaryKey(),$tmpModel);
        // replace table_name
        $tmpModel = str_replace('[table_name]',$this->getTableName(),$tmpModel);
        // replace module_name in file
        $data =  "<?php \n" . str_replace('[module_name]',$this->getModuleName(),$tmpModel);
        // create a file
        $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  . $modelName . ".php",$data);

    }
    public function createServiceFile()
    {
        $pathToCreate = $this->getModuleDir() . DIRECTORY_SEPARATOR  . "http" . DIRECTORY_SEPARATOR . "Service";
        // create directory
        if (!file_exists($pathToCreate)) {
            mkdir($pathToCreate,077);
        }
        // get the template controller
        $tmpModel = file_get_contents(self::TEMPLATE_SERVICE);
        // replace mode_name
        $tmpModel = str_replace('[model_name]',$this->getModelName(), $tmpModel);
        // set primary key
        $tmpModel = str_replace('[primary_key]',$this->getTablePrimaryKey(), $tmpModel);
        // set service template name
        $tmpModel = str_replace('[template_service_name]',$this->getModuleName() . "Service", $tmpModel);
        // replace table_name
        $tmpModel = str_replace('[table_name]',$this->getTableName(), $tmpModel);
        // replace module_name in file
        $data =  "<?php \n" . str_replace('[module_name]',$this->getModuleName(),$tmpModel);
        // create a file
        $this->createFile($pathToCreate . DIRECTORY_SEPARATOR  . $this->getModuleName() . "Service.php",$data);
    }

    private function createAssetsFile()
    {
        foreach (self::ASSETS as $idx => $file) {
            $pathToCreate = __DIR__ . "/../../../../../module/" . $this->getModuleName() . DIRECTORY_SEPARATOR  . "public" . DIRECTORY_SEPARATOR . "js";
            // create directory
            if (!file_exists($pathToCreate)) {
                mkdir($pathToCreate,077);
            }
            // get the template controller
            $tmpFile = file_get_contents($file['file']);
            // replace module_name in file
            $data = str_replace('[module_name]',strtolower($this->getModuleName()),$tmpFile);
            // for edit tool
            $data = str_replace(['[edit-button-event]'],$this->editButtonEventJs(),$data);
            // for save event
            $data = str_replace(['[save-button-event]'], $this->saveButtonEventJs(),$data);
            // tab save callback function
            $data = str_replace('[tab_save_callback]', $this->tabSaveCallbackJs(), $data);
            // for add event
            $data = str_replace('[add-button-event]', $this->addButtonEventJs(), $data);
            // formanem
            $data = str_replace('[form_name]',strtolower($this->getModuleName()) . "form",$data);
            // create a file
            $this->createFile($pathToCreate  . DIRECTORY_SEPARATOR . $file['fileName'],$data);
        }
    }
    private function addButtonEventJs()
    {
        $moduleName = strtolower($this->getModuleName());
        $script = "$(\"body\").on('click','.add-" . $moduleName . "', function(){
            // append loader
            $(\".modal-dynamic-content\").html(" . $moduleName . "Tool.tempLoader);
            // get the configured form
            " . $moduleName . "Tool.getToolModal(function(data){
                $(\".modal-dynamic-content\").html(data);
            });
        });";
        if ($this->toolIsTab()) {
            $script =
                "\$body.on(\"click\", \".add-$moduleName\", function(){
        var tabTitle = translations.tr_" . $moduleName . "_common_add;
     
        // Opening tab form for add/update
        melisHelper.tabOpen(tabTitle, 'fa fa-puzzle-piece', '0_id_" . $moduleName . "_tool_form', '" . $moduleName . "_tool_form', { id: 0}, 'id_" . $moduleName . "_tool');
    });";
        }

        return $script;
    }
    private function editButtonEventJs()
    {
        $moduleName = strtolower($this->getModuleName());
        $script =
            "$(\"body\").on('click',\".edit-$moduleName\", function(){
                var id = $(this).parent().parent().attr('id');
                // append loader
                $(\".modal-dynamic-content\").html(" . $moduleName . "Tool.tempLoader);
                // get the configured form
                " . $moduleName . "Tool.getToolModal(function(data){
                    $(\".modal-dynamic-content\").html(data);
                },id);
            });";

        // override
        if ($this->toolIsTab()) {
            $script =
                "\$body.on(\"click\", \".edit-$moduleName\", function(){
           
           var id = $(this).parent().parent().attr('id');
        var tabTitle = translations.tr_" . $moduleName . "_title + \" / \" +id;

        // Opening tab form for add/update
        melisHelper.tabOpen(tabTitle, 'fa fa-puzzle-piece', id+'_id_" . $moduleName . "_tool_form', '" . $moduleName . "_tool_form', {id: id}, 'id_" . $moduleName . "_tool');
    });";
        }

        return $script;

    }
    private function saveButtonEventJs()
    {
        $modulename = strtolower($this->getModuleName());
        $script = "$(\"body\").on('click', '#save-$modulename', function(){
            $(\"#$modulename form\").submit();
        });";
        // override script
        if ($this->toolIsTab()) {
            $script = "$(\"body\").on('click', '#save-$modulename', function(){
            var targetForm = $(this).data('target');
            $(\"#\" + targetForm + \" form\").submit();
        });";
        }

        return $script;
    }
    private function tabSaveCallbackJs()
    {
        $script = null;
        if ($this->toolIsTab()) {
            $moduleName = strtolower($this->getModuleName());
            $script = "// Close add/update tab zone
                $(\"a[href$=\" + data.id + \"_id_" . $moduleName . "_tool_form']\").siblings('.close-tab').trigger('click');

                // Open new created/updated entry
                melisHelper.tabOpen(translations.tr_" . $moduleName . "_title + ' / ' + data.id, 'fa fa-puzzle-piece', data.id+'_id_" . $moduleName . "_tool_form', '" . $moduleName . "_tool_form', {id: data.id}, 'id_" . $moduleName . "_tool');";
        }

        return $script;
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
        // include melis common translations
        $translations = array_merge_recursive($translations,$this->getMelisCommonTranslations());

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
            $mainId = "";
            if ($val == $this->getTablePrimaryKey()) {
                $mainId = "DT_RowId";
            } else {
                $mainId = $val;
            }
            $partialContent .= "\t\t\t'$mainId'" . " => [\n \t\t\t\t 'text' => __('" . $this->getModuleName() ."::messages.tr_" . strtolower($this->getModuleName()) ."_" . $val . "'),\n\t\t\t\t 'css' => ['width' => '" . $columnsWidth . "%'],\n\t\t\t\t 'sortable' => true  \n\t\t\t],\n";
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
    public function getFormFields()
    {
        $string = "";
        /*
         * construct form fields
         */
        foreach ($this->getTableFields() as $field => $options) {
            switch ($options['type']) {
                case "File":
        $string .=
            "[
                'type' => 'file',
                'name' => '". $field . "',
                'options' => [
                    'label'   => " . ($options['label'] ?? null) . ",
                    'tooltip' => " . ($options['tooltip'] ?? null) . ",
                    'filestyle_options' => [
                        'buttonBefore' => true,
                        'buttonText' => 'Choose',
                     ]
                ],
                'attributes' => [
                    'required'   => '" . (isset($options['required']) ? "required" : null) . "',
                    'class'   => 'form-control',
                ],
            ],\n\t\t\t";
                    break;
                case "Switch" :
        $string .=
            "[
                'type' => 'checkbox',
                'name' => '". $field . "',
                'options' => [
                    'label'   => " . ($options['label'] ?? null) . ",
                    'tooltip' => " . ($options['tooltip'] ?? null) . ",
                    'switchOptions' => [
                        'label' => 'STATUS',
                        'label-on' => 'YES',
                        'label-off' => 'NO',
                        'icon' => \"glyphicon glyphicon-resize-horizontal\",
                    ],
                    'value_options' => [
                        'on' => 'on',
                    ],
                ],
                'attributes' => [
                    'required'   => '" . (isset($options['required']) ? "required" : null) . "',
                    'class'   => 'form-control',
                ],
            ],\n\t\t\t";
        break;
                default :
        $string .=
            "[
                'type' => '". $options['type'] . "',
                'name' => '". $field . "',
                'options' => [
                    'label'   => " . ($options['label'] ?? null) . ",
                    'tooltip' => " . ($options['tooltip'] ?? null) . ",
                ],
                'attributes' => [
                    'required'   => '" . (isset($options['required']) ? "required" : null) . "',
                    'class'   => 'form-control',
                ],
            ],\n\t\t\t";break;
            }
        }

        return $string;
    }
    private function getTableFields()
    {
        $formFields = [];
        // get editable columns
        $editableCols = $this->getToolCreatorSession()['step5']['tcf-db-table-col-editable'];
        // get required columns
        $requiredCols = $this->getToolCreatorSession()['step5']['tcf-db-table-col-required'];
        // input types
        $fieldTypes   = $this->getToolCreatorSession()['step5']['tcf-db-table-col-type'];
        // editable columns
        foreach ($editableCols as $idx => $field) {
            $type = $fieldTypes[$idx];
            // put requried properties of an element
            $formFields[$field] = [
                'label'    => '__(\'' . $this->getModuleName() . '::messages.tr_' . strtolower($this->getModuleName()) . '_' . $field . '\')',
                'tooltip'    => '__(\'' . $this->getModuleName() . '::messages.tr_' . strtolower($this->getModuleName()) . '_' . $field . '_tooltip\')',
                'class'    => $field,
                'type'     => $type
            ];
            // check for id make it hidden
            if ($field == $this->getTablePrimaryKey()) {
                $formFields[$field]['type'] = "hidden";
            } 
            // make columns editable except for table primary key 
            if ($field != $this->getTablePrimaryKey()) {
                $formFields[$field]['editable'] = true;
            }
        }
        // required columns
        foreach ($requiredCols as $idx => $field) {
            if ($field != $this->getTablePrimaryKey()) {
                 $formFields[$field]['required'] = true;
            }
        }
        
        
        return $formFields;
    }
    public function getTableName()
    {
       return $this->getToolCreatorSession()['step3']['tcf-db-table'];
    }

    public function getMelisCommonTranslations()
    {
        $moduleName = strtolower($this->getModuleName());
        $commonTranslations = [];
        $commonTranslations['en_EN'] = [
            'tr_' . $moduleName . '_common_add' => 'Add',
            'tr_' . $moduleName . '_common_edit' => 'Edit',
            'tr_' . $moduleName . '_common_delete' => 'Delete',
            'tr_' . $moduleName . '_common_save' => 'Save',
            'tr_' . $moduleName . '_common_close' => 'Close',
            'tr_' . $moduleName . '_common_refresh' => 'Refresh',
            'tr_' . $moduleName . '_common_delete_item' => 'Delete item',
            'tr_' . $moduleName . '_common_delete_message' => 'Are you sure you want to delete this item?',
            'tr_' . $moduleName . '_delete_item_success' => 'Item deleted successfully',
            'tr_' . $moduleName . '_save_item_success' => 'Item created successfully',
            'tr_' . $moduleName . '_update_item_success' => 'Item saved successfully',
            'tr_' . $moduleName . '_add_item_failed' => 'Unable to save',
            'tr_' . $moduleName . '_update_failed' => 'Unable to update',
        ];
        $commonTranslations['fr_FR'] = [
            'tr_' . $moduleName . '_common_add' => 'Ajouter',
            'tr_' . $moduleName . '_common_edit' => 'Editer',
            'tr_' . $moduleName . '_common_delete' => 'Supprimer',
            'tr_' . $moduleName . '_common_save' => 'Sauvegarder',
            'tr_' . $moduleName . '_common_close' => 'Annuler',
            'tr_' . $moduleName . '_common_refresh' => 'Rafraichir',
            'tr_' . $moduleName . '_common_delete_item' => 'Supprimer l\'élément',
            'tr_' . $moduleName . '_common_delete_message' => 'Etes-vous sûr de vouloir supprimer cet élément?',
            'tr_' . $moduleName . '_delete_item_success' => 'Elément supprimé avec succès',
            'tr_' . $moduleName . '_save_item_success' => 'Elément enregistré avec succès',
            'tr_' . $moduleName . '_update_item_success' => 'Elément enregistré avec succès',
            'tr_' . $moduleName . '_add_item_failed' => 'Impossible d\'enregistrer',
            'tr_' . $moduleName . '_update_failed' => 'Impossible de mettre',
        ];
        // for other languages that are not yet created
        foreach ($this->getMelisLanguages() as $idx => $val) {
            if (!in_array($val,['en_EN','fr_FR'])) {
                $commonTranslations[$val] = $commonTranslations['en_EN'];
            }
        }
        return $commonTranslations;

    }

    public function makeValidator($postData , $fields = [],$messages =  [])
    {
        // make a validator for the request parameters
        return Validator::make($requestParams,$fields ,$messages);
    }
    
    public function toolIsTab()
    {
         return ($this->getToolCreatorSession()['step1']['tcf-tool-edit-type'] == 'tab') ? true : false ;
    }
    public function toolIsDb()
    {
        return ($this->getToolCreatorSession()['step1']['tct-tool-type'] == 'db') ? true : false ;
    }
}
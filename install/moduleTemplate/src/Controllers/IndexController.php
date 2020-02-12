namespace LumenModule\[module_name]\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;use Laravel\Lumen\Routing\Controller as BaseController;
use MelisCore\Service\MelisCoreFlashMessengerService;use MelisPlatformFrameworkLumen\Service\MelisPlatformToolService;
use LumenModule\[module_name]\Http\Service\[module_name]Service;
use Illuminate\Http\Request;

class IndexController extends BaseController
{
    /**
    * default module namespace
    * @var string
    */
    private $viewNamespace = "[module_name]";

    private $toolService;

    private $melisToolService;

    public function __construct([module_name]Service $toolService,MelisPlatformToolService $melisToolService)
    {
        $this->toolService = $toolService;
        $this->melisToolService = $melisToolService;
    }
    public function renderIndex()
    {
        return view($this->viewNamespace . "::tool/index");
    }

    public function getTableData()
    {
        $request = app('request');
        $success = 0;
        $colId = array();
        $dataCount = 0;
        $draw = 0;
        $dataFiltered = 0;
        $tableData = array();
        $parimaryKey = '[primary_key]';
        if($request->getMethod() == Request::METHOD_POST) {

            $lumenAlbumSrvc = $this->toolService;
            $tableConfig = config('[module_name]')['table_config'];
            $tableColumnDisplayType = $tableConfig['table']['columns'];
            $displayTypes = [];
            foreach ($tableColumnDisplayType as $field => $val) {
                $displayTypes[$field] = $val['display_type'];
            }
            $params = $request->request->all();
            /*
            * standard datatable configuration
            */
            $sortOrder = $params['order'][0]['dir'];
            $selCol    = $params['order'];
            $colId     = array_keys($tableConfig['table']['columns']);
            $draw      = $params['draw'];
            // pagination start
            $start     = $params['start'];
            // drop down limit
            $length    = $params['length'];
            // search value from the table
            $search    = $params['search']['value'];
            // get all searchable columns from the config
            $searchableCols = $tableConfig['table']['searchables'] ?? [];
            // get data from the service
            $data = $lumenAlbumSrvc->getDataWithFilters($start,$length,$searchableCols,$search,$parimaryKey,$sortOrder);
            // get total count of the data in the db
            $dataCount = $data['dataCount'];
            // organized data
            $c = 0;
            foreach($data['data'] as $datum){
                $toolTableColumns = array_keys($tableConfig['table']['columns']);
                foreach ($toolTableColumns as $field) {
                    if ($field == 'DT_RowId') {
                        $tableData[$c]['DT_RowId'] = $this->toolService->filterTableDataDisplay($displayTypes[$field],$datum['[primary_key]']);
                    } else {
                        if ($field != 'DT_RowId') {
                            $tableData[$c][$field] = $this->toolService->filterTableDataDisplay($displayTypes[$field],$datum[$field]);
                        }
                    }
                }
                $c++;
            }
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $dataCount,
            'recordsFiltered' => $dataCount,
            'data' => $tableData
        ];
    }
    /**
    * @return \Illuminate\View\View
    */
    public function toolModalContent($id)
    {
        $data = [];
        if ($id) {
            $data = $this->toolService->getDataById($id)->toArray();
        }
        $cmsLang = app('ZendServiceManager')->get('MelisEngineTableCmsLang');
        return view("$this->viewNamespace::tool/modal-content",[
            'form' => $this->melisToolService->createDynamicForm(Config::get('[module_name]')['form_config'],$data),
            [lang_form]
            'langs' => $cmsLang->fetchAll()->toArray(),
            'id' => $id
        ]);

    }
    public function save()
    {
        // errors
        $errors = [];
        // success status
        $success = false;
        // default message
        $message = "tr_" . strtolower('[module_name]') ."_add_item_failed";
        // default title
        $title = "tr_" . strtolower('[module_name]') ."_title";
        // get all request parameters
        $requestParams = app('request')->request->all();
        // log type for melis logging system
        $logTypeCode = ucwords('[module_name]') . "_SAVE";
        // flash messages icon
        $icon = MelisCoreFlashMessengerService::WARNING;
        // id
        $id = null;
        $transParams  = $this->constructTransRequestParams(json_decode($requestParams['trans'],true) ?? []);
        unset($requestParams['trans']);
        $propertiesParams = $requestParams;
        // temp value for files
        if (!empty($propertiesParams['[primary_key]'])) {
            // get data from db
            $tmpData = $this->toolService->getDataById($propertiesParams['[primary_key]'])->getOriginal();
            // merge with updated date
            $updatedData = array_merge($tmpData,$propertiesParams);
            // set for update
            $propertiesParams = $updatedData;
        }
        if (!empty(app('request')->file())) {
            // set temporary value
            foreach (app('request')->file() as $field => $file) {
                $propertiesParams[$field] = "temp-value";
            }
        }
        // construct validator
        $validator = $this->toolService->constructValidator($propertiesParams,Config::get('[module_name]')['form_config']);
        if ($validator->fails()) {
            $errors = $this->formatErrorMessages($validator->errors()->toArray());
        }
        // check for errors
        if (empty($errors)) {
            // set to true
            $success = true;
            // set info icon for flash messeages
            $icon = MelisCoreFlashMessengerService::INFO;
            // check for id
            if (isset($propertiesParams['[primary_key]']) && ! empty($propertiesParams['[primary_key]'])) {
                // set id
                $id = $propertiesParams['[primary_key]'];
                // remove id from the parameters
                unset($propertiesParams['[primary_key]']);
                // set log type code
                $logTypeCode = ucwords('[module_name]') . "_UPDATE";
                // update album
                $this->toolService->save($propertiesParams,$id);
                // set message
                $message = "tr_" . strtolower('[module_name]') ."_update_item_success";
            } else {
                unset($propertiesParams['[primary_key]']);
                // save date
                $id = $this->toolService->save($propertiesParams)['id'];
                // set message
                $message = "tr_" . strtolower('[module_name]') ."_save_item_success";
            }
            // save language date
            if (! empty($transParams)) {
                $this->toolService->saveLanguageData($transParams, $id);
            }
            // upload file and update data
            if (!empty(app('request')->file())) {
                // uploaded files
                $filePaths = [];
                foreach (app('request')->file() as $field => $file) {
                    $filePaths[$field] = $this->toolService->uploadFile($id,$file);
                }
                // update
                $this->toolService->save($filePaths,$id);
            }
        }

        // add to melis flash messenger
        $this->melisToolService->addToFlashMessenger($title, $message,$icon);
        // save into melis logs
        $this->melisToolService->saveLogs($title, $message, $success, $logTypeCode, $id);

        // return required data
        return [
            'errors' => $errors,
            'success' => $success,
            'textMessage' => $message,
            'textTitle' => $title,
            'id'        => $id
        ];

    }
    private function formatErrorMessages($errorMessages)
    {
        $newTranslations = [];
        foreach ($errorMessages as $key => $trans) {
            $newTranslations[$key] = [
                'message' => $trans,
                'label'   => __("[module_name]::messages.tr_" .strtolower('[module_name]') . "_$key")
            ];
        }

        return $newTranslations;
    }
    public function delete()
    {
        // errors
        $errors = [];
        // success status
        $success = false;
        // default message
        $message = "Unable to delete";
        // default title
        $title = "tr_melis_lumen_notification_title";
        // get all request parameters
        $requestParams = app('request')->request->all();
        // log type for melis logging system
        $logTypeCode = ucwords('[module_name]') . "_DELETE";
        // flash messages icon
        $icon = MelisCoreFlashMessengerService::WARNING;
        // id
        $id = app('request')->request->get('id');

        if (empty($id)) {
            throw new \Exception('No id was passed');
        }

        if ( $this->toolService->delete($id)) {
            $success = true;
            $icon = MelisCoreFlashMessengerService::INFO;
            $message = "tr_melis_lumen_notification_message_delete_ok";
        }

        // add to melis flash messenger
        $this->melisToolService->addToFlashMessenger($title, $message,$icon);
        // save into melis logs
        $this->melisToolService->saveLogs($title, $message, $success, $logTypeCode, $id);

        return [
            'success' => $success,
            'error'   => $errors,
            'textMessage' => $message,
            'textTitle' => $title
        ];
    }
    private function constructRequestParams($requestparams)
    {
        if (empty($requestparams)) {
            throw new \Exception("Request paramaters empty");
        }
        $postParams = [];

        foreach ($requestparams as $i => $val) {
            $postParams[$val['name']] = $val['value'];
        }

        return $postParams;
    }
    private function constructTransRequestParams($requestparams)
    {
        $postParams = [];
        if (!empty($requestparams)) {
            /** @var MelisCmsLangTable $cmsLang */
            $cmsLang = app('ZendServiceManager')->get('MelisEngineTableCmsLang');
            foreach ($requestparams as $i => $val) {
                $postParams[$val['locale']] = [];
                foreach ($val['formData'] as $ii => $val2) {
                    if ($val2['name'] == '[second_table_lang_fk]') {
                        $val2['value'] = $cmsLang->getEntryByField('lang_cms_locale',$val['locale'])->current()->lang_cms_id;
                    }
                    $postParams[$val['locale']]["$val2[name]"] = $val2['value'];
                }

            }
        }

        return $postParams;
    }


}
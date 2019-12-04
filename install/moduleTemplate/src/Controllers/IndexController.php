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

        if($request->getMethod() == Request::METHOD_POST) {

            $lumenAlbumSrvc = $this->toolService;
            $tableConfig = config('[module_name]')['table_config'];
            $params = $request->request->all();
            /*
            * standard datatable configuration
            */
            $sortOrder = $params['order'][0]['dir'];
            $selCol    = $params['order'];
            $colId     = array_keys($tableConfig['table']['columns']);
            $selCol    = $colId[$selCol[0]['column']];
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
            $data = $lumenAlbumSrvc->getDataWithFilters($start,$length,$searchableCols,$search,$selCol,$sortOrder);
            // get total count of the data in the db
            $dataCount = $data['dataCount'];
            $albumData = $data['data'];
            // organized data
            $c = 0;
            foreach($albumData as $data){
                $tableData[$c]['DT_RowId'] = $data->alb_id;
                $tableData[$c]['alb_id'] = $data->alb_id;
                $tableData[$c]['alb_name'] = $data->alb_name;
                $tableData[$c]['alb_date'] = $data->alb_date;
                $tableData[$c]['alb_song_num'] = $data->alb_song_num;
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
    public function toolModalContent()
    {
        $id = app('request')->request->get('id') ?? null;
        $data = [];
        //if ($albumId) {
          //  $data = $this->toolService->getAlbumById($albumId)->toArray();
    //    }

        return view("$this->viewNamespace::tool/modal-content",[
            'form' => $this->melisToolService->createDynamicForm(Config::get('[module_name]')['form_config'],$data),
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
        $message = "tr_melis_lumen_notification_message_save_ko";
        // default title
        $title = "tr_melis_lumen_notification_title";
        // get all request parameters
        $requestParams = app('request')->request->all();
        // log type for melis logging system
        $logTypeCode = ucwords('[module_name]') . "_SAVE";
        // flash messages icon
        $icon = MelisCoreFlashMessengerService::WARNING;
        // id
        $id = null;

        // check for errors
        if (empty($errors)) {
            // set to true
            $success = true;
            // set info icon for flash messeages
            $icon = MelisCoreFlashMessengerService::INFO;
            // check for id
            if (isset($requestParams['alb_id']) && ! empty($requestParams['alb_id'])) {
                // set id
                $id = $requestParams['alb_id'];
                // remove id from the parameters
                unset($requestParams['alb_id']);
                // set log type code
                $logTypeCode = ucwords('[module_name]') . "_UPDATE";
                // update album
                $this->toolService->save($requestParams,$id);
                // set message
                $message = "tr_melis_lumen_notification_message_upate_ok";
            } else {
                $requestParams['alb_date'] = date('Y-m-d');
                // save album data
                $id = $this->toolService->save($requestParams)['id'];
                // set message
                $message = "tr_melis_lumen_notification_message_save_ok";
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
            'textTitle' => $title
        ];

    }
}
namespace LumenModule\[module_name]\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
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

    public function __construct([module_name]Service $toolService)
    {
        $this->toolService = $toolService;
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
           // 'form' => $this->melisPlatformToolService->createDynamicForm(Config::get('album_form'),$data),
             'id' => $id
        ]);

    }
}
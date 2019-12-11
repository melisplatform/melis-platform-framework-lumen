namespace LumenModule\[module_name]\Http\Service;

use Illuminate\Support\Facades\Config;
use MelisPlatformFrameworkLumen\MelisServiceProvider;
use LumenModule\[module_name]\Http\Model\[model_name];
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Doctrine\DBAL\Types\Types;

class [template_service_name]
{
    /**
     * @var string
     */
    private $toolTable;

    public function __construct([model_name] $toolModel)
    {
        $this->toolTable = $toolModel;
    }

    /**
     * fetch data from model
     *
     * @param $start
     * @param $limit
     * @param $searchableCols
     * @param $search
     * @param $orderBy
     * @param $orderDir
     * @return array
     * @throws \Exception
     */
    public function getDataWithFilters($start,$limit,$searchableCols,$search,$orderBy,$orderDir)
    {
        $data = [];
        try {
            $data = $this->toolTable::query()
                ->where(function($query) use ($searchableCols,$search){
                    if (! empty($searchableCols) && !empty($search)) {
                        foreach ($searchableCols as $idx => $col) {
                            $query->orWhere($col,"like","%$search%");
                        }
                    }
                })
                ->skip($start)
                ->limit($limit)
                ->orderBy($orderBy,$orderDir)
                ->get();


        }catch (\Exception $err) {
            // return error
            throw new \Exception($err->getMessage());
        }
        // count all with no filters
        $tmpDataCount = $this->toolTable::all()->count();
        // count data with filters
        if (! empty($searchableCols) && !empty($search)) {
            $tmpDataCount = $data->count();
        }
        return [
            'data' => $data,
            'dataCount' => $tmpDataCount
        ];

    }

    /**
     *  save tool data
     *
     * @param $data
     * @param null $id
     * @return array
     * @throws \Exception
     */
    public function save($data,$id = null)
    {
        $success = false;
        try {
            if (empty($id)){
                // insert new row
                $id = $this->toolTable::query()->insertGetId($data);
                $success = true;

            } else {
                $success = $this->toolTable::query()->where('[primary_key]',$id)->update($data);
            }
        } catch(\Exception $err) {
            throw new \Exception($err->getMessage());
        }

        return [
            'success' => $success,
            'id'      => $id
        ];
    }

    /**
     *
     * Delete an album
     *
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function delete($id)
    {
        $success = false;
        try {
            if ($id) {
                // delete album
                $success = $this->toolTable::query()->where('[primary_key]',$id)->delete();
            }
        } catch(\Exception $err) {
            // throw error
            throw new \Exception($err->getMessage());
        }

        return [
            'success' => $success,
            'id'      => $id
        ];
    }

    /**
     * @param $albumName
     * @return array
     */
    public function getEntryByName($name)
    {
       return $this->toolTable::query()->where('alb_name',$name)->first();
    }

    public function getDataById($id)
    {
        return $this->toolTable::query()->where('[primary_key]',$id)->first();
    }
    public function constructValidator($postData,$formConfig = [])
    {
        $tableFieldDataTypes = $this->getFieldDataTypes($this->toolTable->getTable());
        $formElements = $formConfig['form']['elements'];
        $translations = [];
        foreach ($formElements as $idx => $elem) {
            $name = $elem['attributes']['name'];
            // for integer
            if ($tableFieldDataTypes[$name] == Types::INTEGER) {
                $translations[$name. "." . Types::INTEGER] = __("lumenDemo::translations.tr_melis_lumen_notification_not_int");
            }
            if (isset($elem['attributes']['required']) && $elem['attributes']['required']) {
                $tableFieldDataTypes[$name] = $tableFieldDataTypes[$name] . "|required";
                $translations[$name. ".required"] = __("lumenDemo::translations.tr_melis_lumen_notification_empty");
            }
        }

        return Validator::make($postData,$tableFieldDataTypes, $translations);
    }

    public function getFieldDataTypes($tableName)
    {
        $con = Schema::connection('melis');
        $fields = [];
        // get table field data type fields
        foreach ($con->getColumnListing($tableName) as $tblField) {
            $fields[$tblField] = $con->getColumnType($tableName,$tblField);
        }

        return $fields;
    }
    public function getTableFields()
    {
        $con = Schema::connection('melis');
        $fields = [];
        // get table field data type fields
        foreach ($con->getColumnListing($this->toolTable->getTable()) as $tblField) {
            $fields[] = $tblField;
        }

        return $fields;
    }
}
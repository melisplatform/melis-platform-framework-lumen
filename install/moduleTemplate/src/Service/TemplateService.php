namespace LumenModule\[module_name]\Http\Service;

use Illuminate\Support\Facades\Config;
use MelisPlatformFrameworkLumen\MelisServiceProvider;
use LumenModule\[module_name]\Http\Model\[model_name];

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
    public function saveToolData($data,$id = null)
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
    public function deleteToolData($id)
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
    public function getEntryByName($albumName)
    {
       return $this->toolTable::query()->where('alb_name',$albumName)->first();
    }

    public function getAlbumById($id)
    {
        return $this->toolTable::query()->where('alb_id',$id)->first();
    }

}
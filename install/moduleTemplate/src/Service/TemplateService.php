namespace LumenModule\[module_name]\Http\Service;

use Illuminate\Support\Facades\Config;
use MelisPlatformFrameworkLumen\MelisServiceProvider;
use LumenModule\[module_name]\Http\Model\[model_name];
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Doctrine\DBAL\Types\Types;
use Illuminate\Support\Facades\DB;
use MelisPlatformFrameworkLumen\Service\MelisPlatformToolService;

class [template_service_name]
{
    /**
     * @var string
     */
    private $toolTable;
    /**
    * @var
    */
    private $platformToolService;

    public function __construct([model_name] $toolModel,  MelisPlatformToolService $platformToolService)
    {
        $this->toolTable = $toolModel;
        $this->platformToolService = $platformToolService;
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
                ->orderBy("[first_table]." . $orderBy,$orderDir)
                ->get();

            [second_table_data]

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
        $fieldDiff = array_diff(array_keys($tableFieldDataTypes),array_keys($postData));
        // ensure all fields are present for boolean
        if (! empty($fieldDiff)) {
            if (!isset($postData[array_values($fieldDiff)[0]]))
                $postData[array_values($fieldDiff)[0]] = false;
        }
        $formElements = $formConfig['form']['elements'];
        $tableFields = [];
        $translations = [];
        foreach ($formElements as $idx => $elem) {
            $name = $elem['name'];
            $tableFields[$name] = $tableFieldDataTypes[$name];
            if ($tableFieldDataTypes[$name] == Types::DATETIME_MUTABLE) {$tableFields[$name] = 'date_format:Y-m-d H:i:s';}
            if ($tableFieldDataTypes[$name] == Types::TEXT) {$tableFields[$name] = Types::STRING;}
            
            // for integer
            if ($tableFieldDataTypes[$name] == Types::INTEGER) {
                $translations[$name. "." . Types::INTEGER] = __("[module_name]::messages.tr_" . strtolower('[module_name]') . "_not_int");
            }
            if (isset($elem['attributes']['required']) && $elem['attributes']['required']) {
                if (isset($tableFields[$name])) {
                    $tableFields[$name] = $tableFields[$name] . "|required";
                }
                $translations[$name. ".required"] = __("[module_name]::messages.tr_" . strtolower('[module_name]') . "_empty");
            }
        }

        return Validator::make($postData,$tableFields, $translations);
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
    public function getTableFields($tableName)
    {
        $con = Schema::connection('melis');
        $fields = [];
        // get table field data type fields
        foreach ($con->getColumnListing($tableName) as $tblField) {
            $fields[] = $tblField;
        }

        return $fields;
    }
    [save_lang_data_func]
    public function getLanguageTableDataWithForm($field, $value)
    {
        // melis cms language table
        $cmsLang = app('ZendServiceManager')->get('MelisEngineTableCmsLang');
        $cmsLangData = $cmsLang->fetchAll()->toArray();
        $data = [];
        $tmpData = [];
        if (! empty($value)) {
            $tmpData = DB::connection('melis')->table('[second_table]')->select('*')
            ->whereRaw('' . $field . ' = ' . $value)
            ->get()
            ->toArray();
        }

        foreach ($cmsLangData as $i => $lang) {
            $data["" . $lang['lang_cms_locale'] . ""] = [
                'form' => $this->platformToolService->createDynamicForm(Config::get('[module_name]')['form_config']['language_form'])
            ];
            if (!empty($tmpData)) {
                foreach ($tmpData as $idx => $val) {
                    $val = (array)$val;
                    // set data if it has existing data
                    if ($val['[secondary_table_lang_fk]'] == $lang['lang_cms_id']) {
                        $data["" . $lang['lang_cms_locale'] . ""] = [
                            'form' => $this->platformToolService->createDynamicForm(Config::get('[module_name]')['form_config']['language_form'], $val)
                        ];
                    }
                }
            }
        }

        return $data;
    }
    public function filterTableDataDisplay($type, $data)
    {
        switch($type) {
        case "dot_color" :

                if ($data) {
                    $data = "<span class='fa fa-circle text-success'></span>";
                } else {
                    $data = "<span class='fa fa-circle text-success'></span>";
                }

            break;
        case "char_length_limit" :
            if (strlen($data) > 50) {
                $data = substr($data, 0,50) . " ...";
            }
            break;
            case "admin_name";
                $data = $this->getAdminNameByUserId($data) ?? $data;
            break;

            case "lang_name";
                $data = $this->getLanguageByLangId($data) ?? $data;
            break;

            case "template_name";
                $data = $this->getTemplateNameByTplId($data) ?? $data;
            break;

            case "site_name";
                $data = $this->getTemplateNameByTplId($data) ?? $data;
            break;
        }

        return $data;
    }
    public function getAdminNameByUserId($userId)
    {
        $data = null;
        $coreTable = app('ZendServiceManager')->get('MelisCoreTableUser')->getEntryById($userId)->current();
        if (! empty($coreTable)) {
            $data = $coreTable->usr_firstname . " " . $coreTable->usr_lastname;
        }

        return $data;
    }
    public function getLanguageByLangId($langId)
    {
        $data = null;
        $langData = app('ZendServiceManager')->get('MelisEngineTableCmsLang')->getEntryById($langId)->current();
        if (! empty($langData)) {
            $data = $langData->lang_cms_name;
        }

        return $data;
    }

    public function getTemplateNameByTplId($tplId)
    {
        $data = null;
        $siteData = app('ZendServiceManager')->get('MelisEngineTableSite')->getEntryById($tplId)->current();
        if (! empty($siteData)) {
            $data = $siteData->site_label;
        }

        return $data;
    }
    public function getSiteNameBySiteId($siteId)
    {
        $data = null;
        $tplData = app('ZendServiceManager')->get('MelisEngineTableSite')->getEntryById($siteId)->current();
        if (! empty($tplData)) {
            $data = $tplData->tpl_name;
        }

        return $data;
    }
    /**
    * upload file
    *
    * @param $id
    * @param $file UploadedFile
    * @return null|string
    */
    public function uploadFile($id,$file)
    {
        $fileName = "";
        // uploading directory
        $uploadDir = __DIR__ . "/../../assets/$id";
        // create directory
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir,0777);
        }
        // rename duplicate image
        $destination = $uploadDir . "/" ;
        if (file_exists($destination .  $file->getClientOriginalName())) {
            $fileName = $this->renameFileRec($destination .  $file->getClientOriginalName());
        } else {
            $fileName = $file->getClientOriginalName();
        }
        // upload file into the folder
        move_uploaded_file($file->getPathname(),$destination . $fileName);

        return "/melis/[module_name]/$id/$fileName";
    }

    /**
    *
    * rename file name
    *
    * @param $filenamePath
    * @param int $ctr
    * @return null|string
    */
    public function renameFileRec($filenamePath,$ctr = 1)
    {
        $pathInfo = pathinfo($filenamePath);
        $newFileName = null;
        if (! empty($pathInfo)) {
            $directory = $pathInfo['dirname'];
            $extension = "." .$pathInfo['extension'];
            $pathFileName = $pathInfo['filename'];
            // if the file is still exists
            // rename again and again until the file is not exists anymore
            $renamedFile = $directory . "/" . $pathFileName . "_" . $ctr . $extension;
            if (file_exists($renamedFile)) {
                $ctr++;
                // pass again the current file
                $newFileName =  $this->renameFileRec($filenamePath,$ctr);
            } else {
            // return the new file name
                $newFileName = $pathInfo['filename'] . "_" . $ctr . $extension;
            }
        }

        return $newFileName;
    }
    public function removeAllFile($dir)
    {
        $files = glob($dir . '/*'); // get all file names
        foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
        }
    }
}
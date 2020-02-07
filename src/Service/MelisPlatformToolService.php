<?php
namespace MelisPlatformFrameworkLumen\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Laravel\Lumen\Routing\Router;
use MelisCore\Service\MelisCoreFlashMessengerService;
use MelisPlatformFrameworkLumenDemoToolLogic\Model\MelisDemoAlbumTableLumen;

class MelisPlatformToolService
{
    /**
     *
     * save for melis flash messenger
     *
     * @param $title
     * @param $message
     * @param string $icon
     */
    public function addToFlashMessenger($title,$message,$icon = MelisCoreFlashMessengerService::INFO)
    {
        /** @var MelisCoreFlashMessengerService $flashMessenger */
        $flashMessenger = app('ZendServiceManager')->get('MelisCoreFlashMessenger');
        $flashMessenger->addToFlashMessenger($title, $message, $icon);
    }

    /**
     * save logs form melis-core logs
     *
     * @param $title
     * @param $message
     * @param $success
     * @param $typeCode
     * @param $itemId
     */
    public function saveLogs($title,$message,$success,$typeCode,$itemId)
    {
        $logSrv = app('ZendServiceManager')->get('MelisCoreLogService');
        $logSrv->saveLog($title, $message, $success, $typeCode, $itemId);
    }
    /**
     * Get the view of certain url
     *
     * @param $url
     * @return false|string
     */
    public function getContentByUrl($url, $method = "GET",$parameters = [])
    {
        $view = $config['view'] ?? null;
        /*
         * get the dispatcher of the application and passed a Request with url
         */
        $dispatch = app()->dispatch(Request::create($url,$method,$parameters));
        /*
         * check if the url was found the application
         */
        if ($dispatch->getStatusCode() == Response::HTTP_OK) {
            // return the content
            return $dispatch->getContent();
        }

        return null;
    }

    /**
     *
     * create a dynamic form based from a config
     * - also can set value to the inputs
     *
     * @param $formConfig
     * @param array $data
     * @return string
     */
    public function createDynamicForm($formConfig,$data = [])
    {
        $htmlForm = "";
        $formAttributes = "";
        $formElements = "";
        if(!empty($formConfig)) {
            if ($this->checkArraykey('form',$formConfig)) {
                // check for form attributes
                if ($this->checkArraykey('attributes',$formConfig['form'])) {
                    foreach ($formConfig['form']['attributes'] as $idx => $val) {
                        $formAttributes .= $idx . "='" . $val . "' ";
                    }
                }
                // put form attributes
                $htmlForm.= "<form " . $formAttributes .">";
                // check form elements
                if ($this->checkArraykey('elements',$formConfig['form'])) {
                    foreach ($formConfig['form']['elements'] as $idx => $elements) {
                        // through this key the element show only when no data passed
                        if(!isset($elements['hideNoData'])) {
                            // create form element
                            $formElements .= $this->createElement($elements, $data);
                        } else {
                            if (! empty($data)) {
                                $formElements .= $this->createElement($elements,$data);
                            }
                        }

                    }
                }
                // construct form
                $htmlForm .= $formElements . "</form>";
            }
        }

        return $htmlForm;
    }

    /**
     * validate key in an array
     *
     * @param $key
     * @param $array
     * @return bool
     */
    private function checkArraykey($key,$array)
    {
        if (isset($array[$key]) && $array[$key]) {
            return true;
        }

        return false;
    }

    /**
     *
     * creating a form ELEMENT based from a config
     *
     *
     * @param $elements
     * @param array $data
     * @return string
     */
    protected function createElement($elements, $data = [])
    {
        // input types
        $formInputsType = [
            'text',
            'radio',
            'password',
            'hidden',
            'checkbox',
            'file',
        ];
        // declrations
        $elementAttrb = "";
        $toolTip = "";
        $label = "";
        $element = "";
        // check element type
        if ($this->checkArraykey('type',$elements) ) {
            // cehck for element attributes
            if ($this->checkArraykey('attributes',$elements)) {
                foreach ($elements['attributes'] as $idx => $val) {
                    $elementAttrb .= $idx . "='" . $val . "' ";
                }
            }
            // tooltip
            if ($this->checkArraykey('tooltip',$elements)) {
                $toolTip = '<i class="fa fa-info-circle fa-lg float-right tip-info" data-toggle="tooltip" data-placement ="left" data-original-title="' . $elements['tooltip'] . '"></i>';
            }
            // check required attribute
            $required = null;
            if (isset($elements['attributes']['required']) && $elements['attributes']['required']) {
                $required = "*";
            }
            //label
            if ($this->checkArraykey('label',$elements)) {
                $label = "<label class='d-flex flex-row justify-content-between'>" . ($elements['label'] ?? null) . " " . $required  . " " . $toolTip ."</label>";
            }
            // for inputs
            $value = isset($data[$elements['attributes']['name']]) ? "value='". $data[$elements['attributes']['name']] ."'" : null;
            if ($this->checkArraykey('type',$elements)) {
                if (in_array($elements['type'] ?? null, $formInputsType)) {
                    // construct form inputs
                    $element = "<input type='" .  $elements['type'] . "' " . $elementAttrb . "" .  $value .  " />";
                } else if ($elements['type'] == 'textarea') {
                    // construct textarea
                    $element = "<textarea  style='resize: vertical;' " . $elementAttrb . "> " . $value . " </textarea>";
                } else if ($elements['type'] == 'select') {
                    if ($this->checkArraykey('options',$elements)) {
                        $options = "";
                        foreach($elements['options'] as $idx => $val){
                            $options.= "<option value='" . $idx ."'>" . $val . "</option>";
                        }
                    }
                    $element = "<select " . $elementAttrb .">" . $options ."</select>";
                }
            }

        }
        // set form elements
        return "<div class='form-group'>" . $label . " " . $element ."</div>";
    }

}
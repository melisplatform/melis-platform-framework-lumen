<?php
namespace MelisPlatformFrameworkLumen\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Laravel\Lumen\Routing\Router;
use MelisCore\Service\MelisCoreFlashMessengerService;
use MelisPlatformFrameworkLumenDemoToolLogic\Model\MelisDemoAlbumTableLumen;

class MelisPlatformToolLumenService
{
    /**
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
    public function createDynamicForm($formConfig,$data = [])
    {
        $formInputsType = [
            'text',
            'radio',
            'password',
            'hidden',
            'checkbox',
            'file',
        ];
        $htmlForm = "";
        if(!empty($formConfig)) {
            if ($this->checkArraykey('form',$formConfig)) {
                // check for form attributes
                $formAttributes = "";
                if ($this->checkArraykey('attributes',$formConfig['form'])) {
                    foreach ($formConfig['form']['attributes'] as $idx => $val) {
                        $formAttributes .= $idx . "='" . $val . "' ";
                    }
                }
                // put attributes
                $htmlForm.= "<form " . $formAttributes .">";
                $formElements = "";
                // check for form elements
                if ($this->checkArraykey('elements',$formConfig['form'])) {
                    foreach ($formConfig['form']['elements'] as $idx => $elements) {
                        // check element type
                        if ($this->checkArraykey('type',$elements)) {
                            $elementAttrb = "";
                            // cehck for element attributes
                            if ($this->checkArraykey('attributes',$elements)) {
                                foreach ($elements['attributes'] as $idx => $val) {
                                    $elementAttrb .= $idx . "=" . $val . " ";
                                }
                            }
                            if ($this->checkArraykey('tooltip',$elements))
                                $toolTip = '<i class="fa fa-info-circle fa-lg pull-right tip-info" data-toggle="tooltip" data-placement ="left" data-original-title="' . $elements['tooltip'] . '"></i>';

                            if ($this->checkArraykey('label',$elements))
                                $label = "<label>" . ($elements['label'] ?? null) . " " . $toolTip ."</label>";

                            // for inputs
                            if (in_array($elements['type'] ?? null, $formInputsType)) {
                                if ($this->checkArraykey('type',$elements))
                                    $input = "<input type='" .  $elements['type'] . "' " . $elementAttrb . "/>";
                            }

                            $formElements .= "<div class='form-group'>" . $label . " " . $input ."</div>";
                        }
                    }
                }

                $htmlForm .= $formElements . "</form>";
            }
        }

        return $htmlForm;
    }

    private function checkArraykey($key,$array)
    {
        if (isset($array[$key]) && $array[$key]) {
            return true;
        }

        return false;
    }

}
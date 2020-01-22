<?php
namespace MelisPlatformFrameworkLumen\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Laravel\Lumen\Routing\Router;
use MelisAssetManager\Service\MelisConfigService;
use MelisCore\Service\MelisCoreFlashMessengerService;
use MelisPlatformFrameworkLumenDemoToolLogic\Model\MelisDemoAlbumTableLumen;
use Zend\Form\Element;

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
                            if (! empty($data) && !empty($elements['hideNoData'])) {
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
        if ($this->checkArraykey('type',$elements)) {
            $element = $this->renderMelisElement($elements);
        }
        // set form elements
       return $element;
    }

    public function getMelisElementTypes()
    {
        /** @var MelisConfigService $configSvc */
        $configSvc = app('ZendServiceManager')->get('MelisConfig');
        $toolCreatorTypesSelect = $configSvc->getItem('/melistoolcreator/forms/melistoolcreator_step5_form/elements');
        $melisElementTypes = [];
        // check for db table column types field
        foreach ($toolCreatorTypesSelect as $idx => $element) {
            if ($element['spec']['name'] == "tcf-db-table-col-type") {
                $melisElementTypes = array_keys($element['spec']['options']['value_options']);
            }
        }

        return $melisElementTypes;
    }
    public function renderMelisElement($elementConfig)
    {
        /** @var Element $element */
        $element = app('ZendServiceManager')->get("FormElementManager")->get($elementConfig['type']);
        // set options
        if ($this->checkArraykey('options',$elementConfig)) {
            $element->setOptions($elementConfig['options']);
        }
        // set attributes
        if ($this->checkArraykey('attributes',$elementConfig)) {
            $element->setAttributes($elementConfig['attributes']);
        }
        // set name
        if ($this->checkArraykey('name',$elementConfig)) {
            $element->setName($elementConfig['name']);
        }
        // render element
        return app('ZendServiceManager')->get('ViewHelperManager')->get('MelisFieldRow')($element);
    }

}
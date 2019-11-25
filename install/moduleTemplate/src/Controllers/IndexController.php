namespace LumenModule\[module_name]\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class IndexController extends BaseController
{
    /**
    * default module namespace
    * @var string
    */
    private $viewNamespace = "[module_name]";

    public function renderIndex()
    {
        return view($this->viewNamespace . "::tool/index");
    }
}
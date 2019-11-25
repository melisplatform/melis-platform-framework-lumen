<?php
namespace MelisPlatformFrameworkLumen\Controllers;

use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller;
use MelisPlatformFrameworkLumen\Service\MelisLumenModuleService;

class CreateLumenModuleController extends Controller
{
    /**
     * @var
     */
    private $moduleService;

    public function __construct(MelisLumenModuleService $lumenModuleService)
    {
        $this->moduleService = $lumenModuleService;
    }

    public function createModule($moduleName)
    {
        $validator = Validator::make(['module_name' => $moduleName],[
            'module_name' => 'alpha_num'
        ]);

        if($validator->fails()) {
           die($validator->errors()->first());
        }

        $this->moduleService->createModule($moduleName);
    }
}
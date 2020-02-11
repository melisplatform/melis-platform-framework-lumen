use LumenModule\[module_name]\Http\Controllers\IndexController;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$moduleName = strtolower('[module_name]');
Route::get('/melis/' . $moduleName . '/tool',  IndexController::class ."@renderIndex");
// get datatable data
Route::post('/melis/' . $moduleName . '/get-table-data', IndexController::class ."@getTableData");
// get modal
// get album form
Route::get('/melis/' . $moduleName . '/form/{id}', IndexController::class . "@toolModalContent");
// save album data
Route::post('/melis/' . $moduleName . '/save' , IndexController::class . "@save" );
// delete album
Route::post('/melis/' . $moduleName . '/delete' , IndexController::class . "@delete" );





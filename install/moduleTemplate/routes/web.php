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

Route::get('/melis/[module_name]-tool',  IndexController::class ."@renderIndex");
// get datatable data
Route::get('/melis/[module_name]/get-table-data', IndexController::class ."@getTableData");







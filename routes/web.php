<?php
use MelisPlatformFrameworkLumen\Controllers\CreateLumenModuleController;

Route::get("/melis/lumen/create-module/{moduleName}", CreateLumenModuleController::class . "@createModule");


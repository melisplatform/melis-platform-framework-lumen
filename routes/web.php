<?php
use MelisPlatformFrameworkLumen\Controllers\CreateLumenModuleController;

Route::get("/melis/lumen-module-create", CreateLumenModuleController::class . "@createModule");


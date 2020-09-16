<?php


$isCliReqs = php_sapi_name() == 'cli' ? true : false;
//third party Lumen
$thirdPartyFolder = !$isCliReqs ? $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'thirdparty/Lumen' : 'thirdparty/Lumen';
    
// lumen storage dir
if (!is_dir($thirdPartyFolder)) {
    // download lumen skeleton from marketplace
    $message = MelisPlatformFrameworks\Support\MelisPlatformFrameworks::downloadFrameworkSkeleton('lumen');
    // make sure storage dir is writable
    exec("chmod -R 0777 " . getcwd() . '/thirdparty/Lumen/storage/');

    return $message;

} else {

    return [
        'success' => true,
        'message' => 'Lumen skeleton downloaded successfully'
    ];
}

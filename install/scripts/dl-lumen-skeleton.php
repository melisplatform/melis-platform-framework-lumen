<?php


$isCliReqs = php_sapi_name() == 'cli' ? true : false;
//third party Lumen
$thirdPartyFolder = !$isCliReqs ? $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'thirdparty/Lumen' : 'thirdparty/Lumen';

if (!is_dir($thirdPartyFolder)) {
    return MelisPlatformFrameworks\Support\MelisPlatformFrameworks::downloadFrameworkSkeleton('lumen');
}else{
    return [
        'success' => true,
        'message' => 'Lumen skeleton downloaded successfully'
    ];
}
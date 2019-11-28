$namespace = '[module_name]'; ?>
<!-- header area -->
@include($namespace . "::tool/header")

Main Content Index

<[?]= app('melisdatatable')->createTable(config('[module_name]')['table_config']['table']) [?]>
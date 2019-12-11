$namespace = '[module_name]';
$lowerCase = strtolower($namespace);?>
<!-- header area -->
@include($namespace . "::tool/header")

<div class="innerAll spacing-x2">
    <[?]= app('melisdatatable')->createTable(config('[module_name]')['table_config']['table']) [?]>
</div>

<!-- temp modal -->
@include($namespace . "::tool/tmp-modal")

<script>
    if (typeof {{ $lowerCase }}Tool == "undefined") {
        $("body").append('<script src="/melis/[module_name]/js/tool.js">');
    }
</script>


return array(
    'table' => array(
        'ajaxUrl' => '/melis/[module_name]/get-table-data',
        'dataFunction' => '',
        'ajaxCallback' => '',
        'attributes' => [
            'id' => '[module_name]ToolTable',
            'class' => 'table table-stripes table-primary dt-responsive nowrap',
            'cellspacing' => '0',
            'width' => '100%',
        ],
        'filters' => array(
            'left' => array(
                'show' => "l",
            ),
            'center' => array(
                'search' => "f"
            ),
            'right' => array(
                'refresh' => '<div class="lumen-table-refresh"><a class="btn btn-default melis-lumen-refresh" data-toggle="tab" aria-expanded="true" title="' . __("[module_name]::translations.tr_melis_lumen_table_refresh") .'"><i class="fa fa-refresh"></i></a></div>'
            ),
        ),
        'columns' => [tool_columns]
        'searchables' => [tool_searchables]
        'actionButtons' => array(
            'edit' => "<a href=\"#modal-template-manager-actions\" data-toggle=\"modal\" data-target=\"#lumenModal\" class=\"btn btn-success btnEditLumenAlbum\" title=\"" . __("lumenDemo::translations.tr_melis_lumen_table_edit") ."\"> <i class=\"fa fa-pencil\"> </i> </a>\t",
            'delete' => "<a class=\"btn btn-danger btnDelLumenAlbum\" title=\"" . __("lumenDemo::translations.tr_melis_lumen_table_delete")  ."\" > <i class=\"fa fa-times\"> </i> </a>"
        ),
    ),
);


$smModuleName = strtolower('[module_name]');

return array(
    'table' => array(
        'ajaxUrl' => '/melis/' . $smModuleName .'/get-table-data',
        'dataFunction' => '',
        'ajaxCallback' => '',
        'attributes' => [
            'id' => $smModuleName . 'ToolTable',
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
                'refresh' => '<div class="lumen-table-refresh"><a class="btn btn-default melis-lumen-refresh" data-toggle="tab" aria-expanded="true" title="' . __("[module_name]::messages.tr_" . $smModuleName . "_common_refresh") .'"><i class="fa fa-refresh"></i></a></div>'
            ),
        ),
        'columns' => [tool_columns]
        'searchables' => [tool_searchables]
        'actionButtons' => array(
            'edit' => "<a href=\"#modal-template-manager-actions\" [tool_type] class=\"btn btn-success edit-".  $smModuleName  . "\" title=\"" . __("[module_name]::messages.tr_" . $smModuleName . "_common_edit") ."\"> <i class=\"fa fa-pencil\"> </i> </a>\t",
            'delete' => "<a class=\"btn btn-danger delete-" . $smModuleName . "\" title=\"" . __("[module_name]::messages.tr_" . $smModuleName . "_common_delete")  ."\" > <i class=\"fa fa-times\"> </i> </a>"
        ),
    ),
);

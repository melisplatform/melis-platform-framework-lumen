
$smModuleName = strtolower('[module_name]');
$icon = "plus";
$text =  '[module_name]::messages.tr_' . $smModuleName . '_common_add';
if ($id) {
    $icon = "pencil";
    $text = '[module_name]::messages.tr_' . $smModuleName . '_common_edit';
}
?>
<div class="widget-head">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#" class="glyphicons {{ $icon  }}" data-toggle="tab" aria-expanded="true"><i></i>{{ __($text) }}</a></li>
    </ul>
</div>
<div class="widget-body innerAll inner-2x">
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="main-content">
               <?= $form?>
                <br>
                <div align="right">
                    <button data-dismiss="modal" class="btn btn-danger pull-left lumen-modal-close" ><i class="fa fa-times"></i> <?= __('lumenDemo::translations.tr_common_close')?></button>
                    <button type="submit" class="btn btn-success" id="save-<?= $smModuleName ?>"><i class="fa fa-save"></i>  <?= __('lumenDemo::translations.tr_common_save')?></button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
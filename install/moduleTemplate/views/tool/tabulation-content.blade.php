

$smModuleName = strtolower('[module_name]');
$icon = "plus";
$text =  '[module_name]::messages.tr_' . $smModuleName . '_common_add';
if ($id) {
    $icon = "pencil";
    $text = '[module_name] / ' .  $id;
}
?>
@php
    $itemId = $id ?? 0;
@endphp
<div class="me-heading bg-white border-bottom">
    <div class="row">
        <div class="me-hl col-xs-8 col-sm-8 col-md-8">
            <h1 class="content-heading">{{  ($itemId) ? $text : __($text)}}</h1>
        </div>
        <div class="me-hl col-xs-4 col-sm-4 col-md-4">
            <button class="btn btn-success pull-right" id="save-<?= $smModuleName ?>" data-id="{{ $itemId }}" data-target="<?= $smModuleName . $itemId?>"><i class="fa fa-save"></i> {{ __('lumenDemo::translations.tr_common_save') }}</button>
        </div>
    </div>
</div>
<div class="widget widget-tabs widget-tabs-double-2 widget-tabs-responsive">
    <div class="widget-head nav">
        <ul class="tabs-label nav-tabs">
            <li class="active">
                <a href="#laravel-tool-tab-{{ $itemId }}" class="glyphicons tag" data-toggle="tab" aria-expanded="true"><i></i>
                    <span>Properties</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="tab-content innerAll spacing-x2 laravel-form-container-{{ $itemId }}">
    <div class="tab-pane active" id="laravel-tool-tab-{{ $itemId }}">
        <div class="row">
            <div class="col-md-12">
                <div id="<?= $smModuleName . $itemId?>">
                    <?= $form ?>
                </div>
            </div>
        </div>
    </div>
</div>



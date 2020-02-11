
$smModuleName = strtolower('[module_name]');
$icon = "plus";
$text =  '[module_name]::messages.tr_' . $smModuleName . '_common_add';
if ($id) {
    $icon = "pencil";
    $text = '[module_name]::messages.tr_' . $smModuleName . '_common_edit';
}
$itemId = $id ?? 0;
$toolHasLanguageTable = [tool_has_lang_table] ?? 0;
?>
<div class="widget-head">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#property_form" class="glyphicons {{ $icon  }}" data-toggle="tab" aria-expanded="true"><i></i>{{ __($text) }}</a></li>
        @if ($toolHasLanguageTable)
            <li>
                <a href="#language_form" class="glyphicons font" data-toggle="tab" aria-expanded="true"><i></i>
                    <span>Languages</span>
                </a>
            </li>
        @endif
    </ul>
</div>
<div class="widget-body innerAll inner-2x">
    <div class="tab-content">
        <div class="tab-pane active" id="property_form">
            <div class="main-content">
               <?= $form?>
                <br>
                <div class="clearfix"></div>
            </div>
        </div>
        @if ($toolHasLanguageTable)
            <div class="tab-pane" id="language_form">
                <div class="row">
                    <div class="col-xs-12 col-md-4">
                        <ul class="nav-tabs product-text-tab">
                            @foreach($langs As $key => $lang)
                                <li class="{{ ($key) ? '':'active' }}" style="margin-bottom: 6px;">
                                    <a class="clearfix btn-block" data-toggle="tab" href="#{{ $smModuleName }}-text-translation-{{ $lang['lang_cms_locale'] }}" aria-expanded="false" >
                                        @php
                                            $langLabel = '<span>'. $lang['lang_cms_name'] .'</span>';
                                            $moduleSvc = app('ZendServiceManager')->get('ModulesService');
                                            if (file_exists($moduleSvc->getModulePath('MelisCms').'/public/images/lang-flags/'.$lang['lang_cms_locale'].'.png')){
                                                $langLabel .= '<span class="pull-right"><img src="/MelisCms/images/lang-flags/'.$lang['lang_cms_locale'].'.png"></span>';
                                            }
                                        @endphp
                                        {!! $langLabel !!}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-xs-12 col-md-8">
                        <div class="tab-content">
                            @foreach($langs As $key => $lang)
                                <div id="{{ $smModuleName }}-text-translation-{{ $lang['lang_cms_locale'] }}" data-lang='{{ $lang['lang_cms_locale'] }}' class="tab-pane {{ ($key) ? '':'active' }} {{ $smModuleName }}-text-translation" data-langid="{{ $lang['lang_cms_locale']  }}">
                                    <div id="<?= $smModuleName . $itemId?>">
                                        <?= $langForm[$lang['lang_cms_locale']]['form']; ?>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div align="right">
            <button data-dismiss="modal" class="btn btn-danger pull-left lumen-modal-close" ><i class="fa fa-times"></i> <?= __('lumenDemo::translations.tr_common_close')?></button>
            <button type="submit" class="btn btn-success" id="save-<?= $smModuleName ?>"><i class="fa fa-save"></i>  <?= __('lumenDemo::translations.tr_common_save')?></button>
        </div>
    </div>
</div>
<script>
    $("#property_form .tip-info").parent().addClass('d-flex flex-row justify-content-between');
    $("#language_form .tip-info").parent().addClass('d-flex flex-row justify-content-between');
</script>

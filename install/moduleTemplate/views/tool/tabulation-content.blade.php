

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
    $toolHasLanguageTable = [tool_has_lang_table];
@endphp
<div class="me-heading bg-white border-bottom">
    <div class="row">

        <div class="me-hl col-xs-8 col-sm-8 col-md-8">
            <h1 class="content-heading">{{  ($itemId) ? __("[module_name]::messages.tr_" . $smModuleName . "_title") . " / " . $itemId : __($text)}}</h1>
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
                <a href="#{{ $smModuleName }}-tool-tab-{{ $itemId }}" class="glyphicons tag" data-toggle="tab" aria-expanded="true"><i></i>
                    <span>Properties</span>
                </a>
            </li>
            @if ($toolHasLanguageTable)
                <li>
                    <a href="#{{ $smModuleName }}-tool-lang-tab-{{ $itemId }}" class="glyphicons font" data-toggle="tab" aria-expanded="true"><i></i>
                        <span>Languages</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
<div class="tab-content innerAll spacing-x2 {{ $smModuleName }}-form-container-{{ $itemId }}">
    <div class="tab-pane active" id="{{ $smModuleName }}-tool-tab-{{ $itemId }}">
        <div class="row">
            <div class="col-md-12">
                <div id="<?= $smModuleName . $itemId?>">
                    <?= $form ?>
                </div>
            </div>
        </div>
    </div>
    @if ($toolHasLanguageTable)
        <div class="tab-pane" id="{{ $smModuleName }}-tool-lang-tab-{{ $itemId }}">
            <div class="row">
                <div class="col-xs-12 col-md-3">
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
                <div class="col-xs-12 col-md-9">
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

</div>



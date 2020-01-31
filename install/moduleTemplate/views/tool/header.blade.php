<[?]php
    $smToolname = strtolower('[module_name]');
[?]>
<div class="me-heading bg-white border-bottom">
    <div class="row">
        <div class="me-hl col-xs-12 col-md-9">
            <h1 class="content-heading">{{ __('[module_name]::messages.tr_' . $smToolname . '_title') }}</h1>
            <p>{{ __('[module_name]::messages.tr_' . $smToolname . '_desc') }} </p>
        </div>
        <div class="me-hl col-xs-12 col-md-3" align="right">
            <a  [tool_type]  class="btn btn-success add-{{ $smToolname }}" title="{{ __('[module_name]::messages.tr_' . $smToolname . '_common_add') }}">
                <i class="fa fa-plus"></i>
                {{ __('[module_name]::messages.tr_' . $smToolname . '_common_add') }}
            </a>
        </div>
    </div>
</div>

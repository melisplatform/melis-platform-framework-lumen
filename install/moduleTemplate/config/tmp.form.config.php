
$moduleName = strtolower('[module_name]');
return [
    'form' => [
        'attributes' => [
            'class' => $moduleName .'form',
            'method' => 'POST',
            'name'  => $moduleName . 'form',
            'id'    => $moduleName . "form",
            'enctype' => 'multipart/form-data'
        ],
        'elements' => [
            [elements]
        ],
    ],
    [language_form]
];
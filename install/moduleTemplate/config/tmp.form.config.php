
$moduleName = strtolower('[module_name]');
return [
    'form' => [
        'attributes' => [
            'class' => $moduleName .'form',
            'method' => 'POST',
            'name'  => $moduleName . 'form',
            'id'    => $moduleName . "form"
        ],
        'elements' => [
            [elements]
        ],
    ]
];
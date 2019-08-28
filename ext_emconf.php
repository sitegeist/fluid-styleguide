<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Fluid Styleguide',
    'description' => 'Living styleguide for Fluid Components',
    'category' => 'fe',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'beta',
    'uploadfolder' => false,
    'clearCacheOnLoad' => false,
    'version' => '0.4.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.9.99',
            'fluid_components' => '1.3.0'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ]
];

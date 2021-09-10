<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Fluid Styleguide',
    'description' => 'Living styleguide for Fluid Components',
    'category' => 'fe',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => false,
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.9.99',
            'fluid_components' => '3.0.0-3.99.99',
            'php' => '7.4.0-8.9.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'classmap' => [
            'Resources/Private/Php/Parsedown.php'
        ],
        'psr-4' => [
            'Sitegeist\\FluidStyleguide\\' => 'Classes'
        ]
    ],
];

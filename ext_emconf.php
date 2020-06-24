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
    'version' => '1.6.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.9.99',
            'fluid_components' => '1.3.0-2.9.99',
            'php' => '7.2.0-7.9.99'
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

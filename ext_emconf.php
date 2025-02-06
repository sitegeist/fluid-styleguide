<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Fluid Styleguide',
    'description' => 'Living styleguide for Fluid Components',
    'category' => 'fe',
    'author' => 'Ulrich Mathes, Simon Praetorius',
    'author_email' => 'mathes@sitegeist.de, moin@praetorius.me',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'stable',
    'version' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'fluid_components' => '3.0.0-3.99.99',
            'php' => '8.2.0-8.3.99'
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

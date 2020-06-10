<?php

return [
    'frontend' => [
        'sitegeist/fluid-styleguide/router' => [
            'target' => \Sitegeist\FluidStyleguide\Middleware\StyleguideRouter::class,
            'after' => [
                'typo3/cms-frontend/site'
            ],
            'before' => [
                (version_compare(TYPO3_version, '10.0', '<'))
                    ? 'typo3/cms-frontend/static-route-resolver'
                    : 'typo3/cms-frontend/authentication'
            ]
        ],
    ],
];

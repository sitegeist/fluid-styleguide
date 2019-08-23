<?php

return [
    'frontend' => [
        'sitegeist/fluid-styleguide/router' => [
            'target' => \Sitegeist\FluidStyleguide\Middleware\StyleguideRouter::class,
            'after' => [
                'typo3/cms-redirects/redirecthandler'
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver'
            ]
        ],
    ],
];

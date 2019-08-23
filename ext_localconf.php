<?php

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Sitegeist.FluidStyleguide',
        'FluidStyleguide',
        [
            'Styleguide' => 'list, show, downloadComponentZip, component'
        ],
        // non-cacheable actions
        [
            'Styleguide' => 'list, show, downloadComponentZip, component'
        ]
    );

    // Register component namespace
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces']['Sitegeist\\FluidStyleguide\\Components'] =
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('fluid_styleguide', 'Resources/Private/Components');
});

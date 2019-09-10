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

    // Make components available in all fluid templates
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fsc'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fsc'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['fsc'][] = 'Sitegeist\\FluidStyleguide\\Components';

});

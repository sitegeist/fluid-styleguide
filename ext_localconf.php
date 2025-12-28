<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    // Register component namespace
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces']['Sitegeist\\FluidStyleguide\\Components'][] =
        ExtensionManagementUtility::extPath('fluid_styleguide', 'Resources/Private/Components');
});

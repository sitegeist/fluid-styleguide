<?php

call_user_func(function () {
    // Register component namespace
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces']['Sitegeist\\FluidStyleguide\\Components'] =
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('fluid_styleguide', 'Resources/Private/Components');

    if (version_compare(TYPO3_version, '10.0', '<')) {
        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher->connect(
            \Sitegeist\FluidStyleguide\Controller\StyleguideController::class,
            'postProcessComponentView',
            \Sitegeist\FluidStyleguide\EventListener\AssetCollectorExtensionInjector::class,
            'injectJsAndCssFromAssetCollectorExtension'
        );
    }
});

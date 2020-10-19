<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\EventListener;

use B13\Assetcollector\AssetCollector;
use Sitegeist\FluidStyleguide\Event\PostProcessComponentViewEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AssetCollectorExtensionInjector
{
    public function injectJsAndCssFromAssetCollectorExtension(PostProcessComponentViewEvent $event, string $eventName = ''): void
    {
        if (!ExtensionManagementUtility::isLoaded('assetcollector')) {
            return;
        }

        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);

        // Add external css files
        foreach ($assetCollector->getExternalCssFiles() as $cssFile) {
            $event->addHeaderData(
                '<link rel="stylesheet" href="' . $cssFile['fileName'] . '" type="' . $cssFile['mediaType'] . '" />'
            );
        }

        // Add inline css
        $event->addHeaderData($assetCollector->buildInlineCssTag());

        // Add javascript files
        $event->addFooterData($assetCollector->buildJavaScriptIncludes());
    }
}

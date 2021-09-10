<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\EventListener;

use Sitegeist\FluidStyleguide\Event\PostProcessComponentViewEvent;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

final class AssetCollectorInjector
{
    public function injectJsAndCssFromAssetCollector(PostProcessComponentViewEvent $event, string $eventName = ''): void
    {
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);

        // Add css files to head
        foreach ($assetCollector->getStyleSheets() as $assetData) {
            $assetData['attributes']['href'] = $this->getAbsoluteWebPath($assetData['source']);
            $assetData['attributes']['rel'] = $assetData['attributes']['rel'] ?? 'stylesheet';
            $assetData['attributes']['type'] = $assetData['attributes']['type'] ?? 'text/css';
            $event->addHeaderData(
                '<link ' . GeneralUtility::implodeAttributes($assetData['attributes'], true) . ' />'
            );
        }

        // Add inline css to head
        foreach ($assetCollector->getInlineStyleSheets() as $assetData) {
            $event->addHeaderData(
                '<style ' . GeneralUtility::implodeAttributes($assetData['attributes'], true) . '>'
                . $assetData['source'] . '</style>'
            );
        }

        // Add script tags to head or body
        foreach ($assetCollector->getJavaScripts() as $assetData) {
            $assetData['attributes']['src'] = $this->getAbsoluteWebPath($assetData['source']);
            $scriptTag = '<script ' . GeneralUtility::implodeAttributes($assetData['attributes'], true) . '></script>';
            if ($assetData['options']['priority']) {
                $event->addHeaderData($scriptTag);
            } else {
                $event->addFooterData($scriptTag);
            }
        }

        // Add inline javascript to head or body
        foreach ($assetCollector->getInlineJavaScripts() as $assetData) {
            $scriptTag = '<script ' . GeneralUtility::implodeAttributes($assetData['attributes'], true) . '>'
                . $assetData['source'] . '</script>';
            if ($assetData['options']['priority']) {
                $event->addHeaderData($scriptTag);
            } else {
                $event->addFooterData($scriptTag);
            }
        }
    }

    private function getAbsoluteWebPath(string $file): string
    {
        if (strpos($file, '://') !== false || strpos($file, '//') === 0) {
            return $file;
        }
        $file = PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($file));
        return GeneralUtility::createVersionNumberedFilename($file);
    }
}

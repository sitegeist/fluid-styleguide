<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Uri;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
class StyleguideViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('action', 'string', 'Action name', true);
        $this->registerArgument('arguments', 'array', 'Action arguments', false, []);
        $this->registerArgument('section', 'string', 'the anchor to be added to the URI', false, '');
        $this->registerArgument('relative', 'bool', 'generate a relative path', false, true);
    }

    /**
     * Renders markdown code in fluid templates
     */
    public function render(): UriInterface
    {
        $prefix = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('fluid_styleguide', 'uriPrefix');
        $prefix = rtrim((string) $prefix, '/') . '/';
        $baseUrl = static::getCurrentSite()->getBase();
        // reset scheme and host to return a relative path
        if ($this->arguments['relative'] === true) {
            return $baseUrl
                ->withScheme('')
                ->withHost('')
                ->withPath($prefix . $this->arguments['action'])
                ->withQuery(http_build_query($this->arguments['arguments']))
                ->withFragment($this->arguments['section']);
        }
        return $baseUrl
            ->withPath($prefix . $this->arguments['action'])
            ->withQuery(http_build_query($this->arguments['arguments']))
            ->withFragment($this->arguments['section'])
            ->withPort(GeneralUtility::getIndpEnv('TYPO3_PORT') ?: null);
    }

    /**
     * Returns the current Site object to create urls
     */
    protected static function getCurrentSite(): SiteInterface
    {
        return $GLOBALS['TYPO3_CURRENT_SITE'];
    }
}

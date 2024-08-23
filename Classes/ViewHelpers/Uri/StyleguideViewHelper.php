<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Uri;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class StyleguideViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

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
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): UriInterface {
        $prefix = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('fluid_styleguide', 'uriPrefix');
        $prefix = rtrim((string) $prefix, '/') . '/';

        $baseUrl = static::getCurrentSite()->getBase();

        // reset scheme and host to return a relative path
        if ($arguments['relative'] === true) {
            return $baseUrl
                ->withScheme('')
                ->withHost('')
                ->withPath($prefix . $arguments['action'])
                ->withQuery(http_build_query($arguments['arguments']))
                ->withFragment($arguments['section']);
        }

        return $baseUrl
            ->withPath($prefix . $arguments['action'])
            ->withQuery(http_build_query($arguments['arguments']))
            ->withFragment($arguments['section'])
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

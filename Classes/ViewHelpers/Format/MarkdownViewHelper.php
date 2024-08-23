<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class MarkdownViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected static \Parsedown $markdownParser;

    /**
     * Don't escape markdown html
     */
    protected $escapeOutput = false;

    /**
     * Don't escape input html
     */
    protected $escapeChildren = false;

    /**
     * Renders markdown code in fluid templates
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        if (!isset(static::$markdownParser)) {
            static::$markdownParser = new \Parsedown();
        }

        return static::$markdownParser->text($renderChildrenClosure());
    }
}

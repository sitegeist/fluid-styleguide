<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MarkdownViewHelper extends AbstractViewHelper
{
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
    public function render(): string
    {
        if (!isset(static::$markdownParser)) {
            static::$markdownParser = new \Parsedown();
        }
        return static::$markdownParser->text($this->renderChildren());
    }
}

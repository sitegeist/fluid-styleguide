<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Event;

use Sitegeist\FluidStyleguide\Domain\Model\Component;

final class PostProcessComponentViewEvent
{
    /**
     * Component that has been rendered
     *
     * @var string
     */
    private $component;

    /**
     * Name of the component fixture that has been used
     *
     * @var string
     */
    private $fixtureName;

    /**
     * Form data that has been entered by the user in the styleguide
     *
     * @var array
     */
    private $formData;

    /**
     * Rendered component html that will be displayed in the iframe
     *
     * @var string
     */
    private $renderedView;

    /**
     * Markup that should be added to the <head> part
     *
     * @var string[]
     */
    private $headerData = [];

    /**
     * Markup that should be added to the end of the <body> part
     *
     * @var string[]
     */
    private $footerData = [];

    public function __construct(Component $component, string $fixtureName, array $formData, string $renderedView)
    {
        $this->component = $component;
        $this->fixtureName = $fixtureName;
        $this->formData = $formData;
        $this->renderedView = $renderedView;
    }

    public function getComponent(): Component
    {
        return $this->component;
    }

    public function getFixtureName(): string
    {
        return $this->fixtureName;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getRenderedView(): string
    {
        return $this->renderedView;
    }

    public function setRenderedView(string $markup): void
    {
        $this->renderedView = $markup;
    }

    public function getHeaderData(): array
    {
        return $this->headerData;
    }

    public function setHeaderData(array $headerData): void
    {
        $this->headerData = $headerData;
    }

    public function addHeaderData(string $headerData): void
    {
        $this->headerData[] = $headerData;
    }

    public function getFooterData(): array
    {
        return $this->footerData;
    }

    public function setFooterData(array $footerData): void
    {
        $this->footerData = $footerData;
    }

    public function addFooterData(string $footerData): void
    {
        $this->footerData[] = $footerData;
    }
}

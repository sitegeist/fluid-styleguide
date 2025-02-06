<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Component;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;
use Sitegeist\FluidStyleguide\Exception\RequiredComponentArgumentException;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

class ExampleViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('component', Component::class, 'Component that should be rendered', true);
        $this->registerArgument('fixtureName', 'string', 'Name of the fixture that should be used in the example');
        $this->registerArgument('fixtureData', 'array', 'Additional dynamic fixture data that should be used in the example');
        $this->registerArgument('context', 'string', 'The context (html markup) in which the component should be displayed in the styleguide', false, '|');
        $this->registerArgument(
            'applyContextFromFixture',
            'bool',
            'Component context from fixture data (styleguideComponentContext property) will overrule context specified in ViewHelper call',
            false,
            false
        );
        $this->registerArgument('execute', 'bool', 'Set to true if the component example should be executed', false, false);
        $this->registerArgument('handleExceptions', 'bool', 'Handle exceptions that occur during execution of the example', false, false);
    }

    /**
     * Renders fluid example code for the specified component
     */
    public function render(): string
    {
        if (!isset($this->arguments['fixtureName']) && !isset($this->arguments['fixtureData'])) {
            throw new \InvalidArgumentException(sprintf(
                'A fixture name or fixture data has to be specified to render the component example of %s.',
                $this->arguments['component']->getName()->getIdentifier()
            ), 1566377563);
        }
        $fixtureData = $this->arguments['fixtureData'] ?? [];
        $componentContext = $this->arguments['context'];
        if (isset($this->arguments['fixtureName'])) {
            $componentFixture = $this->arguments['component']->getFixture($this->arguments['fixtureName']);
            if (!$componentFixture) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid fixture name "%s" specified for component %s.',
                    $this->arguments['fixtureName'],
                    $this->arguments['component']->getName()->getIdentifier()
                ), 1566377564);
            }

            // Merge static fixture data with manually edited data
            $fixtureData = array_replace($componentFixture->getData(), $fixtureData);

            // Overrule component context if specified in fixture data
            if ($this->arguments['applyContextFromFixture'] && isset($fixtureData['styleguideComponentContext'])) {
                $componentContext = $fixtureData['styleguideComponentContext'];
            }
            unset($fixtureData['styleguideComponentContext']);
        }
        if ($this->arguments['execute']) {
            try {
                // Parse fluid code in fixtures
                $fixtureData = self::renderFluidInExampleData($fixtureData, $this->renderingContext);

                $componentMarkup = self::renderComponent(
                    $this->arguments['component'],
                    $fixtureData,
                    $this->renderingContext
                );

                $componentWithContext = self::applyComponentContext(
                    $componentMarkup,
                    $componentContext,
                    $this->renderingContext,
                    array_replace(
                        $this->arguments['component']->getDefaultValues(),
                        $fixtureData
                    )
                );
            } catch (\Exception $e) {
                if ($this->arguments['handleExceptions']) {
                    return sprintf(
                        'Exception: %s (#%d %s)',
                        $e->getMessage(),
                        $e->getCode(),
                        $e::class
                    );
                } else {
                    throw $e;
                }
            }
        } else {
            $componentMarkup = static::renderComponentTag(
                $this->arguments['component']->getName(),
                $fixtureData
            );

            $componentWithContext = self::applyComponentContext(
                $componentMarkup,
                $componentContext
            );
        }
        return $componentWithContext;
    }

    /**
     * Calls a component with the supplied example data
     */
    public static function renderComponent(
        Component $component,
        array $data,
        RenderingContextInterface $renderingContext
    ): string {
        // Check if all required arguments were supplied to the component
        foreach ($component->getArguments() as $expectedArgument) {
            if ($expectedArgument->isRequired() && !isset($data[$expectedArgument->getName()])) {
                throw new RequiredComponentArgumentException(sprintf(
                    'Required argument "%s" was not supplied for component %s.',
                    $expectedArgument->getName(),
                    $component->getName()->getIdentifier()
                ), 1566636254);
            }
        }

        return ComponentRenderer::renderComponent(
            $data,
            fn() => '',
            $renderingContext,
            $component->getName()->getIdentifier()
        );
    }

    /**
     * Renders inline fluid code in a fixture array that will be provided as example data to a component
     */
    public static function renderFluidInExampleData(array $data, RenderingContextInterface $renderingContext): array
    {
        return array_map(function ($value) use ($renderingContext) {
            if (is_string($value)) {
                return $renderingContext->getTemplateParser()->parse($value)->render($renderingContext);
            } else {
                return $value;
            }
        }, $data);
    }

    /**
     * Renders fluid code of a component call
     */
    public static function renderComponentTag(ComponentName $componentName, array $data): string
    {
        $fluidComponent = new TagBuilder($componentName->getTagName());
        $data = array_map([static::class, 'encodeFluidVariable'], $data);

        if (isset($data['content'])) {
            $fluidComponent->setContent($data['content']);
            unset($data['content']);
        }

        $fluidComponent->addAttributes($data, false);

        return $fluidComponent->render();
    }

    /**
     * Wraps component markup in the specified component context (HTML markup)
     * The component markup will replace all pipe characters (|) in the context string
     * Optionally, a renderingContext and template data can be provided, in which case
     * the context markup will be treated as fluid markup
     */
    public static function applyComponentContext(
        string $componentMarkup,
        string $context,
        RenderingContextInterface $renderingContext = null,
        array $data = []
    ): string {
        // Check if the context should be fetched from a file
        $context = self::checkObtainComponentContextFromFile($context);

        if (isset($renderingContext)) {
            // Use unique value as component markup marker
            $marker = '###COMPONENT_MARKUP_' . mt_rand() . '###';
            $context = str_replace('|', $marker, $context);

            // Parse fluid tags in context string
            $originalVariableContainer = $renderingContext->getVariableProvider();
            $renderingContext->setVariableProvider(new StandardVariableProvider($data));
            $context = $renderingContext->getTemplateParser()->parse($context)->render($renderingContext);
            $renderingContext->setVariableProvider($originalVariableContainer);

            // Wrap component markup
            return str_replace($marker, $componentMarkup, $context);
        } else {
            return str_replace('|', $componentMarkup, $context);
        }
    }

    /**
     * Checks if the provided component context is a file path and returns its contents;
     * falls back to the specified context string.
     */
    protected static function checkObtainComponentContextFromFile(string $context): string
    {
        // Probably not a file path
        if (str_contains($context, '|')) {
            return $context;
        }

        // Check if the value is a valid file
        $path = GeneralUtility::getFileAbsFileName($context);
        if (!file_exists($path)) {
            return $context;
        }

        return file_get_contents($path);
    }

    /**
     * Encodes a fluid variable for use in component/viewhelper call
     */
    public static function encodeFluidVariable(mixed $input, bool $isRoot = true): string
    {
        if (is_array($input)) {
            $fluidArray = [];
            foreach ($input as $key => $value) {
                $fluidArray[] = (string) $key . ': ' . static::encodeFluidVariable($value, false);
            }
            return '{' . implode(', ', $fluidArray) . '}';
        }

        if (is_string($input) && !$isRoot) {
            return "'" . addcslashes($input, "'") . "'";
        }

        if (is_bool($input)) {
            return ($input) ? 'TRUE' : 'FALSE';
        }

        if ($input instanceof \UnitEnum) {
            return $input::class . '::' . $input->name;
        }

        return (string) $input;
    }
}

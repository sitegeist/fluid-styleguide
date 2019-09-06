<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Component;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;
use Sitegeist\FluidStyleguide\Exception\RequiredComponentArgumentException;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ExampleViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('component', Component::class, 'Component that should be rendered', true);
        $this->registerArgument('fixtureName', 'string', 'Name of the fixture that should be used in the example');
        $this->registerArgument('fixtureData', 'array', 'Additional dynamic fixture data that should be used in the example');
        $this->registerArgument('execute', 'bool', 'Set to true if the component example should be executed', false, false);
        $this->registerArgument('handleExceptions', 'bool', 'Handle exceptions that occur during execution of the example', false, false);
    }

    /**
     * Renders fluid example code for the specified component
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        if (!isset($arguments['fixtureName']) && !isset($arguments['fixtureData'])) {
            throw new \InvalidArgumentException(sprintf(
                'A fixture name or fixture data has to be specified to render the component example of %s.',
                $arguments['component']->getName()->getIdentifier()
            ), 1566377563);
        }

        $fixtureData = $arguments['fixtureData'] ?? [];

        if (isset($arguments['fixtureName'])) {
            $componentFixture = $arguments['component']->getFixture($arguments['fixtureName']);
            if (!$componentFixture) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid fixture name "%s" specified for component %s.',
                    $arguments['fixtureName'],
                    $arguments['component']->getName()->getIdentifier()
                ), 1566377564);
            }

            // Merge static fixture data with manually edited data
            $fixtureData = array_replace($componentFixture->getData(), $fixtureData);
        }

        if ($arguments['execute']) {
            try {
                // Parse fluid code in fixtures
                $fixtureData = self::renderFluidInExampleData($fixtureData, $renderingContext);

                return self::renderComponent(
                    $arguments['component'],
                    $fixtureData,
                    $renderingContext
                );
            } catch (\Exception $e) {
                if ($arguments['handleExceptions']) {
                    return sprintf(
                        'Exception: %s (#%d %s)',
                        $e->getMessage(),
                        $e->getCode(),
                        get_class($e)
                    );
                } else {
                    throw $e;
                }
            }
        } else {
            return static::renderComponentTag(
                $arguments['component']->getName(),
                $fixtureData
            );
        }
    }

    /**
     * Calls a component with the supplied example data
     *
     * @param Component $component
     * @param array $data
     * @param RenderingContextInterface $renderingContext
     * @return string
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
            function () {
                return '';
            },
            $renderingContext,
            $component->getName()->getIdentifier()
        );
    }

    /**
     * Renders inline fluid code in a fixture array that will be provided as example data to a component
     *
     * @param array $data
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public static function renderFluidInExampleData(array $data, RenderingContextInterface $renderingContext)
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
     *
     * @param ComponentName $componentName
     * @param array $data
     * @return string
     */
    public static function renderComponentTag(ComponentName $componentName, array $data): string
    {
        $fluidComponent = new TagBuilder($componentName->getTagName());
        $data = array_map([static::class, 'encodeFluidVariable'], $data);

        if (isset($data['content'])) {
            $fluidComponent->setContent($data['content']);
            unset($data['content']);
        }

        $fluidComponent->addAttributes($data);

        return $fluidComponent->render();
    }

    /**
     * Encodes a fluid variable for use in component/viewhelper call
     *
     * @param $input mixed
     * @param $isRoot bool
     * @return string
     */
    public static function encodeFluidVariable($input, bool $isRoot = true): string
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

        return (string) $input;
    }
}

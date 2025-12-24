<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Component;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Repository\ComponentRepository;
use Sitegeist\FluidStyleguide\Exception\RequiredComponentArgumentException;
use Sitegeist\FluidStyleguide\Service\StyleguideConfigurationManager;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class IncludeFixtureViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('component', 'string', 'Name of the component that should be rendered', true);
        $this->registerArgument('fixtureName', 'string', 'Name of the fixture that the component should be rendered with', false, 'default');
        $this->registerArgument('fixtureData', 'array', 'Additional dynamic fixture data that should be used');
    }

    /**
     * Renders fluid example code for the specified component
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): array {

        $componentIdentifier = self::sanitizeComponentIdentifier($arguments['component'] ?? '');

        $componentRepository = GeneralUtility::makeInstance(ComponentRepository::class);

        $component = $componentRepository->findWithFixturesByIdentifier($componentIdentifier);
        if (!$component) {
            return sprintf('Component %s not found', $componentIdentifier);
        }

        if (!isset($arguments['fixtureName']) && !isset($arguments['fixtureData'])) {
            throw new \InvalidArgumentException(sprintf(
                'A fixture name or fixture data has to be specified to render the component %s.',
                $arguments['component']
            ), 1566377563);
        }

        $fixtureData = $arguments['fixtureData'] ?? [];

        $fixtureName = self::sanitizeFixtureName($arguments['fixtureName'] ?? 'default');

        if (isset($fixtureName)) {
            $componentFixture = $component->getFixture($fixtureName);
            if (!$componentFixture) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid fixture name "%s" specified for component %s.',
                    $fixtureName,
                    $componentIdentifier
                ), 1566377564);
            }

            // Merge static fixture data with manually edited data
            $fixtureData = array_replace($componentFixture->getData(), $fixtureData);
        }

        $renderingContext->getViewHelperResolver()->addNamespace('fsv', 'Sitegeist\FluidStyleguide\ViewHelpers');

        // Parse fluid code in fixtures
        $fixtureData = self::renderFluidInExampleData($fixtureData, $renderingContext);

        return $fixtureData;
    }

    /**
     * Make sure that the component identifier doesn't include any malicious characters
     *
     * @param string $componentIdentifier
     * @return string
     */
    protected static function sanitizeComponentIdentifier(string $componentIdentifier): string
    {
        return trim(preg_replace('#[^a-z0-9_\\\\]#i', '', $componentIdentifier), '\\');
    }

    /**
     * Make sure that the fixture name doesn't include any malicious characters
     *
     * @param string $fixtureName
     * @return string
     */
    protected static function sanitizeFixtureName(string $fixtureName): string
    {
        return preg_replace('#[^a-z0-9_]#i', '', $fixtureName);
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
     * @param mixed $data
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderFluidInExampleData($data, RenderingContextInterface $renderingContext)
    {
        if (is_string($data)) {
            return $renderingContext->getTemplateParser()->parse($data)->render($renderingContext);
        } elseif (is_array($data)) {
            return array_map(function ($value) use ($renderingContext) {
                return self::renderFluidInExampleData($value, $renderingContext);
            }, $data);
        } else {
            return $data;
        }
    }

    /**
     * Wraps component markup in the specified component context (HTML markup)
     * The component markup will replace all pipe characters (|) in the context string
     * Optionally, a renderingContext and template data can be provided, in which case
     * the context markup will be treated as fluid markup
     *
     * @param string $componentMarkup
     * @param string $context
     * @param RenderingContextInterface $renderingContext
     * @param array $data
     * @return string
     */
    public static function applyComponentContext(
        string $componentMarkup,
        string $context,
        ?RenderingContextInterface $renderingContext = null,
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
     *
     * @param string $context
     * @return string
     */
    protected static function checkObtainComponentContextFromFile(string $context): string
    {
        // Probably not a file path
        if (strpos($context, '|') !== false) {
            return $context;
        }

        // Check if the value is a valid file
        $path = GeneralUtility::getFileAbsFileName($context);
        if (!file_exists($path)) {
            return $context;
        }

        return file_get_contents($path);
    }
}

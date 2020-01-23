<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sitegeist\FluidStyleguide\Controller\StyleguideController;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use Sitegeist\FluidStyleguide\View\StandaloneView;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class StyleguideRouter implements MiddlewareInterface
{
    const DEFAULT_ACTION = 'list';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context
     */
    protected $context;

    public function __construct()
    {
        $this->context = GeneralUtility::makeInstance(Context::class);
    }


    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $GLOBALS['TYPO3_CURRENT_SITE'] = $site = $request->getAttribute('site', null);

        $prefix = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('fluid_styleguide', 'uriPrefix');
        $prefixWithoutSlash = rtrim($prefix, '/');
        $prefix = $prefixWithoutSlash . '/';

        // Check if fluid styleguide should be rendered
        if (strpos($request->getUri()->getPath(), $prefixWithoutSlash) !== 0) {
            return $handler->handle($request);
        }

        // Correct calls without trailing slash in request url
        if (strpos($request->getUri()->getPath(), $prefix) !== 0) {
            return new RedirectResponse(
                $request->getUri()->withPath($prefix . static::DEFAULT_ACTION)
            );
        }

        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // Extract routing information from URI
        $path = substr($request->getUri()->getPath(), strlen($prefix));
        $pathSegments = explode('/', $path);
        $actionName = array_shift($pathSegments) ?? '';
        $actionName = preg_replace('#[^a-z]#i', '', $actionName);

        // Redirect to default action
        if ($actionName === '') {
            return new RedirectResponse(
                $request->getUri()->withPath($prefix . static::DEFAULT_ACTION)
            );
        }

        // Create controller
        $controller = $this->objectManager->get(StyleguideController::class);
        $controller->setRequest($request);

        // Validate controller action
        $actionMethod = $actionName . 'Action';
        if (!method_exists($controller, $actionMethod)) {
            throw new \Exception(
                'Invalid styleguide action name: ' . $actionName,
                1566584663
            );
        }

        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $this->context,
            $GLOBALS['TYPO3_CURRENT_SITE'],
            $request->getAttribute('language', $site->getDefaultLanguage()),
            $request->getAttribute('routing', null),
            $request->getAttribute('frontend.user', null)
        );


        // Create view
        $view = $this->createView('fluidStyleguide', 'Styleguide', $actionName);
        $controller->initializeView($view);

        // Call action
        $actionArguments = array_replace(
            $request->getQueryParams() ?? [],
            $request->getParsedBody() ?? []
        );
        $response = $this->callControllerAction($controller, $actionMethod, $actionArguments);

        // Normalize response
        if (!$response instanceof ResponseInterface) {
            if (!isset($response)) {
                $response = $view->render();
            }
            $response = new HtmlResponse((string) $response);
        }

        return $response;
    }

    protected function createView(
        string $extensionName,
        string $controllerName,
        string $actionName
    ): StandaloneView {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->getRenderingContext()->getControllerContext()->getRequest()
            ->setControllerExtensionName($extensionName);
        $view->getRenderingContext()->setControllerName($controllerName);
        $view->getRenderingContext()->setControllerAction($actionName);
        return $view;
    }

    protected function callControllerAction(
        object $controller,
        string $actionMethod,
        array $actionArguments
    ) {

        $reflectionService = $this->objectManager->get(ReflectionService::class);
        $methodDefinition = $reflectionService
            ->getClassSchema(get_class($controller))
            ->getMethod($actionMethod);

        if ($methodDefinition instanceof \TYPO3\CMS\Extbase\Reflection\ClassSchema\Method) {
            $methodParameters = $methodDefinition->getParameters();
            $mappedActionArguments = [];
            foreach ($methodParameters as $parameterName => $parameterInfo) {
                $defaultValue = $parameterInfo->hasDefaultValue() ? $parameterInfo->getDefaultValue() : null;
                $mappedActionArguments[] = $actionArguments[$parameterName] ?? $defaultValue;
            }
        } else {
            $methodParameters = $methodDefinition['params'];
            $mappedActionArguments = [];
            foreach ($methodParameters as $parameterName => $parameterInfo) {
                $defaultValue = $parameterInfo['hasDefaultValue'] === true ? $parameterInfo['defaultValue'] : null;
                $mappedActionArguments[] = $actionArguments[$parameterName] ?? $defaultValue;
            }
        }

        return $controller->$actionMethod(...$mappedActionArguments);
    }
}

<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Handler\Exception\Handler\HttpMethodNotAllowedExceptionHandler;
use LDL\Http\Router\Handler\Exception\Handler\HttpRouteNotFoundExceptionHandler;
use LDL\Http\Router\Handler\Exception\Handler\InvalidContentTypeExceptionHandler;
use LDL\Http\Router\Router;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserCollection;
use LDL\Http\Router\Middleware\MiddlewareInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Psr\Container\ContainerInterface;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepository;
use LDL\Http\Router\Route\Dispatcher\AbstractRouteDispatcher;

class Dispatcher extends AbstractRouteDispatcher
{
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParams=null
    ) : ?array
    {
        return [
            'name' => $urlParams->get('urlName')
        ];
    }
}

class Dispatcher2 extends AbstractRouteDispatcher
{
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters = null
    ): ?array
    {
        throw new \InvalidArgumentException('test');
    }
}

class TestExceptionHandler extends AbstractExceptionHandler
{
    public function handle(
        Router $router,
        \Exception $e,
        string $context,
        ParameterBag $urlParameters = null
    ): ?int
    {
        if(!$e instanceof InvalidArgumentException){
            return null;
        }

        return ResponseInterface::HTTP_CODE_FORBIDDEN;
    }
}

class PreDispatch implements MiddlewareInterface
{
    public function getNamespace(): string
    {
        return 'PreDispatchNamespace';
    }

    public function getName(): string
    {
        return 'PreDispatchName';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 1;
    }

    public function dispatch(
        RouteInterface $route,
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $parameterBag=null
    ) : ?array
    {
        return ['pre dispatch result!'];
    }
}

class PostDispatch implements MiddlewareInterface
{
    public function getPriority() : int
    {
        return 1;
    }

    public function getNamespace(): string
    {
        return 'PostDispatchNamespace';
    }

    public function getName(): string
    {
        return 'PostDispatchName';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function dispatch(
        RouteInterface $route,
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $parameterBag=null
    ) : ?array
    {
        return ['post dispatch result!'];
    }
}

/**
 * Class ConfigParser
 *
 * Useful for plugin developers to implement a custom route configuration
 */
class ConfigParser implements RouteConfigParserInterface
{
    public function parse(
        array $config,
        RouteInterface $route,
        ContainerInterface $container = null,
        string $file=null
    ): void
    {
        if(!array_key_exists('customConfig', $config)){
            return;
        }

        $route->getConfig()->getPreDispatchMiddleware()->append(new PreDispatch());
        $route->getConfig()->getPostDispatchMiddleware()->append(new PostDispatch());
    }
}

$routerExceptionHandlers = new ExceptionHandlerCollection();

$routerExceptionHandlers->append(new HttpMethodNotAllowedExceptionHandler('http.method.not.allowed'))
->append(new HttpRouteNotFoundExceptionHandler('http.route.not.found'))
->append(new InvalidContentTypeExceptionHandler('http.invalid.content'));

$parserCollection = new RouteConfigParserCollection();
$parserCollection->append(new ConfigParser());

$response = new Response();

$router = new Router(
    Request::createFromGlobals(),
    $response,
    $routerExceptionHandlers,
    new ResponseParserRepository()
);

$router->getRouteDispatcherRepository()
    ->append(new Dispatcher('dispatcher'))
    ->append(new Dispatcher2('dispatcher2'));


$routeExceptionHandlers = new ExceptionHandlerCollection();
$routeExceptionHandlers->append(new TestExceptionHandler('test.exception.handler'));

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    $router,
    null,
    $parserCollection,
    $routeExceptionHandlers
);

$group = new RouteGroup('Test Group', 'test', $routes);

$router->addGroup($group);

$router->dispatch()->send();

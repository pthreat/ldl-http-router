<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Router;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\RouteDataInterface;

class RouterDispatcher {

    private $staticRouteMap;
    private $variableRouteData;

    private $router;

    public $matchedRoute;

    /**
     * RouterDispatcher constructor.
     *
     * @param RouteDataInterface $data
     * @param Router $router
     */
    public function __construct(
        RouteDataInterface $data,
        Router $router
    )
    {
        $this->staticRouteMap = $data->getStaticRoutes();

        $this->variableRouteData = $data->getVariableRoutes();

        $this->router = $router;
    }

    /**
     * Dispatch a route for the given HTTP Method / URI.
     *
     * @param $httpMethod
     * @param $uri
     * @return void
     */
    public function dispatch(string $httpMethod, string $uri)
    {
        $result = [];
        /**
         * @var Route $route
         */
        [$route, $filters, $vars] = $this->dispatchRoute($httpMethod, trim($uri, '/'));

        if($route) {
            $this->router->setCurrentRoute($route);
        }

        $request = $this->router->getRequest();
        $response = $this->router->getResponse();
        $parser = $route->getConfig()->getResponseParser();

        $preDispatch = $this->router->getPreDispatchMiddleware()->dispatch(
            $route,
            $request,
            $response
        );

        if(count($preDispatch)){
            $result['router']['pre'] = $preDispatch;
        }

        $httpStatusCode = $response->getStatusCode();

        if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
            $response->setContent($parser->parse($result));
            return;
        }

        $result['route'] = $route->dispatch($this->router->getRequest(), $this->router->getResponse(), $vars);

        $postDispatch = $this->router->getPostDispatchMiddleware()->dispatch(
            $route,
            $this->router->getRequest(),
            $this->router->getResponse()
        );

        if(count($postDispatch)){
            $result['router']['post'] = $postDispatch;
        }

        $response->setContent($parser->parse($result));
    }

    /**
     * Perform the route dispatching. Check static routes first followed by variable routes.
     *
     * @param $httpMethod
     * @param $uri
     * @throws Exception\HttpRouteNotFoundException
     */
    private function dispatchRoute($httpMethod, $uri)
    {
        if (isset($this->staticRouteMap[$uri]))
        {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        }

        return $this->dispatchVariableRoute($httpMethod, $uri);
    }

    /**
     * Handle the dispatching of static routes.
     *
     * @param $httpMethod
     * @param $uri
     * @return mixed
     * @throws Exception\HttpMethodNotAllowedException
     */
    private function dispatchStaticRoute($httpMethod, $uri)
    {
        $routes = $this->staticRouteMap[$uri];

        if (!isset($routes[$httpMethod]))
        {
            $httpMethod = $this->checkFallbacks($routes, $httpMethod);
        }

        return $routes[$httpMethod];
    }

    /**
     * Check fallback routes: HEAD for GET requests followed by the ANY attachment.
     *
     * @param $routes
     * @param $httpMethod
     * @throws Exception\HttpMethodNotAllowedException
     */
    private function checkFallbacks($routes, $httpMethod)
    {
        $additional = array(Route::ANY);

        if($httpMethod === Route::HEAD)
        {
            $additional[] = Route::GET;
        }

        foreach($additional as $method)
        {
            if(isset($routes[$method]))
            {
                return $method;
            }
        }

        $this->matchedRoute = $routes;

        throw new HttpMethodNotAllowedException('Allow: ' . implode(', ', array_keys($routes)));
    }

    /**
     * Handle the dispatching of variable routes.
     *
     * @param $httpMethod
     * @param $uri
     * @throws Exception\HttpMethodNotAllowedException
     * @throws Exception\HttpRouteNotFoundException
     */
    private function dispatchVariableRoute($httpMethod, $uri)
    {
        foreach ($this->variableRouteData as $data)
        {
            if (!preg_match($data['regex'], $uri, $matches))
            {
                continue;
            }

            $count = count($matches);

            while(!isset($data['routeMap'][$count++]));

            $routes = $data['routeMap'][$count - 1];

            if (!isset($routes[$httpMethod]))
            {
                $httpMethod = $this->checkFallbacks($routes, $httpMethod);
            }

            foreach (array_values($routes[$httpMethod][2]) as $i => $varName)
            {
                if(!isset($matches[$i + 1]) || $matches[$i + 1] === '')
                {
                    unset($routes[$httpMethod][2][$varName]);
                }
                else
                {
                    $routes[$httpMethod][2][$varName] = $matches[$i + 1];
                }
            }

            return $routes[$httpMethod];
        }

        throw new HttpRouteNotFoundException('Route ' . $uri . ' does not exist');
    }
}
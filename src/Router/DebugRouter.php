<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Router;

use DebugBar\DebugBar;
use Joomla\Router\ResolvedRoute;
use Joomla\Router\Route;
use Joomla\Router\RouterInterface;

/**
 * Debug router
 */
class DebugRouter implements RouterInterface
{
    /**
     * Application debug bar
     *
     * @var  DebugBar
     */
    private $debugBar;
/**
     * The delegated router
     *
     * @var  RouterInterface
     */
    private $router;
/**
     * Router constructor.
     *
     * @param   RouterInterface  $router    The delegated router
     * @param   DebugBar         $debugBar  Application debug bar
     */
    public function __construct(RouterInterface $router, DebugBar $debugBar)
    {
        $this->debugBar = $debugBar;
        $this->router   = $router;
    }

    /**
     * Add a route to the router.
     *
     * @param   Route  $route  The route definition
     *
     * @return  $this
     */
    public function addRoute(Route $route): RouterInterface
    {
        $this->router->addRoute($route);
        return $this;
    }

    /**
     * Add an array of route maps or objects to the router.
     *
     * @param   Route[]|array[]  $routes  A list of route maps or Route objects to add to the router.
     *
     * @return  $this
     *
     * @throws  \UnexpectedValueException  If missing the `pattern` or `controller` keys from the mapping array.
     */
    public function addRoutes(array $routes): RouterInterface
    {
        $this->router->addRoutes($routes);
        return $this;
    }

    /**
     * Get the routes registered with this router.
     *
     * @return  Route[]
     */
    public function getRoutes(): array
    {
        return $this->router->getRoutes();
    }

    /**
     * Parse the given route and return the information about the route, including the controller assigned to the route.
     *
     * @param   string  $route   The route string for which to find and execute a controller.
     * @param   string  $method  Request method to match, should be a valid HTTP request method.
     *
     * @return  ResolvedRoute
     *
     * @throws  Exception\MethodNotAllowedException if the route was found but does not support the request method
     * @throws  Exception\RouteNotFoundException if the route was not found
     */
    public function parseRoute($route, $method = 'GET')
    {
        /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
        $collector = $this->debugBar['time'];
        $label     = 'parsing route ' . $route . ' (' . $method . ')';
        $collector->startMeasure($label);
        try {
            $resolved = $this->router->parseRoute($route, $method);
        } finally {
            $collector->stopMeasure($label);
        }

        return $resolved;
    }

    /**
     * Add a GET route to the router.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function get(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->get($pattern, $controller, $rules, $defaults);
        return $this;
    }

    /**
     * Add a POST route to the router.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function post(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->post($pattern, $controller, $rules, $defaults);
        return $this;
    }

    /**
     * Add a PUT route to the router.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function put(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->put($pattern, $controller, $rules, $defaults);
        return $this;
    }

    /**
     * Add a DELETE route to the router.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function delete(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->delete($pattern, $controller, $rules, $defaults);
        return $this;
    }

    /**
     * Add a HEAD route to the router.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function head(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->head($pattern, $controller, $rules, $defaults);
        return $this;
    }

    /**
     * Add a OPTIONS route to the router.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function options(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->options($pattern, $controller, $rules, $defaults);
        return $this;
    }

    /**
     * Add a TRACE route to the router.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function trace(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->trace($pattern, $controller, $rules, $defaults);
        return $this;
    }

    /**
     * Add a PATCH route to the router.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function patch(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->patch($pattern, $controller, $rules, $defaults);
        return $this;
    }

    /**
     * Add a route to the router that accepts all request methods.
     *
     * @param   string  $pattern     The route pattern to use for matching.
     * @param   mixed   $controller  The controller to map to the given pattern.
     * @param   array   $rules       An array of regex rules keyed using the route variables.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     *
     * @return  $this
     */
    public function all(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface
    {
        $this->router->all($pattern, $controller, $rules, $defaults);
        return $this;
    }
}

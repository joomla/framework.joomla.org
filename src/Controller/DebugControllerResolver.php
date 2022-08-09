<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller;

use DebugBar\DebugBar;
use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Router\ResolvedRoute;

/**
 * Debug controller resolver
 */
class DebugControllerResolver implements ControllerResolverInterface
{
    /**
     * The delegated controller resolver
     *
     * @var  ControllerResolverInterface
     */
    private $controllerResolver;
/**
     * Application debug bar
     *
     * @var  DebugBar
     */
    private $debugBar;
/**
     * Controller resolver constructor.
     *
     * @param   ControllerResolverInterface  $controllerResolver  The delegated router
     * @param   DebugBar                     $debugBar            Application debug bar
     */
    public function __construct(ControllerResolverInterface $controllerResolver, DebugBar $debugBar)
    {
        $this->controllerResolver = $controllerResolver;
        $this->debugBar           = $debugBar;
    }

    /**
     * Resolve the controller for a route
     *
     * @param   ResolvedRoute  $route  The route to resolve the controller for
     *
     * @return  callable
     *
     * @throws  \InvalidArgumentException
     */
    public function resolve(ResolvedRoute $route): callable
    {
        /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
        $collector = $this->debugBar['time'];
        $label     = 'resolving controller';
        $collector->startMeasure($label);
        try {
            $resolved = $this->controllerResolver->resolve($route);
        } finally {
            $collector->stopMeasure($label);
        }

        return $resolved;
    }
}

<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Renderer;

use Fig\Link\{
	GenericLinkProvider, Link
};
use Joomla\Application\AbstractApplication;
use Psr\Link\EvolvableLinkProviderInterface;
use Symfony\Component\Asset\Packages;

/**
 * Twig runtime class
 *
 * @since  1.0
 */
class FrameworkTwigRuntime
{
	/**
	 * Application object
	 *
	 * @var    AbstractApplication
	 * @since  1.0
	 */
	private $app;

	/**
	 * Packages object to look up asset paths
	 *
	 * @var    Packages
	 * @since  1.0
	 */
	private $packages;

	/**
	 * Constructor
	 *
	 * @param   AbstractApplication  $app       The application object
	 * @param   Packages             $packages  Packages object to look up asset paths
	 *
	 * @since   1.0
	 */
	public function __construct(AbstractApplication $app, Packages $packages)
	{
		$this->app      = $app;
		$this->packages = $packages;
	}

	/**
	 * Get the URI for an asset
	 *
	 * @param   string  $path         A public path
	 * @param   string  $packageName  The name of the asset package to use
	 *
	 * @return  string
	 *
	 * @since   1.0
	 * @note    Do not typehint $packageName until PHP 7.1 is the minimum because the underlying implementation depends on a null value
	 */
	public function getAssetUri(string $path, $packageName = null) : string
	{
		return $this->packages->getUrl($path, $packageName);
	}

	/**
	 * Retrieves the current URI
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getRequestUri() : string
	{
		return $this->app->get('uri.request');
	}

	/**
	 * Get the URI for a route
	 *
	 * @param   string  $route  Route to get the path for
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getRouteUri(string $route = '') : string
	{
		return $this->app->get('uri.base.path') . $route;
	}

	/**
	 * Get the full URL for a route
	 *
	 * @param   string  $route  Route to get the URL for
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getRouteUrl(string $route = '') : string
	{
		return $this->app->get('uri.base.host') . $this->getRouteUri($route);
	}

	/**
	 * Preload a resource
	 *
	 * @param   string  $uri  The URI for the resource to preload
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function preloadAsset(string $uri) : string
	{
		/** @var EvolvableLinkProviderInterface $linkProvider */
		$linkProvider = $this->app->input->getRaw('_links', new GenericLinkProvider);
		$this->app->input->set('_links', $linkProvider->withLink(new Link('preload', $uri)));

		return $uri;
	}
}

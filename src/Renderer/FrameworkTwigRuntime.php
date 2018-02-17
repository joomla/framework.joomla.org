<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Renderer;

use Joomla\Application\AbstractApplication;
use Joomla\Preload\PreloadManager;
use Symfony\Component\Asset\Packages;

/**
 * Twig runtime class
 */
class FrameworkTwigRuntime
{
	/**
	 * Application object
	 *
	 * @var  AbstractApplication
	 */
	private $app;

	/**
	 * Packages object to look up asset paths
	 *
	 * @var  Packages
	 */
	private $packages;

	/**
	 * The HTTP/2 preload manager
	 *
	 * @var  PreloadManager
	 */
	private $preloadManager;

	/**
	 * Constructor
	 *
	 * @param   AbstractApplication  $app             The application object
	 * @param   Packages             $packages        Packages object to look up asset paths
	 * @param   PreloadManager       $preloadManager  The HTTP/2 preload manager
	 */
	public function __construct(AbstractApplication $app, Packages $packages, PreloadManager $preloadManager)
	{
		$this->app            = $app;
		$this->packages       = $packages;
		$this->preloadManager = $preloadManager;
	}

	/**
	 * Get the URI for an asset
	 *
	 * @param   string  $path         A public path
	 * @param   string  $packageName  The name of the asset package to use
	 *
	 * @return  string
	 *
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
	 */
	public function getRouteUrl(string $route = '') : string
	{
		return $this->app->get('uri.base.host') . $this->getRouteUri($route);
	}

	/**
	 * Preload a resource
	 *
	 * @param   string  $uri         The URI for the resource to preload
	 * @param   string  $linkType    The preload method to apply
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  string
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function preloadAsset(string $uri, string $linkType = 'preload', array $attributes = []): string
	{
		// Make sure the preload method is supported, special case for `dns-prefetch` to convert it to the right method name
		if ($linkType === 'dns-prefetch')
		{
			$this->preloadManager->dnsPrefetch($uri, $attributes);
		}
		elseif (method_exists($this->preloadManager, $linkType))
		{
			$this->preloadManager->$linkType($uri, $attributes);
		}
		else
		{
			throw new \InvalidArgumentException(sprintf('The "%s" method is not supported for preloading.', $linkType), 500);
		}

		return $uri;
	}
}

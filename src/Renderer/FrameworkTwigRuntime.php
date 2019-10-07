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
	 * The HTTP/2 preload manager
	 *
	 * @var  PreloadManager
	 */
	private $preloadManager;

	/**
	 * The SRI manifest data
	 *
	 * @var  array|null
	 */
	private $sriManifestData;

	/**
	 * The path to the SRI manifest data
	 *
	 * @var  string
	 */
	private $sriManifestPath;

	/**
	 * Constructor
	 *
	 * @param   AbstractApplication  $app              The application object
	 * @param   PreloadManager       $preloadManager   The HTTP/2 preload manager
	 * @param   string               $sriManifestPath  The path to the SRI manifest data
	 */
	public function __construct(AbstractApplication $app, PreloadManager $preloadManager, string $sriManifestPath)
	{
		$this->app             = $app;
		$this->preloadManager  = $preloadManager;
		$this->sriManifestPath = $sriManifestPath;
	}

	/**
	 * Retrieves the current URI
	 *
	 * @return  string
	 */
	public function getRequestUri(): string
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
	public function getRouteUri(string $route = ''): string
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
	public function getRouteUrl(string $route = ''): string
	{
		return $this->app->get('uri.base.host') . $this->getRouteUri($route);
	}

	/**
	 * Get the SRI attributes for an asset
	 *
	 * @param   string  $path  A public path
	 *
	 * @return  string
	 */
	public function getSriAttributes(string $path): string
	{
		if ($this->sriManifestData === null)
		{
			if (!file_exists($this->sriManifestPath))
			{
				throw new \RuntimeException(sprintf('SRI manifest file "%s" does not exist.', $this->sriManifestPath));
			}

			$sriManifestContents = file_get_contents($this->sriManifestPath);

			if ($sriManifestContents === false)
			{
				throw new \RuntimeException(sprintf('Could not read SRI manifest file "%s".', $this->sriManifestPath));
			}

			$this->sriManifestData = json_decode($sriManifestContents, true);

			if (0 < json_last_error())
			{
				throw new \RuntimeException(sprintf('Error parsing JSON from SRI manifest file "%s" - %s', $this->sriManifestPath, json_last_error_msg()));
			}
		}

		$assetKey = "/$path";

		if (!isset($this->sriManifestData[$assetKey]))
		{
			return '';
		}

		$attributes = '';

		foreach ($this->sriManifestData[$assetKey] as $key => $value)
		{
			$attributes .= ' ' . $key . '="' . $value . '"';
		}

		return $attributes;
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
		$this->preloadManager->link($uri, $linkType, $attributes);

		return $uri;
	}
}

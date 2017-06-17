<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Renderer;

use Twig\Extension\AbstractExtension;
use Twig\{
	TwigFilter, TwigFunction
};

/**
 * Framework site's Twig extension class
 *
 * @since  1.0
 */
class FrameworkExtension extends AbstractExtension
{
	/**
	 * Returns a list of filters to add to the existing list
	 *
	 * @return  TwigFilter[]  An array of TwigFilter instances
	 *
	 * @since   1.0
	 */
	public function getFilters()
	{
		return [
			new TwigFilter('get_class', 'get_class'),
			new TwigFilter('strip_root_path', [$this, 'stripRootPath'])
		];
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return  TwigFunction[]  An array of TwigFunction instances
	 *
	 * @since   1.0
	 */
	public function getFunctions()
	{
		return [
			new TwigFunction('asset', [FrameworkTwigRuntime::class, 'getAssetUri']),
			new TwigFunction('preload', [FrameworkTwigRuntime::class, 'preloadAsset']),
			new TwigFunction('request_uri', [FrameworkTwigRuntime::class, 'getRequestUri']),
			new TwigFunction('route', [FrameworkTwigRuntime::class, 'getRouteUri']),
			new TwigFunction('url', [FrameworkTwigRuntime::class, 'getRouteUrl']),
		];
	}

	/**
	 * Removes the application root path defined by the constant "JPATH_ROOT"
	 *
	 * @param   string  $string  The string to process
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function stripRootPath(string $string) : string
	{
		return str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $string);
	}
}

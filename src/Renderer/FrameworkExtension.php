<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Renderer;

/**
 * Framework site's Twig extension class
 *
 * @since  1.0
 */
class FrameworkExtension extends \Twig_Extension
{
	/**
	 * Returns a list of filters to add to the existing list
	 *
	 * @return  \Twig_Filter[]  An array of \Twig_Filter instances
	 *
	 * @since   1.0
	 */
	public function getFilters()
	{
		return [
			new \Twig_Filter('get_class', 'get_class'),
			new \Twig_Filter('strip_root_path', [$this, 'stripRootPath'])
		];
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return  \Twig_Function[]  An array of \Twig_Function instances
	 *
	 * @since   1.0
	 */
	public function getFunctions()
	{
		return [
			new \Twig_Function('asset', [FrameworkTwigRuntime::class, 'getAssetUri']),
			new \Twig_Function('request_uri', [FrameworkTwigRuntime::class, 'getRequestUri']),
			new \Twig_Function('route', [FrameworkTwigRuntime::class, 'getRouteUri']),
			new \Twig_Function('url', [FrameworkTwigRuntime::class, 'getRouteUrl']),
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

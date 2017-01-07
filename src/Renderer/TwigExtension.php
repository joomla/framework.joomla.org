<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Renderer;

/**
 * Twig extension class
 *
 * @since  1.0
 */
class TwigExtension extends \Twig_Extension
{
	/**
	 * Returns a list of filters to add to the existing list
	 *
	 * @return  \Twig_SimpleFilter[]  An array of \Twig_SimpleFilter instances
	 *
	 * @since   1.0
	 */
	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter('get_class', 'get_class'),
			new \Twig_SimpleFilter('stripJRoot', [$this, 'stripJRoot'])
		];
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return  \Twig_SimpleFunction[]  An array of \Twig_SimpleFunction instances
	 *
	 * @since   1.0
	 */
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('asset', [TwigRuntime::class, 'getAssetUri']),
			new \Twig_SimpleFunction('route', [TwigRuntime::class, 'getRouteUri'])
		];
	}

	/**
	 * Replaces the application root path defined by the constant "JPATH_ROOT" with the string "APP_ROOT"
	 *
	 * @param   string  $string  The string to process
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function stripJRoot($string)
	{
		return str_replace(JPATH_ROOT, 'APP_ROOT', $string);
	}
}

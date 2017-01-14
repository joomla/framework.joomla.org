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
	 * @return  \Twig_Filter[]  An array of \Twig_Filter instances
	 *
	 * @since   1.0
	 */
	public function getFilters()
	{
		return [
			new \Twig_Filter('get_class', 'get_class'),
			new \Twig_Filter('stripJRoot', [$this, 'stripJRoot'])
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
			new \Twig_Function('asset', [TwigRuntime::class, 'getAssetUri']),
			new \Twig_Function('route', [TwigRuntime::class, 'getRouteUri'])
		];
	}

	/**
	 * Replaces the application root path defined by the constant "JPATH_ROOT" with the string "APP_ROOT"
	 *
	 * @param   string  $string  The string to process
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function stripJRoot(string $string) : string
	{
		return str_replace(JPATH_ROOT, 'APP_ROOT', $string);
	}
}

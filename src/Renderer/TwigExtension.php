<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Renderer;

use Joomla\Application\AbstractApplication;

/**
 * Twig extension class
 *
 * @since  1.0
 */
class TwigExtension extends \Twig_Extension
{
	/**
	 * Application object
	 *
	 * @var    AbstractApplication
	 * @since  1.0
	 */
	private $app;

	/**
	 * Constructor
	 *
	 * @param   AbstractApplication  $app  The application object
	 *
	 * @since   1.0
	 */
	public function __construct(AbstractApplication $app)
	{
		$this->app = $app;
	}

	/**
	 * Returns the name of the extension
	 *
	 * @return  string  The extension name
	 *
	 * @since   1.0
	 */
	public function getName()
	{
		return 'joomla-framework-status';
	}

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
			new \Twig_SimpleFunction('asset', [$this, 'getAssetUri']),
			new \Twig_SimpleFunction('route', [$this, 'getRouteUri'])
		];
	}

	/**
	 * Get the URI for an asset
	 *
	 * @param   string  $asset  Asset to get the path for
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getAssetUri($asset)
	{
		return $this->app->get('uri.media.path') . $asset;
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
	public function getRouteUri($route = null)
	{
		return $this->app->get('uri.base.path') . $route;
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

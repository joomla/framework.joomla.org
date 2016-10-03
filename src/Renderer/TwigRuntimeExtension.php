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
 * Twig runtime extension class
 *
 * @since  1.0
 */
class TwigRuntimeExtension extends \Twig_Extension
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
}

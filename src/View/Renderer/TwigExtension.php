<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\View\Renderer;

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
	 * @param   AbstractApplication  $container  The application object
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
	 * Returns a list of global variables to add to the existing list
	 *
	 * @return  array  An array of global variables
	 *
	 * @since   1.0
	 */
	public function getGlobals()
	{
		return array(
			'uri' => $this->app->get('uri')
		);
	}

	/**
	 * Returns a list of functions to add to the existing list
	 *
	 * @return  array  An array of functions
	 *
	 * @since   1.0
	 */
	public function getFunctions()
	{
		$functions = array(
			new \Twig_SimpleFunction('sprintf', 'sprintf'),
			new \Twig_SimpleFunction('stripJRoot', array($this, 'stripJRoot'))
		);

		return $functions;
	}

	/**
	 * Returns a list of filters to add to the existing list
	 *
	 * @return  array  An array of filters
	 *
	 * @since   1.0
	 */
	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('basename', 'basename'),
			new \Twig_SimpleFilter('get_class', 'get_class'),
			new \Twig_SimpleFilter('json_decode', 'json_decode'),
			new \Twig_SimpleFilter('stripJRoot', array($this, 'stripJRoot'))
		);
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

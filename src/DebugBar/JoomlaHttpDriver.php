<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\DebugBar;

use DebugBar\HttpDriverInterface;
use Joomla\Application\AbstractWebApplication;

/**
 * HTTP Driver for the DebugBar integrating into the Joomla API
 */
class JoomlaHttpDriver implements HttpDriverInterface
{
	/**
	 * Web application
	 *
	 * @var  AbstractWebApplication
	 */
	private $application;

	/**
	 * Constructor.
	 *
	 * @param   AbstractWebApplication  $application  Web application
	 */
	public function __construct(AbstractWebApplication $application)
	{
		$this->application = $application;
	}

	/**
	 * Sets HTTP headers
	 *
	 * @param   array  $headers  Headers to add to the request
	 *
	 * @return  void
	 */
	public function setHeaders(array $headers)
	{
		foreach ($headers as $name => $value)
		{
			$this->application->setHeader($name, $value);
		}
	}

	/**
	 * Checks if the session is started
	 *
	 * @return  boolean
	 */
	public function isSessionStarted()
	{
		// This application has no session integration
		return false;
	}

	/**
	 * Sets a value in the session
	 *
	 * @param   string  $name   Name of a variable.
	 * @param   mixed   $value  Value of a variable.
	 *
	 * @return  void
	 */
	public function setSessionValue($name, $value)
	{
		// This application has no session integration
	}

	/**
	 * Checks if a value is in the session
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  boolean
	 */
	public function hasSessionValue($name)
	{
		// This application has no session integration
		return false;
	}

	/**
	 * Returns a value from the session
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  mixed
	 */
	public function getSessionValue($name)
	{
		// This application has no session integration
		return null;
	}

	/**
	 * Deletes a value from the session
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  void
	 */
	public function deleteSessionValue($name)
	{
		// This application has no session integration
	}
}

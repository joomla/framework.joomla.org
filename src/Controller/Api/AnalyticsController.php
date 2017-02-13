<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller\Api;

use Joomla\FrameworkWebsite\WebApplication;
use Joomla\Input\Input;
use Ramsey\Uuid\Uuid;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Trait defining controllers which report analytics
 *
 * @since  1.0
 */
trait AnalyticsController
{
	/**
	 * Analytics object.
	 *
	 * @var    Analytics
	 * @since  1.0
	 */
	private $analytics;

	/**
	 * Get the application object.
	 *
	 * @return  WebApplication  The application object.
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException if the application has not been set.
	 */
	abstract public function getApplication();

	/**
	 * Get the input object.
	 *
	 * @return  Input  The input object.
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException
	 */
	abstract public function getInput();

	/**
	 * Send Google Analytics data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function sendAnalytics()
	{
		// On a GET request, submit analytics data if enabled
		if ($this->getInput()->getMethod() === 'GET'
			&& $this->getApplication()->get('analytics.enabled', false)
			&& $this->analytics)
		{
			$this->analytics->setAsyncRequest(true)
				->setProtocolVersion('1')
				->setTrackingId($this->getApplication()->get('analytics.account', ''))
				->setClientId(Uuid::uuid4()->toString())
				->setDocumentPath($this->getApplication()->get('uri.base.path'))
				->setIpOverride($this->getInput()->server->getString('REMOTE_ADDR', '127.0.0.1'))
				->setUserAgentOverride($this->getInput()->server->getString('HTTP_USER_AGENT', 'JoomlaFramework/1.0'));

			// Don't allow sending Analytics data to cause a failure
			try
			{
				$this->analytics->sendPageview();
			}
			catch (\Exception $e)
			{
				// TODO - Incorporate support for a logger to record the error
			}
		}
	}
}

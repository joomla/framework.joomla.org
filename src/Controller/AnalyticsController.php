<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\Input\Input;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Base class for controllers which report analytics data to Google
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 *
 * @since          1.0
 */
abstract class AnalyticsController extends AbstractController implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * Analytics object.
	 *
	 * @var    Analytics
	 * @since  1.0
	 */
	private $analytics;

	/**
	 * Constructor.
	 *
	 * @param   Analytics            $analytics  Analytics object.
	 * @param   StatusJsonView       $view       The view object.
	 * @param   Input                $input      The input object.
	 * @param   AbstractApplication  $app        The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(Analytics $analytics, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->analytics = $analytics;
	}

	/**
	 * Send Google Analytics data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function sendAnalytics()
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
				$this->logger->warning(
					$e->getMessage(),
					['exception' => $e]
				);
			}
		}
	}
}

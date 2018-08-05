<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller\Api;

use Joomla\Application\AbstractApplication;
use Joomla\FrameworkWebsite\Controller\AnalyticsController;
use Joomla\FrameworkWebsite\View\Package\PackageJsonView;
use Joomla\Input\Input;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * API Controller handling a package's status data listing
 */
class PackageControllerGet extends AnalyticsController
{
	/**
	 * The view object.
	 *
	 * @var  PackageJsonView
	 */
	private $view;

	/**
	 * Constructor.
	 *
	 * @param   PackageJsonView      $view       The view object.
	 * @param   Analytics            $analytics  Analytics object.
	 * @param   Input                $input      The input object.
	 * @param   AbstractApplication  $app        The application object.
	 */
	public function __construct(PackageJsonView $view, Analytics $analytics, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($analytics, $input, $app);

		$this->view = $view;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute() : bool
	{
		$this->sendAnalytics();

		// Disable browser caching
		$this->getApplication()->allowCache(false);

		// This is a JSON response
		$this->getApplication()->mimeType = 'application/json';

		$this->view->setPackage($this->getInput()->getString('package'));

		$this->getApplication()->setBody($this->view->render());

		return true;
	}
}

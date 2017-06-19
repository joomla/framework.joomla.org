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
use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Joomla\FrameworkWebsite\View\Status\StatusHtmlView;
use Joomla\Input\Input;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Controller handling the site's package status listing
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 *
 * @since          1.0
 */
class StatusController extends AbstractController
{
	/**
	 * The packagist helper object
	 *
	 * @var    PackagistHelper
	 * @since  1.0
	 */
	private $packagistHelper;

	/**
	 * The view object.
	 *
	 * @var    StatusHtmlView
	 * @since  1.0
	 */
	private $view;

	/**
	 * Constructor.
	 *
	 * @param   StatusHtmlView       $view             The view object.
	 * @param   PackagistHelper      $packagistHelper  The packagist helper object.
	 * @param   Input                $input            The input object.
	 * @param   AbstractApplication  $app              The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(StatusHtmlView $view, PackagistHelper $packagistHelper, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->packagistHelper = $packagistHelper;
		$this->view            = $view;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute() : bool
	{
		// Enable browser caching
		$this->getApplication()->allowCache(true);

		$this->view->addData('downloads', $this->packagistHelper->getDownloadCounts());

		$this->getApplication()->setResponse(new HtmlResponse($this->view->render()));

		return true;
	}
}

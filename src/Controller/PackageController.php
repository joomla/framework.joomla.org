<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Controller;

use Joomla\Registry\Registry;

/**
 * Controller class for the package view of the application
 *
 * @since  1.0
 */
class PackageController extends DefaultController
{
	/**
	 * The default view for the application
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $defaultView = 'package';

	/**
	 * Method to initialize the model object
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function initializeModel()
	{
		$this->modelState = new Registry;

		$this->modelState->set('package.name', $this->getInput()->getString('package'));

		parent::initializeModel();
	}
}

<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\View\Dashboard;

use Joomla\Status\Model\DashboardModel;
use Joomla\Status\View\DefaultHtmlView;

/**
 * Dashboard HTML view class for the application
 *
 * @since  1.0
 */
class DashboardHtmlView extends DefaultHtmlView
{
	/**
	 * The model object, redeclared here for proper typehinting
	 *
	 * @var    DashboardModel
	 * @since  1.0
	 */
	protected $model;

	/**
	 * Method to render the view
	 *
	 * @return  string  The rendered view
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		$this->getRenderer()->set('items', $this->model->getItems());

		return parent::render();
	}
}

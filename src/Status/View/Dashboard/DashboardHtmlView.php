<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\View\Dashboard;

use Joomla\Status\Model\DefaultModel;
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
	 * @var    DefaultModel
	 * @since  1.0
	 */
	protected $model;
}

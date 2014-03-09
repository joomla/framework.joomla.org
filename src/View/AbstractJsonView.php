<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\View;

use Joomla\Application\AbstractApplication;
use Joomla\Model\ModelInterface;
use Joomla\View\AbstractView;

/**
 * Abstract JSON view class for the application
 *
 * @since  1.0
 */
abstract class AbstractJsonView extends AbstractView
{
	/**
	 * Method to instantiate the view
	 *
	 * @param   AbstractApplication  $app            The application object
	 * @param   ModelInterface       $model          The model object
	 * @param   array                $templatePaths  Array of paths for template lookup
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function __construct(AbstractApplication $app, ModelInterface $model, $templatePaths = array())
	{
		parent::__construct($model);
	}
}

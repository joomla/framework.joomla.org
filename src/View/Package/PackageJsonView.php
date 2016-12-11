<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\View\Package;

use Joomla\Status\Model\PackageModel;
use Joomla\Status\View\AbstractJsonView;

/**
 * Package JSON view class for the application
 *
 * @since  1.0
 */
class PackageJsonView extends AbstractJsonView
{
	/**
	 * The model object, redeclared here for proper typehinting
	 *
	 * @var    PackageModel
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
		$package = $this->model->getState()->get('package.name');

		$data = [
			'items'   => $this->model->getItems(),
			'package' => $this->model->getPackages()->get('packages.' . $package . '.display', ucfirst($package)),
		];

		return json_encode($data);
	}
}

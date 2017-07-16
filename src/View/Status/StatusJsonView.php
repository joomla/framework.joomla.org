<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Status;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\View\BaseJsonView;

/**
 * Status dashboard JSON view class for the application
 */
class StatusJsonView extends BaseJsonView
{
	/**
	 * The model object.
	 *
	 * @var  PackageModel
	 */
	protected $model;

	/**
	 * Instantiate the view.
	 *
	 * @param   PackageModel  $model  The model object.
	 */
	public function __construct(PackageModel $model)
	{
		$this->model = $model;
	}

	/**
	 * Method to render the view
	 *
	 * @return  string  The rendered view
	 */
	public function render()
	{
		$packages = $this->model->getLatestReleases();

		// Remove the ID and package ID for each item
		foreach ($packages as $package)
		{
			unset($package->id, $package->package_id);
		}

		$this->setData(['packages' => $packages]);

		return parent::render();
	}
}

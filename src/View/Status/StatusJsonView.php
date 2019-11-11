<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Status;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\Model\ReleaseModel;
use Joomla\View\JsonView;

/**
 * Status dashboard JSON view class for the application
 */
class StatusJsonView extends JsonView
{
	/**
	 * The package model object.
	 *
	 * @var  PackageModel
	 */
	private $packageModel;

	/**
	 * The release model object.
	 *
	 * @var  ReleaseModel
	 */
	private $releaseModel;

	/**
	 * Instantiate the view.
	 *
	 * @param   PackageModel  $packageModel  The package model object.
	 * @param   ReleaseModel  $releaseModel  The release model object.
	 */
	public function __construct(PackageModel $packageModel, ReleaseModel $releaseModel)
	{
		$this->packageModel = $packageModel;
		$this->releaseModel = $releaseModel;
	}

	/**
	 * Method to render the view
	 *
	 * @return  string  The rendered view
	 */
	public function render()
	{
		$releases = $this->releaseModel->getLatestReleases($this->packageModel->getPackages());

		// Remove the ID and package ID for each item
		foreach ($releases as $release)
		{
			unset($release->id, $release->package->id);
		}

		$this->setData(['packages' => array_values($releases)]);

		return parent::render();
	}
}

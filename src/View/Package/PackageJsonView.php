<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Package;

use Joomla\FrameworkWebsite\Helper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\View\BaseJsonView;

/**
 * Package JSON view class for the application
 *
 * @since  1.0
 */
class PackageJsonView extends BaseJsonView
{
	/**
	 * The model object
	 *
	 * @var  PackageModel
	 */
	protected $model;

	/**
	 * The active package
	 *
	 * @var  string
	 */
	private $package = '';

	/**
	 * Instantiate the view.
	 *
	 * @param   PackageModel  $model  The model object.
	 */
	public function __construct(PackageModel $model)
	{
		$this->model  = $model;
	}

	/**
	 * Method to render the view
	 *
	 * @return  string  The rendered view
	 */
	public function render()
	{
		$releases = $this->model->getPackageHistory($this->package);

		// Remove the ID and package ID for each item
		foreach ($releases as $release)
		{
			unset($release->id, $release->package_id);
		}

		$this->setData(['releases' => $releases]);

		return parent::render();
	}

	/**
	 * Set the active package
	 *
	 * @param   string  $package  The active package name
	 *
	 * @return  void
	 */
	public function setPackage(string $package)
	{
		$this->package = $package;
	}
}

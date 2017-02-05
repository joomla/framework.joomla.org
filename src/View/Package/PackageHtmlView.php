<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Package;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\View\DefaultHtmlView;
use Joomla\Renderer\RendererInterface;

/**
 * Package HTML view class for the application
 *
 * @since  1.0
 */
class PackageHtmlView extends DefaultHtmlView
{
	/**
	 * The model object
	 *
	 * @var    PackageModel
	 * @since  1.0
	 */
	protected $model;

	/**
	 * The active package
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $package = '';

	/**
	 * Instantiate the view.
	 *
	 * @param   PackageModel       $model     The model object.
	 * @param   RendererInterface  $renderer  The renderer object.
	 *
	 * @since   1.0
	 */
	public function __construct(PackageModel $model, RendererInterface $renderer)
	{
		parent::__construct($renderer);

		$this->model = $model;
	}

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
		$this->setData([
			'releases' => $this->model->getPackageHistory($this->package),
			'packageName'  => $this->model->getPackages()->get('packages.' . $this->package . '.display', ucfirst($this->package)),
			'repoName'  => $this->model->getPackages()->get('packages.' . $this->package . '.repo', $this->package),
		]);

		return parent::render();
	}

	/**
	 * Set the active package
	 *
	 * @param   string  $package  The active package name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setPackage(string $package)
	{
		$this->package = $package;
	}
}

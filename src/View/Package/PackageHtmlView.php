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
use Joomla\Renderer\RendererInterface;
use Joomla\View\BaseHtmlView;

/**
 * Package HTML view class for the application
 *
 * @since  1.0
 */
class PackageHtmlView extends BaseHtmlView
{
	/**
	 * Helper object
	 *
	 * @var    Helper
	 * @since  1.0
	 */
	private $helper;

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
	 * @param   Helper             $helper    Helper object.
	 *
	 * @since   1.0
	 */
	public function __construct(PackageModel $model, RendererInterface $renderer, Helper $helper)
	{
		parent::__construct($renderer);

		$this->helper = $helper;
		$this->model  = $model;
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
			'releases'          => $this->model->getPackageHistory($this->package),
			'package'           => $this->package,
			'packageName'       => $this->helper->getPackageDisplayName($this->package),
			'repoName'          => $this->helper->getPackageRepositoryName($this->package),
			'packageDeprecated' => $this->helper->getPackageDeprecated($this->package),
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

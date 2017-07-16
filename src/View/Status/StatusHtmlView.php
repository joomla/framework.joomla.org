<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Status;

use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Renderer\RendererInterface;
use Joomla\View\BaseHtmlView;

/**
 * Status dashboard HTML view class for the application
 */
class StatusHtmlView extends BaseHtmlView
{
	/**
	 * The model object.
	 *
	 * @var  PackageModel
	 */
	private $model;

	/**
	 * The packagist helper object
	 *
	 * @var  PackagistHelper
	 */
	private $packagistHelper;

	/**
	 * Instantiate the view.
	 *
	 * @param   PackageModel       $model            The model object.
	 * @param   PackagistHelper    $packagistHelper  The Packagist helper object.
	 * @param   RendererInterface  $renderer         The renderer object.
	 */
	public function __construct(PackageModel $model, PackagistHelper $packagistHelper, RendererInterface $renderer)
	{
		parent::__construct($renderer);

		$this->model           = $model;
		$this->packagistHelper = $packagistHelper;
	}

	/**
	 * Method to render the view
	 *
	 * @return  string  The rendered view
	 */
	public function render()
	{
		$this->setData(
			[
				'downloads' => $this->packagistHelper->getDownloadCounts(),
				'packages'  => $this->model->getLatestReleases(),
			]
		);

		return parent::render();
	}
}

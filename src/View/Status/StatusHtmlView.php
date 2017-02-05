<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Status;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\FrameworkWebsite\View\DefaultHtmlView;
use Joomla\Renderer\RendererInterface;

/**
 * Status dashboard HTML view class for the application
 *
 * @since  1.0
 */
class StatusHtmlView extends DefaultHtmlView
{
	/**
	 * The model object.
	 *
	 * @var    PackageModel
	 * @since  1.0
	 */
	protected $model;

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
	 */
	public function render()
	{
		$this->setData(['packages' => $this->model->getLatestReleases()]);

		return parent::render();
	}
}

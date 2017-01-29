<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Status;

use Joomla\FrameworkWebsite\View\DefaultHtmlView;
use Joomla\Renderer\RendererInterface;
use Joomla\Status\Model\StatusModel;

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
	 * @var    StatusModel
	 * @since  1.0
	 */
	protected $model;

	/**
	 * Instantiate the view.
	 *
	 * @param   StatusModel        $model     The model object.
	 * @param   RendererInterface  $renderer  The renderer object.
	 *
	 * @since   1.0
	 */
	public function __construct(StatusModel $model, RendererInterface $renderer)
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
		$this->setData(['items' => $this->model->getItems()]);

		return parent::render();
	}
}

<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Contributor;

use Joomla\FrameworkWebsite\Model\ContributorModel;
use Joomla\Renderer\RendererInterface;
use Joomla\View\BaseHtmlView;

/**
 * Contributor HTML view class for the application
 */
class ContributorHtmlView extends BaseHtmlView
{
	/**
	 * The contributor model object.
	 *
	 * @var  ContributorModel
	 */
	private $contributorModel;

	/**
	 * Instantiate the view.
	 *
	 * @param   ContributorModel   $contributorModel  The contributor model object.
	 * @param   RendererInterface  $renderer          The renderer object.
	 */
	public function __construct(ContributorModel $contributorModel, RendererInterface $renderer)
	{
		parent::__construct($renderer);

		$this->contributorModel = $contributorModel;
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
				'contributors' => $this->contributorModel->getContributors(),
			]
		);

		return parent::render();
	}
}

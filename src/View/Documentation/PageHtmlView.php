<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\View\Documentation;

use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Renderer\RendererInterface;
use Joomla\View\BaseHtmlView;

/**
 * Documentation page HTML view class for the application
 */
class PageHtmlView extends BaseHtmlView
{
	/**
	 * The error message for display.
	 *
	 * @var  string
	 */
	private $contents = '';

	/**
	 * The package model object.
	 *
	 * @var  PackageModel
	 */
	private $packageModel;

	/**
	 * Instantiate the view.
	 *
	 * @param   PackageModel       $packageModel  The package model object.
	 * @param   RendererInterface  $renderer      The renderer object.
	 */
	public function __construct(PackageModel $packageModel, RendererInterface $renderer)
	{
		parent::__construct($renderer);

		$this->packageModel = $packageModel;
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
				'contents' => $this->contents,
				'packages' => $this->packageModel->getSortedPackages(),
			]
		);

		return parent::render();
	}

	/**
	 * Set the content for display
	 *
	 * @param   string  $contents  The content to display
	 *
	 * @return  void
	 */
	public function setPageContent(string $contents)
	{
		$this->contents = $contents;
	}
}

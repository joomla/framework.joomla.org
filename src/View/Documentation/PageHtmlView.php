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
use Joomla\View\HtmlView;

/**
 * Documentation page HTML view class for the application
 */
class PageHtmlView extends HtmlView
{
	/**
	 * The page contents for display.
	 *
	 * @var  string
	 */
	private $contents = '';

	/**
	 * The active package.
	 *
	 * @var  \stdClass
	 */
	private $package;

	/**
	 * The package model object.
	 *
	 * @var  PackageModel
	 */
	private $packageModel;

	/**
	 * The sidebar contents for display.
	 *
	 * @var  string
	 */
	private $sidebarContents = '';

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
				'activePackage'   => $this->package,
				'contents'        => $this->contents,
				'packages'        => $this->packageModel->getSortedPackages(),
				'sidebarContents' => $this->sidebarContents,
			]
		);

		return parent::render();
	}

	/**
	 * Set the active package
	 *
	 * @param   \stdClass  $package  The active package for the page
	 *
	 * @return  void
	 */
	public function setActivePackage(\stdClass $package): void
	{
		$this->package = $package;
	}

	/**
	 * Set the content for display
	 *
	 * @param   string  $contents  The content to display
	 *
	 * @return  void
	 */
	public function setPageContent(string $contents): void
	{
		$this->contents = $contents;
	}

	/**
	 * Set the sidebar content for display
	 *
	 * @param   string  $contents  The content to display
	 *
	 * @return  void
	 */
	public function setSidebarContent(string $contents): void
	{
		$this->sidebarContents = $contents;
	}
}

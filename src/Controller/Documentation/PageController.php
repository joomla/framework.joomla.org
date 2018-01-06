<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Controller\Documentation;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\Model\PackageModel;
use Joomla\Input\Input;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Controller handling a package's documentation page
 *
 * @method         \Joomla\FrameworkWebsite\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\WebApplication  $app              Application object
 */
class PageController extends AbstractController
{
	/**
	 * The model object.
	 *
	 * @var  PackageModel
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @param   PackageModel         $model  The model object.
	 * @param   Input                $input  The input object.
	 * @param   AbstractApplication  $app    The application object.
	 */
	public function __construct(PackageModel $model, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->model = $model;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute() : bool
	{
		$packageName = $this->getInput()->getString('package');
		$version     = $this->getInput()->getString('version');
		$filename    = $this->getInput()->getString('filename');

		if (!$packageName || !$version)
		{
			throw new \RuntimeException('Missing required package or version parameters.', 404);
		}

		$package = $this->model->getPackage($packageName);

		switch ($version)
		{
			case '1.x':
				throw new \RuntimeException('The Framework 1.x releases are not documented.', 404);

			case '2.x':
				if (!$package->has_v2)
				{
					throw new \RuntimeException(sprintf('The `%s` package does not have a 2.x branch to document.', $package->display), 404);
				}

				break;

			case 'latest':
				$this->getApplication()->setResponse(
					new RedirectResponse($this->getApplication()->get('uri.base.path') . "docs/2.x/{$package->package}/{$filename}")
				);

				break;

			default:
				throw new \RuntimeException(sprintf('Unsupported version `%s` for documentation.', $version), 404);
		}

		return true;
	}
}

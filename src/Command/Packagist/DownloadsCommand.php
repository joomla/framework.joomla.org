<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Packagist;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\FrameworkWebsite\CommandInterface;
use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Joomla\Input\Input;

/**
 * Command to get download counts from Packagist
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class DownloadsCommand extends AbstractController implements CommandInterface
{
	/**
	 * The packagist helper object
	 *
	 * @var    PackagistHelper
	 * @since  1.0
	 */
	private $packagistHelper;

	/**
	 * Instantiate the controller.
	 *
	 * @param   PackagistHelper      $packagistHelper  The packagist helper object.
	 * @param   Input                $input            The input object.
	 * @param   AbstractApplication  $app              The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(PackagistHelper $packagistHelper, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->packagistHelper = $packagistHelper;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Sync Download Counts from Packagist');

		if ($this->packagistHelper->saveCountsToCache($this->packagistHelper->fetchDownloadCounts()))
		{
			$this->getApplication()->out('<info>Update completed.</info>');
		}
		else
		{
			$this->getApplication()->out('<error>Error storing download counts.</error>');
		}

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getDescription() : string
	{
		return 'Synchronizes download count data from Packagist.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle() : string
	{
		return 'Get Download Counts';
	}
}

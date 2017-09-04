<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Packagist;

use Joomla\Console\AbstractCommand;
use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to get download counts from Packagist
 */
class DownloadsCommand extends AbstractCommand
{
	/**
	 * The packagist helper object
	 *
	 * @var  PackagistHelper
	 */
	private $packagistHelper;

	/**
	 * Instantiate the command.
	 *
	 * @param   PackagistHelper  $packagistHelper  The packagist helper object.
	 */
	public function __construct(PackagistHelper $packagistHelper)
	{
		$this->packagistHelper = $packagistHelper;

		parent::__construct();
	}

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());

		$symfonyStyle->title('Sync Download Counts from Packagist');

		$this->packagistHelper->syncDownloadCounts();

		$symfonyStyle->success('Update completed.');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('packagist:sync:downloads');
		$this->setDescription('Synchronizes download counts with Packagist');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command synchronizes the package download counts with Packagist

<info>php %command.full_name% %command.name%</info>
EOF
		);
	}
}

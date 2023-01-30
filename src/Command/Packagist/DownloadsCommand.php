<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Packagist;

use Joomla\Console\Command\AbstractCommand;
use Joomla\FrameworkWebsite\Helper\PackagistHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to get download counts from Packagist
 */
class DownloadsCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var  string|null
     */
    protected static $defaultName = 'packagist:sync:downloads';

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
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title('Sync Download Counts from Packagist');
        $this->packagistHelper->syncDownloadCounts();
        $symfonyStyle->success('Update completed.');
        return 0;
    }

    /**
     * Configures the current command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('Synchronizes download counts with Packagist');
    }
}

<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate SRI information command
 */
class GenerateSriCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var  string|null
     */
    protected static $defaultName = 'template:generate-sri';
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
        $symfonyStyle->title('Generate SRI Information');
        $mediaDir        = JPATH_ROOT . '/www/media';
        $manifestFile    = $mediaDir . '/manifest.json';
        $sriManifestFile = $mediaDir . '/sri-manifest.json';
        if (!file_exists($manifestFile)) {
            $symfonyStyle->error('The asset manifest file is missing, run `npm run prod` first.');
            return 1;
        }

        $manifestData = file_get_contents($manifestFile);
        if ($manifestData === false) {
            $symfonyStyle->error('The asset manifest file could not be read.');
            return 1;
        }

        $manifest    = json_decode($manifestData, true);
        $sriData     = [];
        foreach (array_keys($manifest) as $file) {
            $fullPath = $mediaDir . $file;
            if (!file_exists($fullPath)) {
                $symfonyStyle->warning(sprintf('The asset "%s" is missing.', $fullPath));
                continue;
            }

            $fileContents = file_get_contents($fullPath);
            if ($fileContents === false) {
                $symfonyStyle->warning(sprintf('The asset "%s" could not be read.', $fullPath));
                continue;
            }

            $sriData[$file] = [
                'crossorigin' => 'anonymous',
                'integrity'   => 'sha384-' . base64_encode(hash('sha384', $fileContents, true)),
            ];
        }

        if (!file_put_contents($sriManifestFile, json_encode($sriData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            $symfonyStyle->error('The SRI manifest data was not saved.');
            return 1;
        }

        $symfonyStyle->success('File generated');
        return 0;
    }

    /**
     * Configures the current command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('Generate SRI information for the assets built by Vite');
    }
}

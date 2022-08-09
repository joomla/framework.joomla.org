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
        $mediaDir = JPATH_ROOT . '/www/media';
        $mixManifestFile = $mediaDir . '/mix-manifest.json';
        $sriManifestFile = $mediaDir . '/sri-manifest.json';
        if (!file_exists($mixManifestFile)) {
            $symfonyStyle->error('The Mix manifest file is missing.');
            return 1;
        }

        $mixManifestData = file_get_contents($mixManifestFile);
        if ($mixManifestData === false) {
            $symfonyStyle->error('The Mix manifest file could not be read.');
            return 1;
        }

        $mixManifest = json_decode($mixManifestData, true);
        $sriData = [];
        foreach (array_keys($mixManifest) as $file) {
            $fullPath = $mediaDir . $file;
            if (!file_exists($fullPath)) {
                $symfonyStyle->warning(sprintf('The Mix resource "%s" is missing.', $fullPath));
                continue;
            }

            $fileContents = file_get_contents($fullPath);
            if ($fileContents === false) {
                $symfonyStyle->warning(sprintf('The Mix resource "%s" could not be read.', $fullPath));
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
        $this->setDescription('Generate SRI information for Mix generated assets');
    }
}

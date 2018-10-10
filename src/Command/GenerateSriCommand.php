<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate SRI information command
 */
class GenerateSriCommand extends Command
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'template:generate-sri';

	/**
	 * Executes the current command.
	 *
	 * @param   InputInterface   $input   The command input.
	 * @param   OutputInterface  $output  The command output.
	 *
	 * @return  integer|null  null or 0 if everything went fine, or an error code
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Generate SRI Information');

		$mediaDir = JPATH_ROOT . '/www/media';

		$mixManifestFile = $mediaDir . '/mix-manifest.json';
		$sriManifestFile = $mediaDir . '/sri-manifest.json';

		if (!file_exists($mixManifestFile))
		{
			$symfonyStyle->error('The Mix manifest file is missing.');

			return 1;
		}

		$mixManifest = json_decode(file_get_contents($mixManifestFile), true);

		$sriData = [];

		foreach (array_keys($mixManifest) as $file)
		{
			$fullPath = $mediaDir . $file;

			$sriData[$file] = [
				'crossorigin' => 'anonymous',
				'integrity'   => 'sha384-' . base64_encode(hash('sha384', file_get_contents($fullPath), true)),
			];
		}

		if (!file_put_contents($sriManifestFile, json_encode($sriData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)))
		{
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
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command generates SRI information for Mix generated assets

<info>php %command.full_name%</info>
EOF
		);
	}
}

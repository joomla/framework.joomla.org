<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Twig;

use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\Renderer\TwigRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Error\Error as TwigError;

/**
 * Twig cache reset command
 */
class ResetCacheCommand extends Command
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'twig:reset-cache';

	/**
	 * The template renderer
	 *
	 * @var  TwigRenderer
	 */
	private $renderer;

	/**
	 * The application configuration registry
	 *
	 * @var  Registry
	 */
	private $config;

	/**
	 * Instantiate the command.
	 *
	 * @param   TwigRenderer  $renderer  The template renderer
	 * @param   Registry      $config    The application configuration registry
	 */
	public function __construct(TwigRenderer $renderer, Registry $config)
	{
		$this->renderer = $renderer;
		$this->config   = $config;

		parent::__construct();
	}

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

		$symfonyStyle->title('Reset Twig Cache');

		// Check if caching is enabled
		if ($this->config->get('template.cache.enabled', false) === false)
		{
			$symfonyStyle->comment('Twig caching is disabled.');

			return 0;
		}

		$symfonyStyle->comment('Resetting Twig cache.');

		$twigCache = $this->config->get('template.cache.path', '');

		// First remove the existing cache files
		if (is_dir(JPATH_ROOT . '/' . $twigCache))
		{
			foreach (Folder::folders(JPATH_ROOT . '/' . $twigCache) as $folder)
			{
				Folder::delete(JPATH_ROOT . '/' . $twigCache . '/' . $folder);
			}
		}

		// Now get a list of all the templates
		$files = Folder::files(JPATH_TEMPLATES, '.twig', true, true);

		// Load each template now
		$engine       = $this->renderer->getRenderer();
		$errorFiles   = [];

		foreach ($files as $file)
		{
			$template = str_replace(JPATH_TEMPLATES . '/', '', $file);

			try
			{
				$engine->load($template);
			}
			catch (TwigError $e)
			{
				$errorFiles[] = $file;
			}
		}

		if (\count($errorFiles))
		{
			$symfonyStyle->warning('The following Twig resources failed to cache: ' . implode(', ', $errorFiles));
		}
		else
		{
			$symfonyStyle->success('The cached Twig resources were successfully created.');
		}

		return 0;
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->setDescription('Resets the Twig template cache');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command resets the Twig template cache

<info>php %command.full_name%</info>
EOF
		);
	}
}

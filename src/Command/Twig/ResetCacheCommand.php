<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Twig;

use Joomla\Console\AbstractCommand;
use Joomla\Filesystem\Folder;
use Joomla\Renderer\TwigRenderer;
use Twig\Error\Error as TwigError;

/**
 * Twig cache reset command
 */
class ResetCacheCommand extends AbstractCommand
{
	/**
	 * The template renderer
	 *
	 * @var  TwigRenderer
	 */
	private $renderer;

	/**
	 * Instantiate the command.
	 *
	 * @param   TwigRenderer  $renderer  The template renderer
	 */
	public function __construct(TwigRenderer $renderer)
	{
		$this->renderer = $renderer;

		parent::__construct();
	}

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Reset Twig Cache');

		// Check if caching is enabled
		if ($this->getApplication()->get('template.cache.enabled', false) === false)
		{
			$symfonyStyle->comment('Twig caching is disabled.');

			return 0;
		}

		$symfonyStyle->comment('Resetting Twig cache.');

		$twigCache = $this->getApplication()->get('template.cache.path', '');

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

		if (count($errorFiles))
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
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('twig:reset-cache');
		$this->setDescription('Resets the Twig template cache');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command resets the Twig template cache

<info>php %command.full_name% %command.name%</info>
EOF
		);
	}
}

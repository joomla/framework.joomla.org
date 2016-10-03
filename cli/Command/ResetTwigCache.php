<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatusCli\Command;

use Joomla\Filesystem\Folder;
use Joomla\Status\Helper;
use Joomla\StatusCli\Application;

/**
 * CLI Command to reset the Twig cache if enabled
 *
 * @since  1.0
 */
class ResetTwigCache
{
	/**
	 * Application object
	 *
	 * @var    Application
	 * @since  1.0
	 */
	private $app;

	/**
	 * Database driver object
	 *
	 * @var    \Joomla\Database\DatabaseDriver
	 * @since  1.0
	 */
	private $db;

	/**
	 * Class constructor
	 *
	 * @param   Application  $app  Application object
	 *
	 * @since   1.0
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->db  = $this->app->getContainer()->get('db');
	}

	/**
	 * Execute the command
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// Check if caching is enabled
		$twigCache = $this->app->get('template.cache', false);

		if ($twigCache === false)
		{
			$this->app->out('<info>Twig caching is disabled.</info>');

			return;
		}

		// Display status
		$this->app->out('Resetting Twig Cache.');

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
        /** @var \Joomla\Renderer\TwigRenderer $twigRenderer */
        $twigRenderer = $this->app->getContainer()->get('renderer');
        $engine       = $twigRenderer->getRenderer();
        $errorFiles   = [];

		foreach ($files as $file)
		{
			$template = str_replace(JPATH_TEMPLATES . '/', '', $file);

			try
			{
				$engine->loadTemplate($template);
			}
			catch (\Twig_Error $e)
			{
				$errorFiles[] = $file;
			}
		}

		if (count($errorFiles))
		{
			$msg = '<comment>The following Twig resources failed to cache: ' . implode(', ', $errorFiles) . '</comment>';
		}
		else
		{
			$msg = '<info>The cached Twig resources were successfully created.</info>';
		}

		$this->app->out($msg);
	}
}

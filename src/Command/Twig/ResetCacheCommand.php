<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command\Twig;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\Filesystem\Folder;
use Joomla\FrameworkWebsite\CommandInterface;
use Joomla\Input\Input;
use Joomla\Renderer\TwigRenderer;

/**
 * Update command
 *
 * @method         \Joomla\FrameworkWebsite\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\FrameworkWebsite\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class ResetCacheCommand extends AbstractController implements CommandInterface
{
	/**
	 * The template renderer
	 *
	 * @var    TwigRenderer
	 * @since  1.0
	 */
	private $renderer;

	/**
	 * Instantiate the controller.
	 *
	 * @param   TwigRenderer         $renderer  The template renderer
	 * @param   Input                $input     The input object.
	 * @param   AbstractApplication  $app       The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(TwigRenderer $renderer, Input $input = null, AbstractApplication $app = null)
	{
		parent::__construct($input, $app);

		$this->renderer = $renderer;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Reset Twig Cache');

		// Check if caching is enabled
		$twigCache = $this->getApplication()->get('template.cache', false);

		if ($twigCache === false)
		{
			$this->getApplication()->out('<info>Twig caching is disabled.</info>');

			return true;
		}

		$this->getApplication()->out('<info>Resetting Twig Cache</info>');

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

		$this->getApplication()->out($msg);

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
		return 'Resets the Twig template cache.';
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
		return 'Reset Twig Cache';
	}
}

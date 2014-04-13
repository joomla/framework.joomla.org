<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Service;

use BabDev\Renderer\TwigRenderer;

use Joomla\Application\AbstractApplication;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

use Joomla\Status\Renderer\TwigExtension;
use Joomla\Status\Renderer\TwigLoader;

/**
 * Twig renderer service provider
 *
 * @since  1.0
 */
class TwigRendererProvider implements ServiceProviderInterface
{
	/**
	 * Application object
	 *
	 * @var    AbstractApplication
	 * @since  1.0
	 */
	private $app;

	/**
	 * Configuration instance
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $config;

	/**
	 * Constructor.
	 *
	 * @param   AbstractApplication  $app     Application object
	 * @param   array                $config  Configuration array for the renderer
	 *
	 * @since   1.0
	 */
	public function __construct(AbstractApplication $app, array $config = array())
	{
		$this->app    = $app;
		$this->config = $config;
	}

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register(Container $container)
	{
		$container->set('BabDev\\Renderer\\RendererInterface',
			function (Container $container) {
				/* @type  \Joomla\Registry\Registry  $config */
				$config = $container->get('config');

				// Instantiate our extended Twig filesystem loader
				$loader = new TwigLoader($config->get('template.path'), $config->get('template.extension'));

				// Instantiate the renderer object
				$renderer = new TwigRenderer($loader, $this->config);

				// Add our Twig extension
				$renderer->addExtension(new TwigExtension($this->app));

				// Set the Lexer object
				$renderer->setLexer(
					new \Twig_Lexer($renderer, ['delimiters' => [
						'tag_comment'  => ['{#', '#}'],
						'tag_block'    => ['{%', '%}'],
						'tag_variable' => ['{{', '}}']
					]])
				);

				return $renderer;
			},
			true,
			true
		);

		// Alias the renderer
		$container->alias('renderer', 'BabDev\\Renderer\\RendererInterface');
	}
}

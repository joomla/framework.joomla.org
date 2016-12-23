<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Service;

use Joomla\Application\AbstractApplication;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Renderer\RendererInterface;
use Joomla\Renderer\TwigRenderer;
use Joomla\Status\Renderer\ApplicationContext;
use Joomla\Status\Renderer\TwigExtension;
use Joomla\Status\Renderer\TwigLoader;
use Joomla\Status\Renderer\TwigRuntime;
use Joomla\Status\Renderer\TwigRuntimeLoader;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

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
	 * Constructor.
	 *
	 * @param   AbstractApplication  $app  Application object
	 *
	 * @since   1.0
	 */
	public function __construct(AbstractApplication $app)
	{
		$this->app = $app;
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
		$container->share(
			RendererInterface::class,
			function (Container $container) {
				/* @type  \Joomla\Registry\Registry  $config */
				$config = $container->get('config');

				// Pull down the renderer config
				$rendererConfig = (array) $config->get('template');

				// If the cache isn't false, then it should be a file path relative to the app root
				$rendererConfig['cache'] = $rendererConfig['cache'] === false ? $rendererConfig['cache'] : JPATH_ROOT . '/' . $rendererConfig['cache'];

				// Instantiate the Twig environment
				$environment = new \Twig_Environment(new \Twig_Loader_Filesystem([JPATH_TEMPLATES]), $rendererConfig);

				// Add our Twig runtime loader
				$loader = new TwigRuntimeLoader;
				$loader->setContainer($container);

				$environment->addRuntimeLoader($loader);

				// Add our Twig extension
				$environment->addExtension(new TwigExtension);

				// Add the debug extension if enabled
				if ($config->get('template.debug'))
				{
					$environment->addExtension(new \Twig_Extension_Debug);
				}

				// Set the Lexer object
				$environment->setLexer(
					new \Twig_Lexer(
						$environment, [
							'delimiters' => [
								'tag_comment'  => ['{#', '#}'],
								'tag_block'    => ['{%', '%}'],
								'tag_variable' => ['{{', '}}'],
							],
						]
					)
				);

				return new TwigRenderer($environment);
			},
			true
		);

		// Alias the renderer
		$container->alias('renderer', RendererInterface::class);

		$container->share(
			TwigRuntime::class,
			function (Container $container) {
				return new TwigRuntime($this->app, $container->get(Packages::class));
			}
		);

		$container->share(
			Packages::class,
			function (Container $container) {
				$version = file_exists(JPATH_ROOT . '/current_SHA') ? trim(file_get_contents(JPATH_ROOT . '/current_SHA')) : md5(get_class($this));
				$context = new ApplicationContext($this->app);

				return new Packages(
					new PathPackage('media', new StaticVersionStrategy($version), $context),
					[
						'img' => new PathPackage('media', new EmptyVersionStrategy, $context)
					]
				);
			}
		);
	}
}

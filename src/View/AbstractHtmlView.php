<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\View;

use Joomla\Status\View\Renderer\TwigExtension;

use Joomla\Application\AbstractApplication;
use Joomla\Model\ModelInterface;
use Joomla\View\AbstractView;
use Joomla\View\Renderer\RendererInterface;
use Joomla\View\Renderer\Twig;

/**
 * Abstract HTML view class for the application
 *
 * @since  1.0
 */
abstract class AbstractHtmlView extends AbstractView
{
	/**
	 * The view layout
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $layout = 'index';

	/**
	 * The view template engine
	 *
	 * @var    RendererInterface
	 * @since  1.0
	 */
	private $renderer = null;

	/**
	 * Method to instantiate the view
	 *
	 * @param   AbstractApplication  $app            The application object
	 * @param   ModelInterface       $model          The model object
	 * @param   array                $templatePaths  Array of paths for template lookup
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function __construct(AbstractApplication $app, ModelInterface $model, $templatePaths = array())
	{
		$renderer = new Twig(
			array(
				'templates_base_dir' => JPATH_TEMPLATES,
				'environment'        => array(
					'debug' => true
				)
			)
		);

		$renderer->addExtension(new TwigExtension($app));

		// Register additional paths.
		if (!empty($templatePaths))
		{
			$renderer->setTemplatesPaths($templatePaths, true);
		}

		$this->setRenderer($renderer);

		parent::__construct($model);
	}

	/**
	 * Magic toString method that is a proxy for the render method
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output
	 *
	 * @param   string  $output  The output to escape
	 *
	 * @return  string  The escaped output
	 *
	 * @see     ViewInterface::escape()
	 * @since   1.0
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get the view layout
	 *
	 * @return  string  The layout name
	 *
	 * @since   1.0
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to get the renderer object
	 *
	 * @return  RendererInterface  The renderer object
	 *
	 * @since   1.0
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * Method to render the view
	 *
	 * @return  string  The rendered view
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		return $this->renderer->render($this->layout);
	}

	/**
	 * Method to set the view layout
	 *
	 * @param   string  $layout  The layout name
	 *
	 * @return  $this  Method supports chaining
	 *
	 * @since   1.0
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Method to set the renderer object
	 *
	 * @param   RendererInterface  $renderer  The renderer object
	 *
	 * @return  $this  Method supports chaining
	 *
	 * @since   1.0
	 */
	public function setRenderer(RendererInterface $renderer)
	{
		$this->renderer = $renderer;

		return $this;
	}
}

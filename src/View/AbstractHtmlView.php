<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\View;

use BabDev\Renderer\RendererInterface;

use Joomla\Model\ModelInterface;
use Joomla\View\AbstractView;
use Joomla\View\Renderer\Twig;

/**
 * Abstract HTML view class for the application
 *
 * @since  1.0
 */
abstract class AbstractHtmlView extends AbstractView
{
	/**
	 * The data array to pass to the renderer engine
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $data = array();

	/**
	 * The name of the layout to render
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $layout;

	/**
	 * The renderer object
	 *
	 * @var    RendererInterface
	 * @since  1.0
	 */
	private $renderer;

	/**
	 * Class constructor
	 *
	 * @param   ModelInterface     $model     The model object.
	 * @param   RendererInterface  $renderer  The renderer object.
	 *
	 * @since   1.0
	 */
	public function __construct(ModelInterface $model, RendererInterface $renderer)
	{
		parent::__construct($model);

		$this->setRenderer($renderer);
	}

	/**
	 * Retrieves the data array
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Retrieves the layout name
	 *
	 * @return  string
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getLayout()
	{
		if (is_null($this->layout))
		{
			throw new \RuntimeException('The layout name is not set.');
		}

		return $this->layout;
	}

	/**
	 * Retrieves the renderer object
	 *
	 * @return  RendererInterface
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getRenderer()
	{
		if (is_null($this->renderer))
		{
			throw new \RuntimeException('The renderer object is not set.');
		}

		return $this->renderer;
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		// Before rendering, inject the build data into the data array if it exists
		$data = $this->getData();

		if (file_exists(JPATH_ROOT . '/last_build.json'))
		{
			$build = json_decode(file_get_contents(JPATH_ROOT . '/last_build.json'));
			$data['build'] = $build;
		}

		return $this->getRenderer()->render($this->getLayout(), $data);
	}

	/**
	 * Sets the data array
	 *
	 * @param   array  $data  The data array.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function setData(array $data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Sets the layout name
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Sets the renderer object
	 *
	 * @param   RendererInterface  $renderer  The renderer object.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function setRenderer(RendererInterface $renderer)
	{
		$this->renderer = $renderer;

		return $this;
	}
}

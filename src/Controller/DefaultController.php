<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Controller;

use Joomla\Controller\AbstractController;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use Joomla\View\ViewInterface;

/**
 * Default controller class for the application
 *
 * @since  1.0
 */
class DefaultController extends AbstractController implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The default view for the application
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $defaultView = 'landing';

	/**
	 * State object to inject into the model
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  1.0
	 */
	protected $modelState = null;

	/**
	 * Execute the controller
	 *
	 * This is a generic method to execute and render a view and is not suitable for tasks
	 *
	 * @return  boolean  True if controller finished execution
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		try
		{
			// Initialize the view object
			$view = $this->initializeView();

			// Render our view.
			$this->getApplication()->setBody($view->render());

			return true;
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(sprintf('Error: ' . $e->getMessage()), $e->getCode());
		}
	}


	/**
	 * Method to initialize the model object
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function initializeModel()
	{
		$model = '\\Joomla\\Status\\Model\\' . ucfirst($this->getInput()->getWord('view', $this->defaultView)) . 'Model';

		// If a model doesn't exist for our view, revert to the default model
		if (!class_exists($model))
		{
			$model = '\\Joomla\\Status\\Model\\DefaultModel';
		}

		$object = $this->getContainer()->buildObject($model);

		if ($this->modelState instanceof Registry)
		{
			$object->setState($this->modelState);
		}

		// Hack - Set the package list to our models, will be DI'd when we have the capability
		if (method_exists($object, 'setPackages'))
		{
			$object->setPackages($this->getContainer()->get('application.packages'));
		}

		$this->getContainer()->set($model, $object)->alias('Joomla\\Model\\ModelInterface', $model);
	}

	/**
	 * Method to initialize the renderer object
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function initializeRenderer()
	{
		// Add the provider to the DI container if it doesn't exist
		if (!$this->getContainer()->exists('renderer'))
		{
			$type = $this->getContainer()->get('config')->get('template.renderer');

			// Set the class name for the renderer's service provider
			$class = '\\Joomla\\Status\\Service\\' . ucfirst($type) . 'RendererProvider';

			// Sanity check
			if (!class_exists($class))
			{
				throw new \RuntimeException(sprintf('Renderer provider for renderer type %s not found.', ucfirst($type)));
			}

			$this->getContainer()->registerServiceProvider(new $class($this->getApplication()));
		}
	}

	/**
	 * Method to initialize the view object
	 *
	 * @return  \Joomla\View\ViewInterface  View object
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function initializeView() : ViewInterface
	{
		// Initialize the model object
		$this->initializeModel();

		$view   = ucfirst($this->getInput()->getWord('view', $this->defaultView));
		$format = ucfirst($this->getInput()->getWord('format', 'html'));

		$class = '\\Joomla\\Status\\View\\' . $view . '\\' . $view . $format . 'View';

		// Ensure the class exists, fall back to default otherwise
		if (!class_exists($class))
		{
			$class = '\\Joomla\\Status\\View\\Default' . $format . 'View';

			// If we still have nothing, abort mission
			if (!class_exists($class))
			{
				throw new \RuntimeException(sprintf('A view class was not found for the %s view in the %s format.', $view, $format));
			}
		}

		// The view classes have different dependencies, switch it from here
		switch ($format)
		{
			case 'Json' :
				// We can just instantiate the view here
				$object = $this->getContainer()->buildObject($class);

				break;

			case 'Html' :
			default     :
				// HTML views require a renderer object too, fetch it
				$this->initializeRenderer();

				// Instantiate the view now
				$object = $this->getContainer()->buildObject($class);

				// We need to set the layout too
				$object->setLayout(strtolower($view) . '.' . strtolower($this->getInput()->getWord('layout', 'index')) . '.twig');

				// Add the current view path to the loader's lookup
				$object->getRenderer()->getRenderer()->getLoader()->addPath(JPATH_TEMPLATES . '/' . strtolower($view));

				break;
		}

		return $object;
	}
}

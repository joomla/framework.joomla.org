<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Controller;

use Joomla\Controller\AbstractController;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\Container;

/**
 * Default controller class for the application
 *
 * @since  1.0
 */
class DefaultController extends AbstractController implements ContainerAwareInterface
{
	/**
	 * DI Container
	 *
	 * @var    Container
	 * @since  1.0
	 */
	private $container;

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
		// Get the input
		$input = $this->getInput();

		$task = $input->getCmd('task', 'view');

		// Get some data from the request
		$vName   = $input->getWord('view', $this->defaultView);
		$vFormat = strtolower($input->getWord('format', 'html'));

		if (is_null($input->get('layout')))
		{
			if ($task == 'view' && $input->get('id') == null)
			{
				$input->set('layout', 'index');
			}
			elseif ($task == 'view')
			{
				$input->set('layout', 'view');
			}
			elseif ($task != null)
			{
				$this->$task();
			}
		}

		$lName = $input->get('layout');

		$input->set('view', $vName);

		$vClass = '\\Joomla\\Status\\View\\' . ucfirst($vName) . '\\' . ucfirst($vName) . ucfirst($vFormat) . 'View';
		$mClass = '\\Joomla\\Status\\Model\\' . ucfirst($vName) . 'Model';

		// If a model doesn't exist for our view, revert to the default model
		if (!class_exists($mClass))
		{
			$mClass = '\\Joomla\\Status\\Model\\DefaultModel';

			// If there still isn't a class, panic.
			if (!class_exists($mClass))
			{
				throw new \RuntimeException(sprintf('No model found for view %s', $vName), 500);
			}
		}

		// Make sure the view class exists, otherwise revert to the default
		if (!class_exists($vClass))
		{
			$vClass = '\\Joomla\\Status\\View\\Default' . ucfirst($vFormat) . 'View';

			// If there still isn't a class, panic.
			if (!class_exists($vClass))
			{
				throw new \RuntimeException(sprintf('A view class was not found for the %s format.', $vFormat), 500);
			}
		}

		// Register the templates paths for the view
		$paths = array();

		$path = JPATH_TEMPLATES . '/' . $vName . '/';

		if (is_dir($path))
		{
			$paths[] = $path;
		}

		// If the controller has anything to inject into the model, do it here via the state
		$this->initializeModel();

		$view = new $vClass($this->getApplication(), new $mClass($this->getContainer()->get('db'), $this->modelState), $paths);

		// If this is a HTML view, set the layout
		if ($vFormat == 'html')
		{
			$view->setLayout($vName . '.' . $lName);
		}

		try
		{
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
	 * Get the DI container
	 *
	 * @return  Container
	 *
	 * @since   1.0
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Method to initialize data to inject into the model via the state
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function initializeModel()
	{
		return;
	}

	/**
	 * Set the DI container
	 *
	 * @param   Container  $container  The DI container
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}
}

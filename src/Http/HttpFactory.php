<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Http;

use DebugBar\DebugBar;
use Joomla\FrameworkWebsite\Http\Transport\DebugTransport;
use Joomla\Http\HttpFactory as BaseFactory;
use Joomla\Http\TransportInterface;

/**
 * Extended HTTP factory
 */
class HttpFactory extends BaseFactory
{
	/**
	 * Application debug bar
	 *
	 * @var  DebugBar
	 */
	private $debugBar;

	/**
	 * Constructor.
	 *
	 * @param   DebugBar  $debugBar  Application debug bar.
	 */
	public function __construct(DebugBar $debugBar)
	{
		$this->debugBar = $debugBar;
	}

	/**
	 * Finds an available TransportInterface object for communication
	 *
	 * @param   array|\ArrayAccess  $options  Options for creating TransportInterface object
	 * @param   array|string        $default  Adapter (string) or queue of adapters (array) to use
	 *
	 * @return  TransportInterface|boolean  Interface sub-class or boolean false if no adapters are available
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function getAvailableDriver($options = [], $default = null)
	{
		$wrappedDriver = parent::getAvailableDriver($options, $default);

		return new DebugTransport($this->debugBar, $wrappedDriver, $options);
	}
}

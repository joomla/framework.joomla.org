<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Http\Transport;

use DebugBar\DebugBar;
use Joomla\Http\AbstractTransport;
use Joomla\Http\Response;
use Joomla\Http\TransportInterface;
use Joomla\Uri\UriInterface;

/**
 * HTTP transport class for logging debug information.
 */
class DebugTransport extends AbstractTransport
{
	/**
	 * Application debug bar
	 *
	 * @var  DebugBar
	 */
	private $debugBar;

	/**
	 * Wrapped transport class
	 *
	 * @var TransportInterface
	 */
	private $wrappedTransport;

	/**
	 * Constructor.
	 *
	 * @param   DebugBar            $debugBar          Application debug bar.
	 * @param   TransportInterface  $wrappedTransport  Wrapped transport class.
	 * @param   array|\ArrayAccess  $options           Client options array.
	 */
	public function __construct(DebugBar $debugBar, TransportInterface $wrappedTransport, $options = [])
	{
		parent::__construct($options);

		$this->debugBar         = $debugBar;
		$this->wrappedTransport = $wrappedTransport;
	}

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param   string        $method     The HTTP method for sending the request.
	 * @param   UriInterface  $uri        The URI to the resource to request.
	 * @param   mixed         $data       Either an associative array or a string to be sent with the request.
	 * @param   array         $headers    An array of request headers to send with the request.
	 * @param   integer       $timeout    Read timeout in seconds.
	 * @param   string        $userAgent  The optional user agent string to send with the request.
	 *
	 * @return  Response
	 */
	public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure($method . ' ' . $uri->toString(['scheme', 'host', 'port', 'path', 'query', 'fragment']));

		try
		{
			$response = $this->wrappedTransport->request($method, $uri, $data, $headers, $timeout, $userAgent);
		}
		finally
		{
			$collector->stopMeasure($method . ' ' . $uri->toString(['scheme', 'host', 'port', 'path', 'query', 'fragment']));
		}

		return $response;
	}

	/**
	 * Method to check if HTTP transport layer available for using
	 *
	 * @return  boolean  True if available else false
	 */
	public static function isSupported()
	{
		return true;
	}
}

<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Mock;

/**
 * Class to create a mock Joomla\Status\Application instance
 *
 * @since  1.0
 */
class Application extends BaseMock
{
	/**
	 * Mock storage for the response body.
	 *
	 * @var    array
	 * @since  1.0
	 */
	public static $body = array();

	/**
	 * Mock storage for the response headers.
	 *
	 * @var    array
	 * @since  1.0
	 */
	public static $headers = array();

	/**
	 * Mock storage for the response cache status.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public static $cachable = false;

	/**
	 * Creates an instance of the mock Application object.
	 *
	 * @param   \PHPUnit_Framework_TestCase  $test  A test object.
	 *
	 * @return  \PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   1.0
	 */
	public function create($test)
	{
		// Collect all the relevant methods in DatabaseDriver and merge any additional parameters
		$methods = [
			// \Joomla\Status\Application
			'doExecute',
			'initialise',
			'setErrorOutput',
			'setErrorHeaderResponse',
			'getContainer',
			'setContainer',
			// \Joomla\Application\AbstractWebApplication
			'compress',
			'respond',
			'redirect',
			'allowCache',
			'setHeader',
			'getHeaders',
			'clearHeaders',
			'sendHeaders',
			'setBody',
			'prependBody',
			'appendBody',
			'getBody',
			'getSession',
			'checkConnectionAlive',
			'checkHeadersSent',
			'detectRequestUri',
			'header',
			'isSSLConnection',
			'setSession',
			'loadSystemUris',
			'checkToken',
			'getFormToken',
			// \Joomla\Application\AbstractApplication
			'close',
			'execute',
			'get',
			'getLogger',
			'set',
			'setConfiguration',
			'setLogger'
		];

		// Create the mock.
		$mockObject = $test->getMock('\\Joomla\\Status\\Application', $methods, [], '', false);

		// Mock selected methods.
		$this->assignMockReturns($test, $mockObject, ['close' => true]);

		$this->assignMockCallbacks(
			$test,
			$mockObject,
			[
				'appendBody' => [(is_callable([$test, 'mockAppendBody']) ? $test : get_called_class()), 'mockAppendBody'],
				'getBody' => [(is_callable([$test, 'mockGetBody']) ? $test : get_called_class()), 'mockGetBody'],
				'prependBody' => [(is_callable([$test, 'mockPrependBody']) ? $test : get_called_class()), 'mockPrependBody'],
				'setBody' => [(is_callable([$test, 'mockSetBody']) ? $test : get_called_class()), 'mockSetBody'],
				'getHeaders' => [(is_callable([$test, 'mockGetHeaders']) ? $test : get_called_class()), 'mockGetHeaders'],
				'setHeader' => [(is_callable([$test, 'mockSetHeader']) ? $test : get_called_class()), 'mockSetHeader'],
				'clearHeaders' => [(is_callable([$test, 'mockClearHeaders']) ? $test : get_called_class()), 'mockClearHeaders'],
				'allowCache' => [(is_callable([$test, 'mockAllowCache']) ? $test : get_called_class()), 'mockAllowCache'],
			]
		);

		// Reset the body storage.
		static::$body = array();

		// Reset the headers storage.
		static::$headers = array();

		// Reset the cache storage.
		static::$cachable = false;

		return $mockObject;
	}

	/**
	 * Mock the Application object's appendBody method.
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public static function mockAppendBody($content)
	{
		array_push(static::$body, (string) $content);
	}

	/**
	 * Mock the Application object's getBody method.
	 *
	 * @param   boolean  $asArray  True to return the body as an array of strings.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public static function mockGetBody($asArray = false)
	{
		return $asArray ? static::$body : implode((array) static::$body);
	}

	/**
	 * Mock the Application object's appendBody method.
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public static function mockPrependBody($content)
	{
		array_unshift(static::$body, (string) $content);
	}

	/**
	 * Mock the Application object's setBody method.
	 *
	 * @param   string  $content  The body of the response.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function mockSetBody($content)
	{
		static::$body = array($content);
	}

	/**
	 * Mock the Application object's getHeaders method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function mockGetHeaders()
	{
		return static::$headers;
	}

	/**
	 * Mock the Application object's setHeader method.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function mockSetHeader($name, $value, $replace = false)
	{
		// Sanitize the input values.
		$name = (string) $name;
		$value = (string) $value;

		// If the replace flag is set, unset all known headers with the given name.
		if ($replace)
		{
			foreach (static::$headers as $key => $header)
			{
				if ($name == $header['name'])
				{
					unset(static::$headers[$key]);
				}
			}

			// Clean up the array as unsetting nested arrays leaves some junk.
			static::$headers = array_values(static::$headers);
		}

		// Add the header to the internal array.
		static::$headers[] = array('name' => $name, 'value' => $value);
	}

	/**
	 * Mock the Application object's clearHeaders method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function mockClearHeaders()
	{
		static::$headers = array();
	}

	/**
	 * Mock the Application object's allowCache method.
	 *
	 * @param   boolean  $allow  True to allow browser caching.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public static function mockAllowCache($allow = null)
	{
		if ($allow !== null)
		{
			static::$cachable = (bool) $allow;
		}

		return static::$cachable;
	}
}

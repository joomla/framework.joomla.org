<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Mock;

/**
 * Base mock class for common code
 *
 * @since  1.0
 */
abstract class BaseMock
{
	/**
	 * Assigns mock callbacks to methods.
	 *
	 * @param   \PHPUnit_Framework_TestCase               $test        A test object.
	 * @param   \PHPUnit_Framework_MockObject_MockObject  $mockObject  The mock object.
	 * @param   array|\ArrayAccess                        $array       An array of methods names to mock with callbacks.
	 *
	 * @return  void
	 *
	 * @note    This method assumes that the mock callback is named {mock}{method name}.
	 * @since   1.0
	 */
	public function assignMockCallbacks($test, $mockObject, $array)
	{
		foreach ($array as $index => $method)
		{
			if (is_array($method))
			{
				$methodName = $index;
				$callback = $method;
			}
			else
			{
				$methodName = $method;
				$callback = array(get_called_class(), 'mock' . $method);
			}

			$mockObject->expects($test->any())
				->method($methodName)
				->willReturnCallback($callback);
		}
	}

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   \PHPUnit_Framework_TestCase               $test          A test object.
	 * @param   \PHPUnit_Framework_MockObject_MockObject  $mockObject  The mock object.
	 * @param   array|\ArrayAccess                        $array       An associative array of methods to mock with return values:
	 *                                                                 string (method name) => mixed (return value)
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function assignMockReturns($test, $mockObject, $array)
	{
		foreach ($array as $method => $return)
		{
			$mockObject->expects($test->any())
				->method($method)
				->willReturn($return);
		}
	}
}

<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests\Mock;

/**
 * Class to create a mock Joomla\Database\DatabaseDriver instance
 *
 * @since  1.0
 */
class DatabaseDriver
{
	/**
	 * A query string or object.
	 *
	 * @var    DatabaseQuery
	 * @since  1.0
	 */
	public static $lastQuery;

	/**
	 * Cached DatabaseDriver object to use in callbacks
	 *
	 * @var    \PHPUnit_Framework_MockObject_MockObject
	 * @since  1.0
	 */
	public static $dbo;

	/**
	 * Assigns mock callbacks to methods.
	 *
	 * @param   \PHPUnit_Framework_TestCase               $test          A test object.
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

	/**
	 * Creates an instance of the mock DatabaseDriver object.
	 *
	 * @param   \PHPUnit_Framework_TestCase  $test          A test object.
	 * @param   string                       $driver        Optional driver to create a sub-class of DatabaseDriver.
	 * @param   array                        $extraMethods  An array of additional methods to add to the mock.
	 * @param   string                       $nullDate      A null date string for the driver.
	 * @param   string                       $dateFormat    A date format for the driver.
	 *
	 * @return  \PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   1.0
	 */
	public function create($test, $driver = '', array $extraMethods = array(), $nullDate = '0000-00-00 00:00:00', $dateFormat = 'Y-m-d H:i:s')
	{
		// Collect all the relevant methods in DatabaseDriver and merge any additional parameters
		$methods = array_merge($extraMethods, [
			'getConnectors',
			'getInstance',
		    'splitSql',
		    'connect',
		    'connected',
		    'disconnect',
		    'dropTable',
		    'escape',
		    'fetchArray',
		    'fetchAssoc',
		    'fetchObject',
		    'freeResult',
		    'getAffectedRows',
		    'getCollation',
		    'getConnection',
		    'getCount',
		    'getDatabase',
		    'getDateFormat',
		    'getMinimum',
		    'getNullDate',
		    'getNumRows',
		    'getPrefix',
		    'getExporter',
		    'getImporter',
		    'getQuery',
		    'getIterator',
		    'getTableColumns',
		    'getTableCreate',
		    'getTableKeys',
		    'getTableList',
		    'hasUTFSupport',
		    'getVersion',
		    'insertid',
		    'insertObject',
		    'isMinimumVersion',
		    'loadAssoc',
		    'loadAssocList',
		    'loadColumn',
		    'loadObject',
		    'loadObjectList',
		    'loadResult',
		    'loadRow',
		    'loadRowList',
		    'log',
		    'lockTable',
		    'quote',
		    'quoteName',
		    'quoteNameStr',
		    'replacePrefix',
		    'renameTable',
		    'select',
		    'setDebug',
		    'setQuery',
		    'setLogger',
		    'setUTF',
		    'transactionCommit',
		    'transactionRollback',
		    'transactionStart',
		    'truncateTable',
		    'updateObject',
		    'execute',
		    'unlockTables'
		]);

		if (empty($driver))
		{
			$class = '\\Joomla\\Database\\DatabaseDriver';
		}
		else
		{
			$class = '\\Joomla\\Database\\' . ucfirst(strtolower($driver)) . '\\' . ucfirst(strtolower($driver)) . 'Driver';
		}

		// Create the mock.
		$mockObject = $test->getMock($class, $methods, [['driver' => $driver]], '', false);

		// Mock selected methods.
		$this->assignMockReturns(
			$test,
			$mockObject,
			[
				'getNullDate' => $nullDate,
				'getDateFormat' => $dateFormat
			]
		);

		$this->assignMockCallbacks(
			$test,
			$mockObject,
			[
				'escape' => array((is_callable(array($test, 'mockEscape')) ? $test : __CLASS__), 'mockEscape'),
				'getQuery' => array((is_callable(array($test, 'mockGetQuery')) ? $test : __CLASS__), 'mockGetQuery'),
				'quote' => array((is_callable(array($test, 'mockQuote')) ? $test : __CLASS__), 'mockQuote'),
				'quoteName' => array((is_callable(array($test, 'mockQuoteName')) ? $test : __CLASS__), 'mockQuoteName'),
				'setQuery' => array((is_callable(array($test, 'mockSetQuery')) ? $test : __CLASS__), 'mockSetQuery'),
			]
		);

		self::$dbo = $mockObject;

		return $mockObject;
	}

	/**
	 * Callback for the DatabaseDriver's escape method.
	 *
	 * @param   string  $text  The input text.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public static function mockEscape($text)
	{
		return "_{$text}_";
	}

	/**
	 * Callback for the DatabaseDriver's getQuery method.
	 *
	 * @param   boolean  $new  True to get a new query, false to get the last query.
	 *
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   1.0
	 */
	public static function mockGetQuery($new = false)
	{
		if ($new || is_null(self::$lastQuery))
		{
			return new DatabaseQuery(self::$dbo);
		}
		else
		{
			return self::$lastQuery;
		}
	}

	/**
	 * Callback for the DatabaseDriver's quote method.
	 *
	 * @param   string   $value   The value to be quoted.
	 * @param   boolean  $escape  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The value passed wrapped in quotes.
	 *
	 * @since   1.0
	 */
	public static function mockQuote($value, $escape = true)
	{
		if (is_array($value))
		{
			foreach ($value as $k => $v)
			{
				$value[$k] = self::mockQuote($v, $escape);
			}

			return $value;
		}

		return '\'' . ($escape ? self::mockEscape($value) : $value) . '\'';
	}

	/**
	 * Callback for the DatabaseDriver's quoteName method.
	 *
	 * @param   string  $value  The value to be quoted.
	 *
	 * @return  string  The value passed wrapped in quotes.
	 *
	 * @since   1.0
	 */
	public static function mockQuoteName($value)
	{
		return "`$value`";
	}

	/**
	 * Callback for the DatabaseDriver's setQuery method.
	 *
	 * @param   string  $query  The query.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function mockSetQuery($query)
	{
		self::$lastQuery = $query;

		return self::$dbo;
	}
}

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
class DatabaseDriver extends BaseMock
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
				'escape' => [(is_callable([$test, 'mockEscape']) ? $test : __CLASS__), 'mockEscape'],
				'getQuery' => [(is_callable([$test, 'mockGetQuery']) ? $test : __CLASS__), 'mockGetQuery'],
				'quote' => [(is_callable([$test, 'mockQuote']) ? $test : __CLASS__), 'mockQuote'],
				'quoteName' => [(is_callable([$test, 'mockQuoteName']) ? $test : __CLASS__), 'mockQuoteName'],
				'setQuery' => [(is_callable([$test, 'mockSetQuery']) ? $test : __CLASS__), 'mockSetQuery'],
			]
		);

		static::$dbo = $mockObject;

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
		if ($new || is_null(static::$lastQuery))
		{
			return new DatabaseQuery(static::$dbo);
		}
		else
		{
			return static::$lastQuery;
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
				$value[$k] = static::mockQuote($v, $escape);
			}

			return $value;
		}

		return '\'' . ($escape ? static::mockEscape($value) : $value) . '\'';
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
		static::$lastQuery = $query;

		return static::$dbo;
	}
}

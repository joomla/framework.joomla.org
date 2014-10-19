<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Tests;

use Joomla\Status\Helper;

/**
 * Test class for \Joomla\Status\Helper
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Data provider to test getPackageDisplayName
	 *
	 * @return  array
	 */
	public function dataGetPackageDisplayName()
	{
		return [
			'application'   => ['Application', 'application'],
		    'datetime'      => ['DateTime', 'datetime'],
		    'di'            => ['DI', 'di'],
		    'github'        => ['GitHub', 'github'],
		    'http'          => ['HTTP', 'http'],
		    'ldap'          => ['LDAP', 'ldap'],
		    'linkedin'      => ['LinkedIn', 'linkedin'],
		    'oauth1'        => ['OAuth1', 'oauth1'],
		    'oauth2'        => ['OAuth2', 'oauth2'],
		    'openstreetmap' => ['OpenStreetMap', 'openstreetmap'],
		    'uri'           => ['URI', 'uri']
		];
	}

	/**
	 * @testdox  The return for getPackageDisplayName with a package matching a case
	 *
	 * @param   string  $expected  Expected result
	 * @param   string  $input     Method input
	 *
	 * @covers       \Joomla\Status\Helper::getPackageDisplayName
	 * @dataProvider dataGetPackageDisplayName
	 */
	public function testTheReturnForGetPackageDisplayNameWithAPackageMatchingACase($expected, $input)
	{
		$this->assertSame($expected, (new Helper)->getPackageDisplayName($input));
	}

	/**
	 * @testdox  The return for parseComposer contains the specified key
	 *
	 * @covers   \Joomla\Status\Helper::parseComposer
	 */
	public function testTheReturnForParseComposerContainsTheSpecifiedKey()
	{
		$this->assertArrayHasKey('application', (new Helper)->parseComposer());
	}
}

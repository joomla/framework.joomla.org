<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Model;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\Mysql\MysqlQuery;
use Joomla\Model\{
	DatabaseModelInterface, DatabaseModelTrait
};

/**
 * Model class for contributors
 */
class ContributorModel implements DatabaseModelInterface
{
	use DatabaseModelTrait;

	/**
	 * Instantiate the model.
	 *
	 * @param   DatabaseDriver  $db  The database adapter.
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->setDb($db);
	}

	/**
	 * Get the contributor data
	 *
	 * @return  array
	 */
	public function getContributors() : array
	{
		$db = $this->getDb();

		/** @var MysqlQuery $query */
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__contributors'));

		return $db->setQuery($query)->loadObjectList('id');
	}
}

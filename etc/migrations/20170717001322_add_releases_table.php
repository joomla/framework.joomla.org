<?php

use Phinx\Migration\AbstractMigration;

class AddReleasesTable extends AbstractMigration
{
	/**
	 * Change Method.
	 *
	 * Write your reversible migrations using this method.
	 *
	 * More information on writing migrations is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
	 *
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 *
	 *    createTable
	 *    renameTable
	 *    addColumn
	 *    renameColumn
	 *    addIndex
	 *    addForeignKey
	 *
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change()
	{
		$this->table('releases')
			->addColumn('package_id', 'integer')
			->addColumn('version', 'string', ['limit' => 25, 'null' => false])
			->addColumn('tests', 'integer', ['null' => true, 'default' => 0])
			->addColumn('assertions', 'integer', ['null' => true, 'default' => 0])
			->addColumn('errors', 'integer', ['null' => true, 'default' => 0])
			->addColumn('failures', 'integer', ['null' => true, 'default' => 0])
			->addColumn('total_lines', 'integer', ['null' => true, 'default' => 0])
			->addColumn('lines_covered', 'integer', ['null' => true, 'default' => 0])
			->create();
	}
}

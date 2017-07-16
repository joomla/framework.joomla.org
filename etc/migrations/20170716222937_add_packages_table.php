<?php

use Phinx\Migration\AbstractMigration;

class AddPackagesTable extends AbstractMigration
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
		$this->table('packages')
			->addColumn('package', 'string', ['limit' => 50, 'null' => false])
			->addColumn('display', 'string', ['limit' => 50, 'null' => false])
			->addColumn('repo', 'string', ['limit' => 50, 'null' => false])
			->addColumn('stable', 'boolean', ['default' => true])
			->addColumn('deprecated', 'boolean', ['default' => false])
			->create();
	}
}

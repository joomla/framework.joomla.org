<?php

use Phinx\Migration\AbstractMigration;

class CreateTestResultsTable extends AbstractMigration
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
		$table = $this->table('test_results');
		$table->addColumn('package_id', 'integer')
			->addColumn('tests', 'integer', ['null' => false])
			->addColumn('assertions', 'integer', ['null' => false])
			->addColumn('errors', 'integer', ['null' => false])
			->addColumn('failures', 'integer', ['null' => false])
			->addColumn('total_lines', 'integer', ['null' => false])
			->addColumn('lines_covered', 'integer', ['null' => false])
			->create();

		$table->addForeignKey('package_id', $this->getAdapter()->getAdapterTableName('packages'), ['id'])
			->update();
	}
}

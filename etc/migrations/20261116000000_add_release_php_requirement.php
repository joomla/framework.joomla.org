<?php

use Phinx\Migration\AbstractMigration;

class AddReleasePhpRequirement extends AbstractMigration
{
    /**
     * Store the PHP version constraint of each release, as required in the release's composer.json
     *
     * @return  void
     */
    public function change()
    {
        $this->table('releases')
            ->addColumn('php', 'string', ['limit' => 100, 'null' => true, 'default' => null])
            ->update();
    }
}

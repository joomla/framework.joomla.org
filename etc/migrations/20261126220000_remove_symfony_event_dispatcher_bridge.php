<?php

use Phinx\Db\Adapter\AdapterWrapper;
use Phinx\Db\Adapter\TablePrefixAdapter;
use Phinx\Migration\AbstractMigration;

class RemoveSymfonyEventDispatcherBridge extends AbstractMigration
{
    /**
     * Remove the Symfony Event Dispatcher Bridge package, which was never released and is archived
     *
     * The package was removed from packages.yml, but `package:sync` only adds and updates packages, so its record is deleted here.
     *
     * @return  void
     */
    public function up(): void
    {
        $packages = $this->getAdapter()->quoteTableName($this->getPrefixedTableName('packages'));
        $releases = $this->getAdapter()->quoteTableName($this->getPrefixedTableName('releases'));
        $package  = $this->fetchRow("SELECT id FROM $packages WHERE package = 'symfony-event-dispatcher-bridge'");

        if ($package === false) {
            return;
        }

        // Delete the releases first instead of relying on the foreign key to cascade
        $this->execute("DELETE FROM $releases WHERE package_id = ?", [(int) $package['id']]);
        $this->execute("DELETE FROM $packages WHERE id = ?", [(int) $package['id']]);
    }

    /**
     * The removed package can't be restored, it's no longer part of packages.yml
     *
     * @return  void
     */
    public function down(): void
    {
    }

    /**
     * Get the table name including the configured prefix, raw queries don't get the prefix added by Phinx
     *
     * @param   string  $tableName  The table name without prefix
     *
     * @return  string
     */
    private function getPrefixedTableName(string $tableName): string
    {
        $adapter = $this->getAdapter();

        while ($adapter instanceof AdapterWrapper) {
            if ($adapter instanceof TablePrefixAdapter) {
                return $adapter->getAdapterTableName($tableName);
            }

            $adapter = $adapter->getAdapter();
        }

        return $tableName;
    }
}

<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Command;

use Joomla\Console\AbstractCommand;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Command to clear the cache pool
 */
class ClearCacheCommand extends AbstractCommand
{
	/**
	 * Cache pool
	 *
	 * @var  CacheItemPoolInterface
	 */
	private $cache;

	/**
	 * Instantiate the command.
	 *
	 * @param   CacheItemPoolInterface  $cache  Cache pool.
	 */
	public function __construct(CacheItemPoolInterface $cache)
	{
		$this->cache = $cache;

		parent::__construct();
	}

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Clear Cache');

		$this->cache->clear();

		$symfonyStyle->success('Cache cleared.');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 */
	protected function initialise()
	{
		$this->setName('cache:clear');
		$this->setDescription('Clear the local cache pool');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command clears the application cache pool

<info>php %command.full_name% %command.name%</info>
EOF
		);
	}
}

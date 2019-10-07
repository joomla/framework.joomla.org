<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Cache\Adapter;

use DebugBar\DebugBar;
use Joomla\FrameworkWebsite\Cache\ResetInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\CallbackInterface;

/**
 * Debug cache adapter
 */
class DebugAdapter implements AdapterInterface, CacheInterface, PruneableInterface, ResettableInterface
{
	/**
	 * Application debug bar
	 *
	 * @var  DebugBar
	 */
	private $debugBar;

	/**
	 * Wrapped cache pool
	 *
	 * @var  AdapterInterface
	 */
	private $pool;

	/**
	 * Constructor.
	 *
	 * @param   DebugBar          $debugBar  Application debug bar.
	 * @param   AdapterInterface  $pool      Wrapped cache pool.
	 */
	public function __construct(DebugBar $debugBar, AdapterInterface $pool)
	{
		$this->debugBar = $debugBar;
		$this->pool     = $pool;
	}

	/**
	 * Fetches a value from the pool or computes it if not found.
	 *
	 * @param   string                      $key       The key of the item to retrieve from the cache
	 * @param   callable|CallbackInterface  $callback  Should return the computed value for the given key/item
	 * @param   float|null                  $beta      A float that, as it grows, controls the likeliness of triggering
	 *                                                 early expiration. 0 disables it, INF forces immediate expiration.
	 *                                                 The default (or providing null) is implementation dependent but should
	 *                                                 typically be 1.0, which should provide optimal stampede protection.
	 * @param   array                       $metadata  The metadata of the cached item
	 *
	 * @return  mixed  The value corresponding to the provided key
	 */
	public function get(string $key, callable $callback, float $beta = null, array &$metadata = null)
	{
		if (!$this->pool instanceof CacheInterface)
		{
			throw new \BadMethodCallException(
				sprintf('Cannot call "%s::get()": this class doesn\'t implement "%s".', \get_class($this->pool), CacheInterface::class)
			);
		}

		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.get ' . $key);

		try
		{
			return $this->pool->get($key, $callback, $beta, $metadata);
		}
		finally
		{
			$collector->stopMeasure('cache.get ' . $key);
		}
	}

	/**
	 * Returns a Cache Item representing the specified key.
	 *
	 * @param   string  $key  The key for which to return the corresponding Cache Item.
	 *
	 * @return  CacheItemInterface  The corresponding Cache Item.
	 */
	public function getItem($key)
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.getItem ' . $key);

		try
		{
			return $this->pool->getItem($key);
		}
		finally
		{
			$collector->stopMeasure('cache.getItem ' . $key);
		}
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param   string  $key  The key for which to check existence.
	 *
	 * @return  boolean
	 */
	public function hasItem($key)
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.hasItem ' . $key);

		try
		{
			return $this->pool->hasItem($key);
		}
		finally
		{
			$collector->stopMeasure('cache.hasItem ' . $key);
		}
	}

	/**
	 * Removes the item from the pool.
	 *
	 * @param   string  $key  The key to delete.
	 *
	 * @return  boolean
	 */
	public function deleteItem($key)
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.deleteItem ' . $key);

		try
		{
			return $this->pool->deleteItem($key);
		}
		finally
		{
			$collector->stopMeasure('cache.deleteItem ' . $key);
		}
	}

	/**
	 * Persists a cache item immediately.
	 *
	 * @param   CacheItemInterface  $item  The cache item to save.
	 *
	 * @return  boolean
	 */
	public function save(CacheItemInterface $item)
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.save ' . $item->getKey());

		try
		{
			return $this->pool->save($item);
		}
		finally
		{
			$collector->stopMeasure('cache.save ' . $item->getKey());
		}
	}

	/**
	 * Sets a cache item to be persisted later.
	 *
	 * @param   CacheItemInterface  $item  The cache item to save.
	 *
	 * @return  boolean
	 */
	public function saveDeferred(CacheItemInterface $item)
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.saveDeferred ' . $item->getKey());

		try
		{
			return $this->pool->saveDeferred($item);
		}
		finally
		{
			$collector->stopMeasure('cache.saveDeferred ' . $item->getKey());
		}
	}

	/**
	 * Returns a traversable set of cache items.
	 *
	 * @param   string[]  $keys  An indexed array of keys of items to retrieve.
	 *
	 * @return  array  A traversable collection of Cache Items keyed by the cache keys of each item.
	 *                 A Cache item will be returned for each key, even if that key is not found.
	 */
	public function getItems(array $keys = [])
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.getItems ' . implode(', ', $keys));

		try
		{
			return $this->pool->getItems($keys);
		}
		finally
		{
			$collector->stopMeasure('cache.getItems ' . implode(', ', $keys));
		}
	}

	/**
	 * Deletes all items in the pool.
	 *
	 * @return  boolean
	 */
	public function clear()
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.clear');

		try
		{
			return $this->pool->clear();
		}
		finally
		{
			$collector->stopMeasure('cache.clear');
		}
	}

	/**
	 * Removes multiple items from the pool.
	 *
	 * @param   array  $keys  An array of keys that should be removed from the pool.
	 *
	 * @return  boolean  True if the items were successfully removed. False if there was an error.
	 */
	public function deleteItems(array $keys)
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.deleteItems ' . implode(', ', $keys));

		try
		{
			return $this->pool->deleteItems($keys);
		}
		finally
		{
			$collector->stopMeasure('cache.deleteItems ' . implode(', ', $keys));
		}
	}

	/**
	 * Persists any deferred cache items.
	 *
	 * @return  boolean  True if all not-yet-saved items were successfully saved or there were none. False otherwise.
	 */
	public function commit()
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.commit');

		try
		{
			return $this->pool->commit();
		}
		finally
		{
			$collector->stopMeasure('cache.commit');
		}
	}

	/**
	 * Prune all expired cache items.
	 *
	 * @return  boolean
	 */
	public function prune()
	{
		if (!$this->pool instanceof PruneableInterface)
		{
			return false;
		}

		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.prune');

		try
		{
			return $this->pool->prune();
		}
		finally
		{
			$collector->stopMeasure('cache.prune');
		}
	}

	/**
	 * Reset the cache pool to its original state
	 *
	 * @return  void
	 */
	public function reset(): void
	{
		if (!$this->pool instanceof ResetInterface)
		{
			return;
		}

		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.reset');

		try
		{
			$this->pool->reset();
		}
		finally
		{
			$collector->stopMeasure('cache.reset');
		}
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param   string  $key  The unique cache key of the item to delete.
	 *
	 * @return  boolean
	 */
	public function delete(string $key): bool
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];

		$collector->startMeasure('cache.delete ' . $key);

		try
		{
			return $this->pool->deleteItem($key);
		}
		finally
		{
			$collector->stopMeasure('cache.delete ' . $key);
		}
	}
}

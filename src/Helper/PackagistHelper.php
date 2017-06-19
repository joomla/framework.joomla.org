<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Helper;

use Joomla\Cache\Item\Item;
use Joomla\FrameworkWebsite\PackageAware;
use Joomla\Http\Http;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Helper interacting with the Packagist API
 *
 * @since  1.0
 */
class PackagistHelper
{
	use PackageAware;

	/**
	 * The cache adapter
	 *
	 * @var    CacheItemPoolInterface
	 * @since  1.0
	 */
	private $cache;

	/**
	 * The HTTP driver
	 *
	 * @var    Http
	 * @since  1.0
	 */
	private $http;

	/**
	 * The cache key
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $cacheKey = 'downloads.count';

	/**
	 * Instantiate the helper.
	 *
	 * @param   Http                    $http   The HTTP driver.
	 * @param   CacheItemPoolInterface  $cache  The cache adapter.
	 *
	 * @since   1.0
	 */
	public function __construct(Http $http, CacheItemPoolInterface $cache)
	{
		$this->cache = $cache;
		$this->http  = $http;
	}

	/**
	 * Fetch the download counts for all packages.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function fetchDownloadCounts() : array
	{
		$counts = [];

		foreach (array_keys((array) $this->getPackages()->get('packages')) as $packageName)
		{
			$url = "https://packagist.org/packages/joomla/$packageName.json";

			try
			{
				$response = $this->http->get($url);
				$data     = json_decode($response->body);

				$counts[$packageName] = $data->package->downloads->total;
			}
			catch (\RuntimeException $exception)
			{
				$counts[$packageName] = false;
			}
		}

		return $counts;
	}

	/**
	 * Fetch the download counts data from the data store
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getDownloadCounts() : array
	{
		if ($this->cache->hasItem($this->cacheKey))
		{
			$cacheItem = $this->cache->getItem($this->cacheKey);

			if ($cacheItem->isHit())
			{
				$counts = $cacheItem->get();
			}
			else
			{
				$counts = $this->fetchDownloadCounts();
				$this->saveCountsToCache($counts);
			}
		}
		else
		{
			$counts = $this->fetchDownloadCounts();
			$this->saveCountsToCache($counts);
		}

		return $counts;
	}

	/**
	 * Save the download count data to the cache store
	 *
	 * @param   array  $counts  Array containing the count data
	 *
	 * @return  bool  Result of the cache save operation
	 *
	 * @since   1.0
	 */
	public function saveCountsToCache(array $counts) : bool
	{
		// Cache for 59 minutes, the API can't handle anything over one hour right now
		$cacheItem = (new Item($this->cacheKey, 3540))
			->set($counts);

		return $this->cache->save($cacheItem);
	}
}

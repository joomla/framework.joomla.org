<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use Joomla\Registry\Registry;

/**
 * Trait for objects which are package aware
 *
 * @since  1.0
 */
trait PackageAware
{
	/**
	 * Registry containing the package data
	 *
	 * @var    Registry
	 * @since  1.0
	 */
	protected $packages;

	/**
	 * Get the package registry.
	 *
	 * @return  Registry
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException May be thrown if the registry has not been set.
	 */
	public function getPackages() : Registry
	{
		if ($this->packages)
		{
			return $this->packages;
		}

		throw new \UnexpectedValueException('Package registry not set in ' . get_class($this));
	}

	/**
	 * Set the package registry.
	 *
	 * @param   Registry  $packages  The package registry
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setPackages(Registry $packages)
	{
		$this->packages = $packages;

		return $this;
	}
}

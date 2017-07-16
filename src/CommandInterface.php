<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite;

use Joomla\Controller\ControllerInterface;

/**
 * Console Command Interface
 */
interface CommandInterface extends ControllerInterface
{
	/**
	 * Get the command's description
	 *
	 * @return  string
	 */
	public function getDescription() : string;

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle() : string;
}

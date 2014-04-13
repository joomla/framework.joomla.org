<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Renderer;

/**
 * Extended filesystem loader for Twig
 *
 * @since  1.0
 */
class TwigLoader extends \Twig_Loader_Filesystem
{
	/**
	 * Optional file extension
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param   string|array  $paths      A path or an array of paths where to look for templates
	 * @param   string        $extension  An optional file extension
	 *
	 * @since   1.0
	 */
	public function __construct($paths = array(), $extension = '')
	{
		parent::__construct($paths);

		$this->extension = $extension;
	}

	/**
	 * Normalize the template name
	 *
	 * @param   string  $name  The template name to normalize
	 *
	 * @return  string  The normalized name
	 *
	 * @since   1.0
	 */
	protected function normalizeName($name)
	{
		$name = parent::normalizeName($name);

		if (!strpos($name, $this->extension))
		{
			$name .= $this->extension;
		}

		return $name;
	}
}

<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2026 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Tests\Stub;

use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Web\WebClient;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Minimal web application for tests, it only holds the input, configuration and response
 */
final class TestWebApplication extends AbstractWebApplication
{
    /**
     * Constructor.
     *
     * @param   array  $request  The request data
     * @param   array  $config   The application configuration
     */
    public function __construct(array $request = [], array $config = [])
    {
        parent::__construct(new Input($request), new Registry($config), new WebClient('PHPUnit'));

        $this->set('uri.base.path', '/');
        $this->set('uri.base.host', 'https://framework.example');
    }

    /**
     * Method to run the application routines.
     *
     * @return  void
     */
    protected function doExecute(): void
    {
    }
}

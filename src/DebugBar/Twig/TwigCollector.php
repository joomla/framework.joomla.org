<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\DebugBar\Twig;

use DebugBar\DataCollector\{
	AssetProvider, DataCollector, Renderable
};

/**
 * Collects data about rendered templates
 *
 * http://twig.sensiolabs.org/
 *
 * Your Twig_Environment object needs to be wrapped in a
 * TraceableTwigEnvironment object
 *
 * <code>
 * $env = new TraceableTwigEnvironment(new Twig_Environment($loader));
 * $debugbar->addCollector(new TwigCollector($env));
 * </code>
 */
class TwigCollector extends DataCollector implements Renderable, AssetProvider
{
	public function __construct(TraceableTwigEnvironment $twig)
	{
		$this->twig = $twig;
	}

	public function collect()
	{
		$templates      = [];
		$accuRenderTime = 0;

		foreach ($this->twig->getRenderedTemplates() as $tpl)
		{
			$accuRenderTime += $tpl['render_time'];
			$templates[]    = [
				'name'            => $tpl['name'],
				'render_time'     => $tpl['render_time'],
				'render_time_str' => $this->formatDuration($tpl['render_time']),
			];
		}

		return [
			'nb_templates'                => count($templates),
			'templates'                   => $templates,
			'accumulated_render_time'     => $accuRenderTime,
			'accumulated_render_time_str' => $this->formatDuration($accuRenderTime),
		];
	}

	public function getName()
	{
		return 'twig';
	}

	public function getWidgets()
	{
		return [
			'twig'       => [
				'icon'    => 'leaf',
				'widget'  => 'PhpDebugBar.Widgets.TemplatesWidget',
				'map'     => 'twig',
				'default' => json_encode(['templates' => []]),
			],
			'twig:badge' => [
				'map'     => 'twig.nb_templates',
				'default' => 0,
			],
		];
	}

	public function getAssets()
	{
		return [
			'css' => 'widgets/templates/widget.css',
			'js'  => 'widgets/templates/widget.js',
		];
	}
}

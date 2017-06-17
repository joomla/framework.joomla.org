<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\DebugBar\Twig;

use DebugBar\DataCollector\TimeDataCollector;

/**
 * Wrapped a Twig Environment to provide profiling features
 */
class TraceableTwigEnvironment extends \Twig_Environment
{
	protected $twig;

	protected $renderedTemplates = [];

	protected $timeDataCollector;

	public function __construct(\Twig_Environment $twig, TimeDataCollector $timeDataCollector = null)
	{
		$this->twig              = $twig;
		$this->timeDataCollector = $timeDataCollector;
	}

	public function __call($name, $arguments)
	{
		return call_user_func_array([$this->twig, $name], $arguments);
	}

	public function getRenderedTemplates()
	{
		return $this->renderedTemplates;
	}

	public function addRenderedTemplate(array $info)
	{
		$this->renderedTemplates[] = $info;
	}

	public function getTimeDataCollector()
	{
		return $this->timeDataCollector;
	}

	public function getBaseTemplateClass()
	{
		return $this->twig->getBaseTemplateClass();
	}

	public function setBaseTemplateClass($class)
	{
		$this->twig->setBaseTemplateClass($class);
	}

	public function enableDebug()
	{
		$this->twig->enableDebug();
	}

	public function disableDebug()
	{
		$this->twig->disableDebug();
	}

	public function isDebug()
	{
		return $this->twig->isDebug();
	}

	public function enableAutoReload()
	{
		$this->twig->enableAutoReload();
	}

	public function disableAutoReload()
	{
		$this->twig->disableAutoReload();
	}

	public function isAutoReload()
	{
		return $this->twig->isAutoReload();
	}

	public function enableStrictVariables()
	{
		$this->twig->enableStrictVariables();
	}

	public function disableStrictVariables()
	{
		$this->twig->disableStrictVariables();
	}

	public function isStrictVariables()
	{
		return $this->twig->isStrictVariables();
	}

	public function getCache($original = true)
	{
		return $this->twig->getCache($original);
	}

	public function setCache($cache)
	{
		$this->twig->setCache($cache);
	}

	public function getTemplateClass($name, $index = null)
	{
		return $this->twig->getTemplateClass($name, $index);
	}

	public function render($name, array $context = [])
	{
		return $this->loadTemplate($name)->render($context);
	}

	public function display($name, array $context = [])
	{
		$this->loadTemplate($name)->display($context);
	}

	public function load($name)
	{
		return $this->twig->load($name);
	}

	public function loadTemplate($name, $index = null)
	{
		$cls = $mainCls = $this->getTemplateClass($name);

		if (null !== $index)
		{
			$cls .= '_' . $index;
		}

		$refl = new \ReflectionClass($this->twig);

		$loadedTemplatesProperty = $refl->getProperty('loadedTemplates');
		$loadedTemplatesProperty->setAccessible(true);

		/** @var array $loadedTemplates */
		$loadedTemplates = $loadedTemplatesProperty->getValue($this->twig);

		if (isset($loadedTemplates[$cls]))
		{
			return $loadedTemplates[$cls];
		}

		if (!class_exists($cls, false))
		{
			$cache = $this->getCache(false);

			$key = $cache->generateKey($name, $mainCls);

			if (!$this->isAutoReload() || $this->isTemplateFresh($name, $cache->getTimestamp($key)))
			{
				$cache->load($key);
			}

			if (!class_exists($cls, false))
			{
				$source  = $this->getLoader()->getSourceContext($name);
				$content = $this->compileSource($source);
				$cache->write($key, $content);
				$cache->load($key);

				if (!class_exists($mainCls, false))
				{
					/* Last line of defense if either $this->bcWriteCacheFile was used,
					 * $cache is implemented as a no-op or we have a race condition
					 * where the cache was cleared between the above calls to write to and load from
					 * the cache.
					 */
					eval('?>' . $content);
				}

				if (!class_exists($cls, false))
				{
					throw new \Twig_Error_Runtime(sprintf('Failed to load Twig template "%s", index "%s": cache is corrupted.', $name, $index), -1, $source);
				}
			}
		}

		$extensionSetProperty = $refl->getProperty('extensionSet');
		$extensionSetProperty->setAccessible(true);

		/** @var \Twig_ExtensionSet $extensionSet */
		$extensionSet = $extensionSetProperty->getValue($this->twig);
		$extensionSet->initRuntime($this);

		return $loadedTemplates[$cls] = new TraceableTwigTemplate($this, new $cls($this));
	}

	public function createTemplate($template)
	{
		return $this->twig->createTemplate($template);
	}

	public function isTemplateFresh($name, $time)
	{
		return $this->twig->isTemplateFresh($name, $time);
	}

	public function resolveTemplate($names)
	{
		return $this->twig->resolveTemplate($names);
	}

	public function setLexer(\Twig_Lexer $lexer)
	{
		$this->twig->setLexer($lexer);
	}

	public function tokenize(\Twig_Source $source)
	{
		return $this->twig->tokenize($source);
	}

	public function setParser(\Twig_Parser $parser)
	{
		$this->twig->setParser($parser);
	}

	public function parse(\Twig_TokenStream $tokens)
	{
		return $this->twig->parse($tokens);
	}

	public function setCompiler(\Twig_Compiler $compiler)
	{
		$this->twig->setCompiler($compiler);
	}

	public function compile(\Twig_Node $node)
	{
		return $this->twig->compile($node);
	}

	public function compileSource(\Twig_Source $source)
	{
		return $this->twig->compileSource($source);
	}

	public function setLoader(\Twig_LoaderInterface $loader)
	{
		$this->twig->setLoader($loader);
	}

	public function getLoader()
	{
		return $this->twig->getLoader();
	}

	public function setCharset($charset)
	{
		$this->twig->setCharset($charset);
	}

	public function getCharset()
	{
		return $this->twig->getCharset();
	}

	public function hasExtension($name)
	{
		return $this->twig->hasExtension($name);
	}

	public function addRuntimeLoader(\Twig_RuntimeLoaderInterface $loader)
	{
		$this->twig->addRuntimeLoader($loader);
	}

	public function getExtension($name)
	{
		return $this->twig->getExtension($name);
	}

	public function getRuntime($class)
	{
		return $this->twig->getRuntime($class);
	}

	public function addExtension(\Twig_ExtensionInterface $extension)
	{
		$this->twig->addExtension($extension);
	}

	public function setExtensions(array $extensions)
	{
		$this->twig->setExtensions($extensions);
	}

	public function getExtensions()
	{
		return $this->twig->getExtensions();
	}

	public function addTokenParser(\Twig_TokenParserInterface $parser)
	{
		$this->twig->addTokenParser($parser);
	}

	public function getTokenParsers()
	{
		return $this->twig->getTokenParsers();
	}

	public function getTags()
	{
		return $this->twig->getTags();
	}

	public function addNodeVisitor(\Twig_NodeVisitorInterface $visitor)
	{
		$this->twig->addNodeVisitor($visitor);
	}

	public function getNodeVisitors()
	{
		return $this->twig->getNodeVisitors();
	}

	public function addFilter(\Twig_Filter $filter)
	{
		$this->twig->addFilter($filter);
	}

	public function getFilter($name)
	{
		return $this->twig->getFilter($name);
	}

	public function registerUndefinedFilterCallback(callable $callable)
	{
		$this->twig->registerUndefinedFilterCallback($callable);
	}

	public function getFilters()
	{
		return $this->twig->getFilters();
	}

	public function addTest(\Twig_Test $test)
	{
		$this->twig->addTest($test);
	}

	public function getTests()
	{
		return $this->twig->getTests();
	}

	public function getTest($name)
	{
		return $this->twig->getTest($name);
	}

	public function addFunction(\Twig_Function $function)
	{
		$this->twig->addFunction($function);
	}

	public function getFunction($name)
	{
		return $this->twig->getFunction($name);
	}

	public function registerUndefinedFunctionCallback(callable $callable)
	{
		$this->twig->registerUndefinedFunctionCallback($callable);
	}

	public function getFunctions()
	{
		return $this->twig->getFunctions();
	}

	public function addGlobal($name, $value)
	{
		$this->twig->addGlobal($name, $value);
	}

	public function getGlobals()
	{
		return $this->twig->getGlobals();
	}

	public function mergeGlobals(array $context)
	{
		return $this->twig->mergeGlobals($context);
	}

	public function getUnaryOperators()
	{
		return $this->twig->getUnaryOperators();
	}

	public function getBinaryOperators()
	{
		return $this->twig->getBinaryOperators();
	}
}

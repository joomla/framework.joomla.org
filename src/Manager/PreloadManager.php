<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Manager;

use Fig\Link\{
	GenericLinkProvider,
	Link
};
use Psr\Link\EvolvableLinkProviderInterface;

/**
 * Manager for HTTP/2 asset preloading
 */
class PreloadManager
{
	/**
	 * The link provider
	 *
	 * @var  EvolvableLinkProviderInterface
	 */
	protected $linkProvider;

	/**
	 * PreloadManager constructor
	 *
	 * @param   EvolvableLinkProviderInterface  $linkProvider  The link provider
	 */
	public function __construct(EvolvableLinkProviderInterface $linkProvider = null)
	{
		$this->linkProvider = $linkProvider ?: new GenericLinkProvider;
	}

	/**
	 * Get the link provider
	 *
	 * @return  EvolvableLinkProviderInterface
	 */
	public function getLinkProvider(): EvolvableLinkProviderInterface
	{
		return $this->linkProvider;
	}

	/**
	 * Set the link provider
	 *
	 * @param   EvolvableLinkProviderInterface  $linkProvider  The link provider
	 *
	 * @return  void
	 */
	public function setLinkProvider(EvolvableLinkProviderInterface $linkProvider)
	{
		$this->linkProvider = $linkProvider;
	}

	/**
	 * Preloads a resource.
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('crossorigin' => 'use-credentials')")
	 *
	 * @return  void
	 */
	public function preload(string $uri, array $attributes = [])
	{
		$this->link($uri, 'preload', $attributes);
	}

	/**
	 * Resolves a resource origin as early as possible.
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 */
	public function dnsPrefetch(string $uri, array $attributes = [])
	{
		$this->link($uri, 'dns-prefetch', $attributes);
	}

	/**
	 * Initiates a early connection to a resource (DNS resolution, TCP handshake, TLS negotiation).
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 */
	public function preconnect(string $uri, array $attributes = [])
	{
		$this->link($uri, 'preconnect', $attributes);
	}

	/**
	 * Indicates to the client that it should prefetch this resource.
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 */
	public function prefetch(string $uri, array $attributes = [])
	{
		$this->link($uri, 'prefetch', $attributes);
	}

	/**
	 * Indicates to the client that it should prerender this resource.
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 */
	public function prerender(string $uri, array $attributes = [])
	{
		$this->link($uri, 'prerender', $attributes);
	}

	/**
	 * Adds a "Link" HTTP header.
	 *
	 * @param   string  $uri         The relation URI
	 * @param   string  $rel         The relation type (e.g. "preload", "prefetch", "prerender" or "dns-prefetch")
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 */
	private function link(string $uri, string $rel, array $attributes = [])
	{
		$link = new Link($rel, $uri);

		foreach ($attributes as $key => $value)
		{
			$link = $link->withAttribute($key, $value);
		}

		$this->setLinkProvider($this->getLinkProvider()->withLink($link));
	}
}

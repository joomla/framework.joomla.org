<?php

/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Helper;

/**
 * Helper reading information from the fetched package documentation
 */
class DocumentationHelper
{
    /**
     * The documentation version the `latest` documentation routes point to
     *
     * @var  string
     */
    public const LATEST_VERSION = '4.x';

    /**
     * The maximum length of a package summary
     *
     * @var  integer
     */
    private const SUMMARY_LENGTH = 300;

    /**
     * The path to the root of the documentation files
     *
     * @var  string
     */
    private $docsPath;

    /**
     * Constructor.
     *
     * @param   string  $docsPath  The path to the root of the documentation files.
     */
    public function __construct(string $docsPath)
    {
        $this->docsPath = rtrim($docsPath, '/\\');
    }

    /**
     * Check if a package has documentation for the given version
     *
     * @param   \stdClass  $package  The package record.
     * @param   string     $version  The documentation version.
     *
     * @return  boolean
     */
    public function hasDocumentation(\stdClass $package, string $version = self::LATEST_VERSION): bool
    {
        return is_file($this->getOverviewPath($package, $version));
    }

    /**
     * Get a short summary of a package from the first paragraph of its overview page
     *
     * @param   \stdClass  $package  The package record.
     * @param   string     $version  The documentation version.
     *
     * @return  string  The summary as plain text, an empty string if the package has no overview page
     */
    public function getPackageSummary(\stdClass $package, string $version = self::LATEST_VERSION): string
    {
        $path = $this->getOverviewPath($package, $version);

        if (!is_file($path)) {
            return '';
        }

        $markdown = (string) file_get_contents($path);

        return $this->extractSummary($markdown);
    }

    /**
     * Extract the first paragraph of a Markdown document as plain text
     *
     * @param   string  $markdown  The Markdown document.
     *
     * @return  string
     */
    public function extractSummary(string $markdown): string
    {
        $markdown = str_replace(["\r\n", "\r"], "\n", $markdown);

        // Comments may span several paragraphs
        $markdown = preg_replace('/<!--.*?-->/s', '', $markdown);

        // Fenced code blocks may contain blank lines, so remove them before splitting the paragraphs
        $markdown = preg_replace('/^(```|~~~).*?^\1[^\n]*$/ms', '', $markdown);

        foreach (preg_split('/\n\s*\n/', $markdown) as $block) {
            $block = trim($block);

            // Skip everything which is not a plain paragraph: headings, HTML, tables, quotes, lists and indented code
            if ($block === '' || preg_match('/^(?:#|<|\||>|[-*+]\s|\d+[.)]\s|\s{4})/', $block)) {
                continue;
            }

            $text = $this->toPlainText($block);

            // Some overview pages are only placeholders
            if (preg_match('/^todo\b/i', $text)) {
                return '';
            }

            // A paragraph introducing a list ends with a colon; without the list, only the complete sentences make sense
            if (substr($text, -1) === ':' && ($end = strrpos($text, '. ')) !== false) {
                $text = substr($text, 0, $end + 1);
            }

            return $this->shorten($text);
        }

        return '';
    }

    /**
     * Get the path to the overview page of a package
     *
     * @param   \stdClass  $package  The package record.
     * @param   string     $version  The documentation version.
     *
     * @return  string
     */
    private function getOverviewPath(\stdClass $package, string $version): string
    {
        return $this->docsPath . '/' . $version . '/' . $package->package . '/overview.md';
    }

    /**
     * Shorten a text to the maximum summary length, at a word boundary
     *
     * @param   string  $text  The text.
     *
     * @return  string
     */
    private function shorten(string $text): string
    {
        if (mb_strlen($text) <= self::SUMMARY_LENGTH) {
            return $text;
        }

        $text = mb_substr($text, 0, self::SUMMARY_LENGTH);
        $text = preg_replace('/\s+\S*$/u', '', $text);

        return rtrim($text, " ,;:.") . '…';
    }

    /**
     * Convert inline Markdown to plain text
     *
     * @param   string  $markdown  The Markdown text.
     *
     * @return  string
     */
    private function toPlainText(string $markdown): string
    {
        $text = preg_replace(
            [
                '/!\[([^\]]*)\]\([^)]*\)/',  // Images
                '/\[([^\]]*)\]\([^)]*\)/',   // Links
                '/\[([^\]]*)\]\[[^\]]*\]/',  // Reference links
                '/`([^`]*)`/',               // Inline code
                '/(\*\*|__)(.+?)\1/',        // Bold
                '/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/', // Italic
                '/<[^>]+>/',                 // Inline HTML
            ],
            ['$1', '$1', '$1', '$1', '$2', '$1', ''],
            $markdown
        );

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}

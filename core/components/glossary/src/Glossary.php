<?php
/**
 * Glossary
 *
 * Copyright 2012-2016 by Alan Pich <alan.pich@gmail.com>
 * Copyright 2016-2022 by Thomas Jakobi <office@treehillstudio.com>
 *
 * @package glossary
 * @subpackage classfile
 */

namespace TreehillStudio\Glossary;

use modResource;
use modX;
use Term;

/**
 * Class Glossary
 */
class Glossary
{
    /**
     * A reference to the modX instance
     * @var modX $modx
     */
    public $modx;

    /**
     * The namespace
     * @var string $namespace
     */
    public $namespace = 'glossary';

    /**
     * The package name
     * @var string $packageName
     */
    public $packageName = 'Glossary';

    /**
     * The version
     * @var string $version
     */
    public $version = '2.5.1';

    /**
     * The class options
     * @var array $options
     */
    public $options = [];

    /**
     * Glossary constructor
     *
     * @param modX $modx A reference to the modX instance.
     * @param array $options An array of options. Optional.
     */
    public function __construct(modX &$modx, $options = [])
    {
        $this->modx =& $modx;

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/' . $this->namespace . '/');
        $modxversion = $this->modx->getVersionData();

        // Load some default paths for easier management
        $this->options = array_merge([

            'namespace' => $this->namespace,
            'version' => $this->version,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'vendorPath' => $corePath . 'vendor/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'pagesPath' => $corePath . 'elements/pages/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'pluginsPath' => $corePath . 'elements/plugins/',
            'controllersPath' => $corePath . 'controllers/',
            'processorsPath' => $corePath . 'processors/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'imagesUrl' => $assetsUrl . 'images/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ], $options);

        $lexicon = $this->modx->getService('lexicon', 'modLexicon');
        $lexicon->load($this->namespace . ':default');

        $this->packageName = $this->modx->lexicon('glossary');

        $this->modx->addPackage($this->namespace, $this->getOption('modelPath'));

        // Add default options
        $this->options = array_merge($this->options, [
            'debug' => (bool)$this->getOption('debug', $options, false),
            'modxversion' => $modxversion['version'],
            'disabledTags' => $this->getOption('disabledTags', $options, 'a,form,select'),
            'fullwords' => (bool)$this->getOption('fullwords', $options, true),
            'html' => (bool)$this->getOption('html', $options, true),
            'is_admin' => $this->modx->user && $this->modx->context && ($modx->hasPermission('settings') || $modx->hasPermission($this->namespace . '_settings')),
            'sections' => (bool)$this->getOption('sections', $options, false),
            'sectionsEnd' => $this->getOption('sectionsEnd', $options, '<!-- GlossaryEnd -->'),
            'sectionsStart' => $this->getOption('sectionsStart', $options, '<!-- GlossaryStart -->'),
            'tpl' => $this->getOption('tpl', $options, 'Glossary.highlighterTpl'),
        ]);
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = [], $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("$this->namespace.$key", $this->modx->config)) {
                $option = $this->modx->getOption("$this->namespace.$key");
            }
        }
        return $option;
    }

    /**
     * Get the Glossary terms grouped by the transliterated first letter
     *
     * @return array
     */
    public function getGroupedTerms($showEmptySections)
    {
        /** @var Term[] $terms */
        $terms = $this->modx->getCollection('Term');
        $letters = [];
        if ($showEmptySections) {
            foreach (range('A', 'Z') as $value) {
                $letters[$value] = [];
            }
        }
        foreach ($terms as $termObj) {
            $term = $termObj->toArray();
            $cleanTerm = $this->cleanAlias($term['term']);
            $firstLetter = strtoupper(substr($cleanTerm, 0, 1));
            if (!isset($letters[$firstLetter])) {
                $letters[$firstLetter] = [];
            }
            $letters[$firstLetter][] = $term;
        }
        foreach ($letters as &$letter) {
            usort($letter, [$this, 'sortTerms']);
        }
        ksort($letters, SORT_NATURAL);
        return $letters;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    private function sortTerms($a, $b) {
        return strcoll($a['term'], $b['term']);
    }

    /**
     * Get the Glossary terms
     *
     * @return array
     */
    public function getTerms($chunkName)
    {
        $html = $this->getOption('html');
        // Generate URL to target page
        $target = $this->modx->makeUrl($this->getOption('resid'));

        /** @var Term[] $terms */
        $terms = $this->modx->getCollection('Term');
        $result = [];
        foreach ($terms as $term) {
            $result[$term->get('term')] = $this->modx->getChunk($chunkName, [
                'term' => $term->get('term'),
                'link' => $target . '#' . $this->cleanAlias($term->get('term')),
                'explanation' => htmlspecialchars($term->get('explanation'), ENT_QUOTES, $this->modx->getOption('modx_charset')),
                'html' => ($html) ? '1' : ''
            ]);
        }
        return $result;
    }

    /**
     * Highlight terms in the text
     *
     * @param string $text
     * @param array $terms
     * @return string
     */
    public function highlightTerms($text, $terms)
    {
        // Enable section markers
        $enableSections = $this->getOption('sections', null, false);
        if ($enableSections) {
            $splitEx = '~((?:' . $this->getOption('sectionsStart') . ').*?(?:' . $this->getOption('sectionsEnd') . '))~isu';
            $sections = preg_split($splitEx, $text, null, PREG_SPLIT_DELIM_CAPTURE);
        } else {
            $sections = [$text];
        }

        // Mask all terms first
        $maskStart = '<_^_>';
        $maskEnd = '<_$_>';
        $fullwords = $this->getOption('fullwords', null, true);
        $disabledTags = array_map('trim', explode(',', $this->getOption('disabledTags')));
        $splitExTags = [];
        foreach ($disabledTags as $disabledTag) {
            $splitExTags[] = '<' . $disabledTag . '.*?</' . $disabledTag . '>';
        }
        // No replacements in html tag attributes and disabled tags
        $splitExDisabled = '~([a-z0-9-]+\s*=\s*".*?"|' . implode('|', $splitExTags) . ')~isu';
        foreach ($terms as $termText => $termValue) {
            if ($fullwords) {
                foreach ($sections as &$section) {
                    if (($enableSections && strpos($section, $this->getOption('sectionsStart')) === 0 && preg_match('/\b' . preg_quote($termText, '/') . '\b/u', $section)) ||
                        (!$enableSections && preg_match('/\b' . preg_quote($termText, '/') . '\b/u', $section))
                    ) {
                        $subSections = preg_split($splitExDisabled, $section, null, PREG_SPLIT_DELIM_CAPTURE);
                        foreach ($subSections as &$subSection) {
                            if (!preg_match($splitExDisabled, $subSection)) {
                                $subSection = preg_replace('/\b' . preg_quote($termText, '/') . '\b/u', $maskStart . $termText . $maskEnd, $subSection);
                            }
                        }
                        $section = implode('', $subSections);
                    }
                }
            } else {
                foreach ($sections as &$section) {
                    if (($enableSections && strpos($section, $this->getOption('sectionsStart')) === 0 && strpos($section, $termText) !== false) ||
                        (!$enableSections && strpos($section, $termText) !== false)
                    ) {
                        $subSections = preg_split($splitExDisabled, $section, null, PREG_SPLIT_DELIM_CAPTURE);
                        foreach ($subSections as &$subSection) {
                            if (!preg_match($splitExDisabled, $subSection)) {
                                $subSection = str_replace($termText, $maskStart . $termText . $maskEnd, $subSection);
                            }
                        }
                        $section = implode('', $subSections);
                    }
                }
            }
        }
        $text = implode('', $sections);

        // Replace the terms after to avoid nested replacement
        foreach ($terms as $termText => $termValue) {
            $text = str_replace($maskStart . $termText . $maskEnd, $termValue, $text);
        }

        // Remove remaining section markers
        $text = str_replace([
            $this->getOption('sectionsStart'), $this->getOption('sectionsEnd')
        ], '', $text);
        return $text;
    }

    /**
     * Translit a string with iconv or with a core MODX method
     *
     * @param $string
     * @return string
     */
    private function cleanAlias($string)
    {
        if (function_exists('iconv')) {
            return preg_replace('[^\W]', '', str_replace(' ', '-', iconv($this->modx->getOption('modx_charset', null, 'UTF-8'), 'ASCII//TRANSLIT//IGNORE', $string)));
        } else {
            return modResource::filterPathSegment($this->modx, $string);
        }
    }
}

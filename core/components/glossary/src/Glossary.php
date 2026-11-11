<?php
/**
 * Glossary
 *
 * Copyright 2012-2016 by Alan Pich <alan.pich@gmail.com>
 * Copyright 2016-2026 by Thomas Jakobi <office@treehillstudio.com>
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
    public $version = '2.6.0';

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
     * Get unique anchors for all Glossary terms
     *
     * Terms with the same transliterated anchor (i.e. 'Foo Bar' and 'Foo-Bar')
     * get a numeric suffix in the order of their creation.
     *
     * @return array The anchors keyed by the term
     */
    public function getTermAnchors()
    {
        $c = $this->modx->newQuery('Term');
        $c->select($this->modx->getSelectColumns('Term', 'Term', '', ['id', 'term']));
        $c->sortby('id', 'ASC');
        $anchors = [];
        $used = [];
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(\PDO::FETCH_ASSOC)) {
                $anchor = $this->getAnchor($row['term']);
                $unique = $anchor;
                for ($i = 2; isset($used[$unique]); $i++) {
                    $unique = $anchor . '-' . $i;
                }
                $used[$unique] = true;
                $anchors[$row['term']] = $unique;
            }
        }
        return $anchors;
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
        $anchors = $this->getTermAnchors();
        $letters = [];
        if ($showEmptySections) {
            foreach (range('A', 'Z') as $value) {
                $letters[$value] = [];
            }
        }
        foreach ($terms as $termObj) {
            $term = $termObj->toArray();
            $term['anchor'] = $anchors[$term['term']] ?? $this->getAnchor($term['term']);
            $firstLetter = mb_strtoupper(mb_substr($this->getAnchor($term['term']), 0, 1));
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
        $anchors = $this->getTermAnchors();
        $result = [];
        foreach ($terms as $term) {
            $result[$term->get('term')] = $this->modx->getChunk($chunkName, [
                'term' => $term->get('term'),
                'link' => $target . '#' . ($anchors[$term->get('term')] ?? $this->getAnchor($term->get('term'))),
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
        $sectionsStart = (string)$this->getOption('sectionsStart');
        $sectionsEnd = (string)$this->getOption('sectionsEnd');

        // Enable section markers
        $enableSections = $this->getOption('sections', null, false) && $sectionsStart !== '' && $sectionsEnd !== '';
        $sections = false;
        if ($enableSections) {
            $splitEx = '~(' . preg_quote($sectionsStart, '~') . '.*?' . preg_quote($sectionsEnd, '~') . ')~isu';
            $sections = preg_split($splitEx, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        }
        if ($sections === false) {
            $sections = [$text];
        }

        // Mask longer terms first, so that a term contained in another term does not break the longer one
        uksort($terms, function ($a, $b) {
            return mb_strlen($b) - mb_strlen($a);
        });

        // No replacements in already masked terms, html comments, html entities, script, style and textarea contents,
        // disabled tags, html tags (including their attributes) and MODX tags (including nested tags)
        $protected = [
            '\x1A\d+\x1A',
            '<!--.*?-->',
            '&(?:[a-z][a-z0-9]*|#[0-9]+|#x[0-9a-f]+);',
            '(?<modx>\[\[(?:[^\[\]]++|\[(?!\[)|\](?!\])|(?&modx))*+\]\])',
        ];
        $disabledTags = array_merge(['script', 'style', 'textarea'], array_map('trim', explode(',', (string)$this->getOption('disabledTags'))));
        foreach (array_unique(array_filter($disabledTags)) as $disabledTag) {
            $disabledTag = preg_quote($disabledTag, '~');
            $protected[] = '<' . $disabledTag . '(?=[\s>/]).*?</' . $disabledTag . '\s*>';
        }
        $protected[] = '<[a-z/!](?:[^>"\']|"[^"]*"|\'[^\']*\')*>';
        // Only the protected html is matched case insensitive, the terms are matched case sensitive
        $protectedEx = '(?i:' . implode('|', $protected) . ')';

        // Mask all terms first
        $fullwords = $this->getOption('fullwords', null, true);
        $masks = [];
        foreach ($terms as $termText => $termValue) {
            $termText = (string)$termText;
            if ($termText === '') {
                continue;
            }
            $mask = "\x1A" . count($masks) . "\x1A";
            $masks[$mask] = $termValue;
            $termEx = ($fullwords) ?
                '(?<![\p{L}\p{M}\p{N}_])' . preg_quote($termText, '~') . '(?![\p{L}\p{M}\p{N}_])' :
                preg_quote($termText, '~');
            // A protected part is matched before a term starting at the same position and is kept unchanged
            $replaceEx = '~' . $protectedEx . '|(?<term>' . $termEx . ')~su';
            foreach ($sections as $i => $section) {
                if ($enableSections && strpos($section, $sectionsStart) !== 0) {
                    continue;
                }
                if (!preg_match('~' . $termEx . '~u', $section)) {
                    continue;
                }
                $replaced = preg_replace_callback($replaceEx, function ($matches) use ($mask) {
                    return (isset($matches['term']) && $matches['term'] !== '') ? $mask : $matches[0];
                }, $section);
                if ($replaced !== null) {
                    $sections[$i] = $replaced;
                }
            }
        }
        $text = implode('', $sections);

        // Replace the masks after to avoid nested replacement
        $text = strtr($text, $masks);

        // Remove remaining section markers
        if ($sectionsStart !== '' && $sectionsEnd !== '') {
            $text = str_replace([$sectionsStart, $sectionsEnd], '', $text);
        }
        return $text;
    }

    /**
     * Clear the resource cache of all contexts, so that changed terms are used in cached resources
     */
    public function clearResourceCache()
    {
        $contexts = [];
        $c = $this->modx->newQuery('modContext');
        $c->select($this->modx->escape('key'));
        if ($c->prepare() && $c->stmt->execute()) {
            $contexts = $c->stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
        $this->modx->getCacheManager()->refresh([
            'resource' => ['contexts' => array_values(array_diff($contexts, ['mgr']))],
        ]);
    }

    /**
     * Get the anchor of a term, transliterated with iconv or with a core MODX method
     *
     * @param string $term
     * @return string
     */
    public function getAnchor($term)
    {
        $anchor = '';
        if (function_exists('iconv')) {
            $anchor = @iconv($this->modx->getOption('modx_charset', null, 'UTF-8'), 'ASCII//TRANSLIT//IGNORE', $term);
            // Characters that can't be transliterated (i.e. cyrillic with some iconv implementations) are removed
            $anchor = ($anchor !== false) ? trim(strtolower(preg_replace('/[^a-z0-9_-]+/i', '', str_replace(' ', '-', $anchor))), '-') : '';
        }
        if ($anchor === '') {
            $anchor = modResource::filterPathSegment($this->modx, $term);
        }
        return $anchor;
    }
}

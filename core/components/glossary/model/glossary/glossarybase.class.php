<?php
/**
 * Glossary Base Classfile
 *
 * Copyright 2012-2016 by Alan Pich <alan.pich@gmail.com>
 * Copyright 2016-2018 by Thomas Jakobi <thomas.jakobi@partout.info>
 *
 * @package glossary
 * @subpackage classfile
 */

/**
 * class GlossaryBase
 */
class GlossaryBase
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
     * The version
     * @var string $version
     */
    public $version = '2.3.0';

    /**
     * The class options
     * @var array $options
     */
    public $options = array();

    /**
     * GlossaryBase constructor
     *
     * @param modX $modx A reference to the modX instance.
     * @param array $options An array of options. Optional.
     */
    function __construct(modX &$modx, $options = array())
    {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, $this->namespace);

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path') . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path') . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url') . 'components/' . $this->namespace . '/');

        // Load some default paths for easier management
        $this->options = array_merge(array(
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
        ), $options);

        // Add default options
        $this->options = array_merge($this->options, array(
            'is_admin' => ($this->modx->user) ? $this->modx->user->isMember('Administrator') || $this->modx->user->isMember('Agenda Administrator') : false,
            'debug' => (bool)$this->getOption('debug', $options, false),
            'html' => (bool)$this->getOption('html', $options, true)
        ));

        $this->modx->addPackage($this->namespace, $this->getOption('modelPath'));
        $lexicon = $this->modx->getService('lexicon', 'modLexicon');
        $lexicon->load($this->namespace . ':default');
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
    public function getOption($key, $options = array(), $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    /**
     * Get the Glossary terms grouped by the transliterated first letter
     *
     * @return array
     */
    public function getGroupedTerms()
    {
        /** @var Term[] $terms */
        $terms = $this->modx->getCollection('Term');
        $letters = array();
        foreach ($terms as $termObj) {
            $term = $termObj->toArray();
            $cleanTerm = $this->cleanAlias($term['term']);
            $firstLetter = strtoupper(substr($cleanTerm, 0, 1));
            if (!isset($letters[$firstLetter])) {
                $letters[$firstLetter] = array();
            }
            $letters[$firstLetter][] = $term;
        };
        ksort($letters, SORT_NATURAL);
        return $letters;
    }

    /**
     * Get the Glossary terms
     *
     * @return array
     */
    public function getTerms()
    {
        /** @var Term[] $terms */
        $terms = $this->modx->getCollection('Term');
        $retArray = array();
        foreach ($terms as $term) {
            $retArray[] = $term->toArray();
        };
        return $retArray;
    }

    /**
     * Highlight terms in the text
     *
     * @param string $text
     * @param int $targetId
     * @param string $chunkName
     * @return string
     */
    public function highlightTerms($text, $targetId, $chunkName)
    {
        // Generate URL to target page
        $target = $this->modx->makeUrl($targetId);

        // Enable section markers
        $enableSections = $this->getOption('sections', null, false);
        if ($enableSections) {
            $splitEx = '#((?:' . $this->getOption('sectionsStart') . ').*?(?:' . $this->getOption('sectionsEnd') . '))#isu';
                $sections = preg_split($splitEx, $text, null, PREG_SPLIT_DELIM_CAPTURE);
        } else {
            $sections = array($text);
        }

        // Mask all terms first
        $terms = $this->getTerms();
        $maskStart = '<_^_>';
        $maskEnd = '<_$_>';
        $fullwords = $this->getOption('fullwords', null, true);
        $disabledAttributes = array_map('trim', explode(',', $this->getOption('disabledAttributes', null, 'title,alt')));
        $splitEx = '#((?:' . implode('|', $disabledAttributes) . ')\s*=\s*".*?")#isu';
        foreach ($terms as $term) {
            if ($fullwords) {
                foreach ($sections as &$section) {
                    if (($enableSections && strpos($section, $this->getOption('sectionsStart')) === 0 && preg_match('/\b' . preg_quote($term['term']) . '\b/u', $section)) ||
                        (!$enableSections && preg_match('/\b' . preg_quote($term['term']) . '\b/u', $section))
                    ) {
                        $subSections = preg_split($splitEx, $section, null, PREG_SPLIT_DELIM_CAPTURE);
                        foreach ($subSections as &$subSection) {
                            if (!preg_match($splitEx, $subSection)) {
                                $subSection = preg_replace('/\b' . preg_quote($term['term']) . '\b/u', $maskStart . $term['term'] . $maskEnd, $subSection);
                            }
                        }
                        $section = implode('', $subSections);
                    }
                }
            } else {
                foreach ($sections as &$section) {
                    if (($enableSections && strpos($section, $this->getOption('sectionsStart')) === 0 && strpos($section, $term['term']) !== false) ||
                        (!$enableSections && strpos($section, $term['term']) !== false)
                    ) {
                        $subSections = preg_split($splitEx, $section, null, PREG_SPLIT_DELIM_CAPTURE);
                        foreach ($subSections as &$subSection) {
                            if (!preg_match($splitEx, $subSection)) {
                                $subSection = str_replace($term['term'], $maskStart . $term['term'] . $maskEnd, $subSection);
                            }
                        }
                        $section = implode('', $subSections);
                    }
                }
            }
        }
        $text = implode('', $sections);

        // And replace the terms after to avoid nested replacement
        $html = $this->getOption('html');
        foreach ($terms as $term) {
            $term['explanation'] = ($html) ? $term['explanation'] : strip_tags($term['explanation']);
            $chunk = $this->modx->getChunk($chunkName, array(
                'term' => $term['term'],
                'link' => $target . '#' . strtolower(str_replace(' ', '-', $term['term'])),
                'explanation' => htmlspecialchars($term['explanation'], ENT_QUOTES, $this->modx->getOption('modx_charset')),
                'html' => ($html) ? '1' : ''
            ));
            $text = str_replace($maskStart . $term['term'] . $maskEnd, $chunk, $text);
        }

        // Remove remaining section markers
        $text = ($enableSections) ? str_replace(array(
            $this->getOption('sectionsStart'), $this->getOption('sectionsEnd')
        ), '', $text) : $text;
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
            return preg_replace('[^\W]', '', iconv($this->modx->getOption('modx_charset', null, 'UTF-8'), 'ASCII//TRANSLIT//IGNORE', $string));
        } else {
            return modResource::filterPathSegment($this->modx, $string);
        }
    }
}

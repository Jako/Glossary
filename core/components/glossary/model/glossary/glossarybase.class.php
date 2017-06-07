<?php
/**
 * Glossary Base Classfile
 *
 * Copyright 2012-2017 by Alan Pich <alan.pich@gmail.com>
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
    public $version = '2.2.1';

    /**
     * The class config
     * @var array $config
     */
    public $config = array();

    /**
     * GlossaryBase constructor
     *
     * @param modX $modx A reference to the modX instance.
     * @param array $config An config array. Optional.
     */
    function __construct(modX &$modx, $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->getOption('core_path', $config, $this->modx->getOption('core_path') . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $config, $this->modx->getOption('assets_path') . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $config, $this->modx->getOption('assets_url') . 'components/' . $this->namespace . '/');

        // Load some default paths for easier management
        $this->config = array_merge(array(
            'namespace' => $this->namespace,
            'version' => $this->version,
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'imagesUrl' => $assetsUrl . 'images/',
            'connectorUrl' => $assetsUrl . 'connector.php',
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
        ), $config);

        // set default options
        $this->config = array_merge($this->config, array(
            'glossaryStart' => $this->getOption('glossaryStart', $config, '<!-- GlossaryStart -->'),
            'glossaryEnd' => $this->getOption('glossaryEnd', $config, '<!-- GlossaryEnd -->'),
            'html' => (bool)$this->getOption('html', $config, true),
        ));

        $modx->getService('lexicon', 'modLexicon');
        $this->modx->lexicon->load($this->namespace . ':default');

        $this->modx->addPackage('glossary', $this->config['modelPath']);
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
            } elseif (array_key_exists($key, $this->config)) {
                $option = $this->config[$key];
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
        /** @var modResource $tmp Temporary resource to clean alias */
        $tmp = $this->modx->newObject('modResource');
        foreach ($terms as $termObj) {
            $term = $termObj->toArray();
            $cleanTerm = $tmp->cleanAlias($term['term']);
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
            $splitEx = '#((?:' . $this->getOption('glossaryStart') . ').*?(?:' . $this->getOption('glossaryEnd') . '))#isu';
            $sections = preg_split($splitEx, $text, null, PREG_SPLIT_DELIM_CAPTURE);
        } else {
            $sections = array($text);
        }

        // Mask all terms first
        $terms = $this->getTerms();
        $maskStart = '<_^_>';
        $maskEnd = '<_$_>';
        $fullwords = $this->getOption('fullwords', null, true);
        foreach ($terms as $term) {
            if ($fullwords) {
                foreach ($sections as &$section) {
                    if (($enableSections && substr($section, 0, strlen($this->getOption('glossaryStart'))) == $this->getOption('glossaryStart') && preg_match('/\b' . preg_quote($term['term']) . '\b/u', $section)) ||
                        (!$enableSections && preg_match('/\b' . preg_quote($term['term']) . '\b/u', $section))
                    ) {
                        $section = preg_replace('/\b' . preg_quote($term['term']) . '\b/u', $maskStart . $term['term'] . $maskEnd, $section);
                    }
                }
            } else {
                foreach ($sections as &$section) {
                    if (($enableSections && substr($section, 0, strlen($this->getOption('glossaryStart'))) == $this->getOption('glossaryStart') && strpos($text, $term['term']) !== false) ||
                        (!$enableSections && strpos($text, $term['term']) !== false)
                    ) {
                        $section = str_replace($term['term'], $maskStart . $term['term'] . $maskEnd, $section);
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
                'link' => $target . '#' . strtolower(str_replace(' ', '-', $term['term'])),
                'term' => $term['term'],
                'explanation' => htmlspecialchars($term['explanation'], ENT_QUOTES, $this->modx->getOption('modx_charset')),
                'html' => ($html) ? '1' : ''
            ));
            $text = str_replace($maskStart . $term['term'] . $maskEnd, $chunk, $text);
        }

        // Remove remaining section markers
        $text = ($enableSections) ? str_replace(array($this->getOption('glossaryStart'), $this->getOption('glossaryEnd')), '', $text) : $text;
        return $text;
    }
}

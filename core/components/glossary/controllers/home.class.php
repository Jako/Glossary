<?php
/**
 * Home Manager Controller class for Glossary
 *
 * @package glossary
 * @subpackage controllers
 */

/**
 * Class GlossaryHomeManagerController
 */
class GlossaryHomeManagerController extends modExtraManagerController
{
    /** @var Glossary $glossary */
    public $glossary;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        $corePath = $this->modx->getOption('glossary.core_path', null, $this->modx->getOption('core_path') . 'components/glossary/');
        $this->glossary = $this->modx->getService('glossary', 'Glossary', $corePath . 'model/glossary/', [
            'core_path' => $corePath
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function loadCustomCssJs()
    {
        $assetsUrl = $this->glossary->getOption('assetsUrl');
        $jsUrl = $this->glossary->getOption('jsUrl') . 'mgr/';
        $jsSourceUrl = $assetsUrl . '../../../source/js/mgr/';
        $cssUrl = $this->glossary->getOption('cssUrl') . 'mgr/';
        $cssSourceUrl = $assetsUrl . '../../../source/css/mgr/';

        if ($this->glossary->getOption('debug') && ($this->glossary->getOption('assetsUrl') != MODX_ASSETS_URL . 'components/glossary/')) {
            $this->addCss($cssSourceUrl . 'glossary.css?v=v' . $this->glossary->version);
            $this->addJavascript($jsSourceUrl . 'glossary.js?v=v' . $this->glossary->version);
            $this->addJavascript($jsSourceUrl . 'widgets/home.panel.js?v=v' . $this->glossary->version);
            $this->addJavascript($jsSourceUrl . 'widgets/terms.grid.js?v=v' . $this->glossary->version);
            $this->addJavascript(MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.grid.settings.js');
            $this->addJavascript($jsSourceUrl . 'widgets/settings.panel.js?v=v' . $this->glossary->version);
            $this->addLastJavascript($jsSourceUrl . 'sections/home.js?v=v' . $this->glossary->version);
        } else {
            $this->addCss($cssUrl . 'glossary.min.css?v=v' . $this->glossary->version);
            $this->addJavascript(MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.grid.settings.js');
            $this->addLastJavascript($jsUrl . 'glossary.min.js?v=v' . $this->glossary->version);
        }
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Glossary.config = ' . json_encode($this->glossary->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ';
            MODx.load({xtype: "glossary-page-home"});
        });
        </script>');
        $this->loadRichTextEditor();
    }

    /**
     * {@inheritDoc}
     * @return string[]
     */
    public function getLanguageTopics()
    {
        return ['core:setting', 'glossary:default'];
    }

    /**
     * {@inheritDoc}
     * @param array $scriptProperties
     */
    public function process(array $scriptProperties = [])
    {
    }

    /**
     * {@inheritDoc}
     * @return string|null
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('glossary');
    }

    /**
     * {@inheritDoc}
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->glossary->getOption('templatesPath') . 'home.tpl';
    }

    /**
     * Load rich text editor.
     */
    public function loadRichTextEditor()
    {
        $useEditor = $this->modx->getOption('use_editor');
        $whichEditor = $this->modx->getOption('which_editor');
        if ($useEditor && !empty($whichEditor)) {
            $onRichTextEditorInit = $this->modx->invokeEvent('OnRichTextEditorInit', [
                'editor' => $whichEditor
            ]);
            if (is_array($onRichTextEditorInit)) {
                $onRichTextEditorInit = implode('', $onRichTextEditorInit);
            }
            $this->addHtml($onRichTextEditorInit);
        }
    }
}

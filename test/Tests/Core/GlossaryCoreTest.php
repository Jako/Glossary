<?php
/**
 * Glossary Core Tests
 *
 * @package glossary
 * @subpackage test
 */

class GlossaryCoreTest extends GlossaryTestCase
{
    public function testAddGlossary()
    {
        $source = file_get_contents($this->modx->config['testPath'] . 'Data/Page/source.page.tpl');
        $terms = [
            'Template' => '<span class="glossary-term" data-toggle="tooltip" data-html="true" title="" data-original-title="<p>Template <strong>Test</strong> Template</p>">Template</span>'
        ];
        $this->glossary->options['sections'] = true;
        $this->glossary->options['fullwords'] = false;
        $source = $this->glossary->highlightTerms($source, $terms);
        $result = file_get_contents($this->modx->config['testPath'] . 'Data/Page/result.page.tpl');

        $this->assertEquals($source, $result);
    }

    public function testAddGlossaryFullwords()
    {
        $source = file_get_contents($this->modx->config['testPath'] . 'Data/Page/source.page.tpl');
        $terms = [
            'Template' => '<span class="glossary-term" data-toggle="tooltip" data-html="true" title="" data-original-title="<p>Template <strong>Test</strong> Template</p>">Template</span>'
        ];
        $this->glossary->options['sections'] = true;
        $this->glossary->options['fullwords'] = true;
        $source = $this->glossary->highlightTerms($source, $terms);
        $result = file_get_contents($this->modx->config['testPath'] . 'Data/Page/result_fullwords.page.tpl');

        $this->assertEquals($source, $result);
    }
}

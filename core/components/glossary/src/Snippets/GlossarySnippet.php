<?php
/**
 * Glossary Snippet
 *
 * @package glossary
 * @subpackage snippet
 */

namespace TreehillStudio\Glossary\Snippets;

class GlossarySnippet extends Snippet
{
    /**
     * Get default snippet properties.
     *
     * @return array
     */
    public function getDefaultProperties()
    {
        return [
            'outerTpl' => 'Glossary.listOuterTpl',
            'groupTpl' => 'Glossary.listGroupTpl',
            'termTpl' => 'Glossary.listItemTpl',
            'showNav::bool' => true,
            'navOuterTpl' => 'Glossary.navOuterTpl',
            'navItemTpl' => 'Glossary.navItemTpl',
            'showEmptySections::bool' => false,
            'toPlaceholder' => ''
        ];
    }

    /**
     * Execute the snippet and return the result.
     *
     * @return string
     * @throws /Exception
     */
    public function execute()
    {
        $output = '';

        // Grab all terms grouped by first letter
        $letters = $this->glossary->getGroupedTerms($this->getProperty('showEmptySections'));

        $navHTML = '';
        // Show navigation list (if on)
        if ($this->getProperty('showNav')) {
            // Prepare letter chunks
            $tplLetters = '';
            foreach ($letters as $letter => $terms) {
                if (count($terms) > 0) {
                    $tplLetters .= $this->modx->getChunk($this->getProperty('navItemTpl'), [
                        'letter' => $letter,
                        'class' => ''
                    ]);
                } elseif ($this->getProperty('showEmptySections')) {
                    $tplLetters .= $this->modx->getChunk($this->getProperty('navItemTpl'), [
                        'letter' => $letter,
                        'class' => 'disabled'
                    ]);
                }
            }

            // Wrap letters in outer tpl
            $navHTML = $this->modx->getChunk($this->getProperty('navOuterTpl'), [
                'letters' => $tplLetters
            ]);
            $output .= $navHTML;
        }

        // Output all terms (grouped)
        $groupsHTML = '';
        foreach ($letters as $letter => $terms) {
            $termsHTML = '';
            if (count($terms)) {
                // Prepare Terms HTML
                foreach ($terms as $term) {
                    $params = array_merge($term, [
                        'anchor' => $this->glossary->getAnchor($term['term']),
                        'letter' => $letter
                    ]);
                    $termsHTML .= $this->modx->getChunk($this->getProperty('termTpl'), $params);
                }
                // Prepare letter wrapper HTML
                $groupsHTML .= $this->modx->getChunk($this->getProperty('groupTpl'), [
                    'items' => $termsHTML,
                    'letter' => $letter
                ]);
            }
        }

        // Add groups to outer wrapper
        $itemsHTML = $this->modx->getChunk($this->getProperty('outerTpl'), [
            'groups' => $groupsHTML
        ]);
        $output .= $itemsHTML;

        if ($this->getProperty('toPlaceholder') != '') {
            $this->modx->setPlaceholders([
                '.nav' => $navHTML,
                '.items' => $itemsHTML,
                '' => $output,
            ], $this->getProperty('toPlaceholder'));
            $output = '';
        }
        return $output;
    }
}

<?php
/**
 * Glossary Snippet
 *
 * @package glossary
 * @subpackage snippet
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption('glossary.core_path', null, $modx->getOption('core_path') . 'components/glossary/');
$glossary = $modx->getService('glossary', 'GlossaryBase', $corePath . 'model/glossary/', array(
    'core_path' => $corePath
));

// Get options
$outerTpl = $modx->getOption('outerTpl', $scriptProperties, 'Glossary.listOuterTpl', true);
$groupTpl = $modx->getOption('groupTpl', $scriptProperties, 'Glossary.listGroupTpl', true);
$termTpl = $modx->getOption('termTpl', $scriptProperties, 'Glossary.listItemTpl', true);
$navOuterTpl = $modx->getOption('navOuterTpl', $scriptProperties, 'Glossary.navOuterTpl', true);
$navItemTpl = $modx->getOption('navItemTpl', $scriptProperties, 'Glossary.navItemTpl', true);
$showNav = (bool)$modx->getOption('showNav', $scriptProperties, true, true);
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, '', true);

// Outputness
$output = '';

// Grab all terms grouped by first letter
$letters = $glossary->getGroupedTerms();

// Show navigation list (if on)
$navHTML = '';
if ($showNav) {
    // Prepare letter chunks
    $tplLetters = '';
    foreach ($letters as $letter => $terms) {
        if (count($terms) > 0) {
            $tplLetters .= $modx->getChunk($navItemTpl, array(
                'letter' => $letter
            ));
        };
    };
    // Wrap letters in outer tpl
    $navHTML = $modx->getChunk($navOuterTpl, array(
        'letters' => $tplLetters
    ));
    $output .= $navHTML;
};

// Output all terms (grouped)
$termsHTML = '';
$groupsHTML = '';
foreach ($letters as $letter => $terms) {
    if (count($terms)) {
        // Prepare Terms HTML
        foreach ($terms as $term) {
            $params = array_merge($term, array(
                'anchor' => strtolower(str_replace(' ', '-', $term['term'])),
                'letter' => $letter
            ));
            $termsHTML .= $modx->getChunk($termTpl, $params);
        };
        // Prepare letter wrapper HTML
        $groupsHTML .= $modx->getChunk($groupTpl, array(
            'items' => $termsHTML,
            'letter' => $letter
        ));
    };
};

// Add groups to outer wrapper
$output .= $modx->getChunk($outerTpl, array(
    'groups' => $groupsHTML
));

if ($toPlaceholder != '') {
    $modx->setPlaceholders(array(
       '.nav' => $navHTML,
       '.items' => $termsHTML,
       '' => $output,
    ), $toPlaceholder);
    $output = '';
}
return $output;

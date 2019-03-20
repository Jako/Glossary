## Introduction

This MODX Extra adds a custom manager page that allows you to create and
maintain a list of explanations for key terms in your site. Entries into the
glossary take the form of `term => explanation` where `term` is the phrase being
described and `explanation` is the description of said term.

Included in the package is a snippet called Glossary which will output the 
glossary terms to a resource page. The snippet is fully templatable using 
chunks specified as optional parameters to the snippet call, and various 
features can be turned on or off in the same manner.

## Snippet

This snippet outputs a list of terms and explanations, ordered alphabetically 
and grouped by first letter. It also ouputs a navigation list of links at the 
top of the glossary list to allow a user to jump to a specific letter.
The following properties could be set in the snippet call:

Property | Description | Default
---------|-------------|--------
showNav | Show the letter nav at the top of the list. | 1 (Yes)
outerTpl | Template chunk for glossary outer wrapper. | Glossary.listOuterTpl
groupTpl | Template chunk for glossary item group. | Glossary.listGroupTpl
termTpl | Template chunk for glossary term items. | Glossary.listItemTpl
navOuterTpl | Template chunk for outer nav-bar wrapper. | Glossary.navOuterTpl
navItemTpl | Template chunk for each item in the nav-bar. | Glossary.navItemTpl
toPlaceholder | If set, will assign the result to this placeholder instead of outputting it directly. [^1] | -

[^1]: If you fill the toPlaceholder property i.e. with the value
`glossaryResult`, the placeholder `[[+glossaryResult]]` is filled by the output
of the snippet. This also creates two additional placeholders:
`[[+glossaryResult.nav]]` and `[[+glossaryResult.items]]`.

## Plugin

The Highlighter plugin parses page content field on render and replaces all
occurrences of terms with markup defined in the plugin's tpl chunk. This can be
used to provide a link directly to the glossary entry for that term. The Plugin 
could be controlled by the following MODX System settings:

Setting | Description | Default
------------|---------|--------
debug | Log debug informations in the MODX error log. | No
disabledAttributes | (Comma separated list) Glossary does not replace text inside of this HTML tag attributes. | title,alt
fullwords | Replace only full words of a glossary term in the resource content. [^2] | Yes
html | Allow HTML in the explanation (enables a rich-text editor in the term form). | Yes
resid | ID of a resource containing a Glossary snippet call. | 0
sections | Replace Glossary links only in sections marked with `<!— GlossaryStart -->` and `<!— GlossaryEnd -->`. The section markers could be changed with the settings `glossary.sectionsStart` and `glossary.sectionsEnd`. | No
sectionsEnd | Marker at the end of a section processed by Glossary. The restriction to marked sections can be activated in the setting `glossary.sections`.
sectionsStart | Marker at the start of a section processed by Glossary. The restriction to marked sections can be activated in the setting `glossary.sections`.
tpl | Template Chunk for the highlight replacement. | Glossary.highlighterTpl

[^2]: The word boundary detection works only with not entity encoded text. So please check, if the richtext editor of the site produces the right output.

## Available placeholders

The following placeholders are available in the chunks used by the snippet and
the plugin or in the resource output:

Placeholder | Description | Chunk
------------|-------------|------
link | Link url including hash anchor. | highlighterTpl
groups | The list of term groups. | outerTpl
items | The list of terms. | groupTpl
anchor | The anchor for the term being referenced. | listItemTpl
term | The term being referenced. | listItemTpl, highlighterTpl
explanation | The explanation for this term. | listItemTpl, highlighterTpl
letters | The list of letters in the letter nav. | navOuterTpl
letter | One letter in the letter nav. | groupTpl, navItemTpl
placeholder | Optional placeholder, that is created by the toPlaceholder property: The whole snippet output [^3] | Resource output
placeholder.nav | Optional placeholder, that is created by the toPlaceholder property: Outputs the nav only [^3] | Resource output
placeholder.items | Optional placeholder, that is created by the toPlaceholder property: Outputs the items (terms & explanations) [^3] | Resource output

[^3]: See the toPlaceholder snippet property.

The default chunks for these placeholders are available with the `Glossary.`
prefix. If you want to change the chunks, you have to duplicate them and
change the duplicates. The default chunks are reset with each update of the 
Glossary extra.

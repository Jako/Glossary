# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.5.1] - TBA

### Changed

- Compatibility with Redactor

## [2.5.0] - 2021-12-29

### Changed

- Code refactoring
- Full MODX 3 compatibility

## [2.4.4] - 2021-12-28

### Added

- Sort the grouped by letter terms alphabetical in the Glossary snippet output

## [2.4.3] - 2020-11-17

### Added

- showEmptySections snippet property for the Glossary snippet

### Changed

- Fix an error with invalid anchor tag characters

## [2.4.2] - 2019-03-20

### Changed

- Set the glossary.resid system setting during install by setup options

## [2.4.1] - 2019-03-20

### Changed

- Quote the regular expression delimiter in the Glossary term
- Check permission instead of usergroup for displaying the Glossary system setting tab

## [2.4.0] - 2018-01-16

### Added

- Don't replace Glossary terms in definable html tags
- Don't replace Glossary terms in all html tag attributes

## [2.3.0] - 2018-12-15

### Added

- Don't replace Glossary terms in definable html tag attributes
- System settings for sectionsStart and sectionsEnd
- Edit Glossary system settings in the custom manager page

## [2.2.1] - 2017-11-30

### Changed

- Bugfix for duplicated entries in the glossary overview

## [2.2.0] - 2017-02-20

### Added

- Output the result to custom placeholders via the toPlaceholder property

## [2.1.0] - 2016-10-19

### Added

- Use a Rich Text Editor for the explanation, if a MODX RTE is installed and the Glossary system setting html is enabled
- Use bootstrap data attributes for tooltip generation in the default templates

## [2.0.2] - 2016-06-09

### Changed

- Don't replace terms nested in explanations of another term

## [2.0.1] - 2016-06-06

### Added

- Restrict the term replacements to marked sections, if that option is enabled in the MODX system settings
- Terms starting with a special char are sorted into the character group according to the MODX translit result

## [2.0.0] - 2016-05-30

### Added

- Replace only full words of a glossary term in the resource content (could be disabled by a MODX system setting)
- Restrict the chars of the glossary term to letters, numbers, a space and the following characters: :.;,-_
- MySQL 5.7 compatibility
- Generate the transport package with GPM
- Uglify manager javascripts

### Changed

- Change the plugin properties to MODX system settings, since the system settings will survive package updates
- Improve and refactor the code

## [1.1.0] - 2018-11-30

### Added

- Transport package

### Changed

- Output templateable by chunks

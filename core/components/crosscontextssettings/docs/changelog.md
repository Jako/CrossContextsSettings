# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2021-03-06

### Added

- Update resolver for deleting old files and modifying old menu entries

## [1.1.0] - 2021-03-04

### Added

- Restrict the displayed contexts by system setting
- Clear cache after creating/editing a context setting by system setting

### Changed

- Remove modAction usage
- Don't save the context_key and the area in updatefromgrid processor [#19]
- Fix ONLY_FULL_GROUP_BY issue [#22]
- Remove limit from Contexts [#23]
- Fix overflow scroll issue [#18]
- List the contexts of all namespaces by default [#15]

## [1.0.5] - 2016-01-22

### Added

- Namespace and area columns in the grid

### Changed

- Fix pagination not working [#13]

## [1.0.4] - 2016-01-18

### Changed

- Fixed grid for context's name beyond [w]eb [#14]

## [1.0.3] - 2015-08-05

### Changed

- Lost contexts when parsing the panels [#12]

## [1.0.2] - 2015-07-28

### Added

- "Clear Cache" tab [#9]

### Changed

- Fixed bug on 0 as value on "Create" [#8]
- Set correct input type for setting value [#10]

## [1.0.1] - 2015-07-26

### Changed

- Fixed bug on 0 as value [#8]
- Ignored same value [#8]

## [1.0.0] - 2015-07-25

### Added

- Delete context setting row action
- New context setting button [#2]

## [1.0.0-b3] - 2015-07-03

### Changed

- Fixing pagination issue in MODX Revolution 2.3
- Reduced pagesize to 10 and adapted grid height to show the whole grid (autoHeight is not possible because of lockinggridview limitations)
- Fixing area filter (processor does not exist)
- Fixing edited triangle does not go away [#3]
- Changed UX to make the CMP look the same as other extras

## [1.0.0-b2] - 2014-09-30

### Changed

- Prevent saving/remove setting when value is empty

## [1.0.0-b1] - 2014-09-25

### Added

- Initial release

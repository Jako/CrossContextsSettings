# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.] - 2024-03-20

### Added

- Add toggle all checkbox on the clear cache tab in the custom manager page

### Changed

- Also clear the DB cache when clearing the cache

## [1.2.6] - 2024-02-07

### Fixed

- Fix missing grid height update in some circumstances

## [1.2.5] - 2024-01-18

### Fixed

- Set context setting grid height on base of the content

## [1.2.4] - 2023-03-11

### Added

- Set the namespace/area of new context settings on base of the namespace/area filter in the cross context setting grid.
- Update the xtype, namespace and area when saving the update context setting window.
- Set the key of a context setting readonly in the update context setting window.

## [1.2.3] - 2022-04-05

### Fixed

- Fix create contexts settings

## [1.2.2] - 2022-03-17

### Fixed

- Fix editing values in the grid
- Fix save new context settings

## [1.2.1] - 2022-02-25

### Fixed

- Fix a MySQL 8 group by sort issue
- Fix some inconsistencies in the lexicon

## [1.2.0] - 2022-01-04

### Added

- Allow row editing in a modal window
- Use the xtype while editing in the modal window
- Actionbuttons in the grid

### Changed

- Code refactoring
- Full MODX 3 compatibility

### Fixed

- Resize issues with the locking grid

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
- Remove limit from Contexts [#23]
- List the contexts of all namespaces by default [#15]

### Fixed

- ONLY_FULL_GROUP_BY issue [#22]
- Overflow scroll issue [#18]


## [1.0.5] - 2016-01-22

### Added

- Namespace and area columns in the grid

### Fixed

- Pagination not working [#13]

## [1.0.4] - 2016-01-18

### Fixed

- Grid for context's name beyond [w]eb [#14]

## [1.0.3] - 2015-08-05

### Changed

- Lost contexts when parsing the panels [#12]

## [1.0.2] - 2015-07-28

### Added

- "Clear Cache" tab [#9]

### Fixed

- Bug on 0 as value during "Create" [#8]
- Set correct input type for setting value [#10]

## [1.0.1] - 2015-07-26

### Changed

- Ignored same value [#8]

### Fixed

- Bug on 0 as value [#8]

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

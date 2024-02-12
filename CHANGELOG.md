# Changelog

All notable changes will be documented in this file following the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) 
format. This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2024-02-12

### Added

-   Added support for view hooks as closures that return a view
-   Added support for explicitly setting the view name in `<x-hook>`

## [0.1.0] - 2024-01-24

### Changed

-   Added `Context` and moved `stopPropagation()` to it
-   Refactored how hooks are registered in the `HookRegistry`

## [0.0.4] - 2024-01-22

### Changed

-   We now filter out results that are `null`

## [0.0.3] - 2024-01-21

### Added

-   Made hook calls fluent

## [0.0.2] - 2024-01-21

### Changed

-   Renamed `Breakpoints` to `Hooks`
-   Removed unused global `hook()` helper

## [0.0.1] - 2024-01-21

### Added

-   Initial release

## [0.0.1]

# Keep a Changelog Syntax

-   `Added` for new features.
-   `Changed` for changes in existing functionality.
-   `Deprecated` for soon-to-be removed features.
-   `Removed` for now removed features.
-   `Fixed` for any bug fixes. 
-   `Security` in case of vulnerabilities.

[Unreleased]: https://github.com/glhd/hooks/compare/0.2.0...HEAD

[0.2.0]: https://github.com/glhd/hooks/compare/0.1.0...0.2.0

[0.1.0]: https://github.com/glhd/hooks/compare/0.0.4...0.1.0

[0.0.4]: https://github.com/glhd/hooks/compare/0.0.3...0.0.4

[0.0.3]: https://github.com/glhd/hooks/compare/0.0.2...0.0.3

[0.0.2]: https://github.com/glhd/hooks/compare/0.0.1...0.0.2

[0.0.1]: https://github.com/glhd/hooks/compare/0.0.1...0.0.1

[0.0.1]: https://github.com/glhd/hooks/compare/0.0.1...0.0.1

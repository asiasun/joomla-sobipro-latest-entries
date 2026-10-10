# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-10-10

### Added
- Dual compatibility for Joomla 3.10 and Joomla 4 / 5.
- Configurable parameters for item count, section ID, sort field, title field, cover field, category field, entry base path, and optional category filter.
- English and Persian administrator language files.
- Helper class for SobiPro field decoding and entry queries.
- `CHANGELOG.md` shipped in the install package.
- Joomla Update Server declaration with SHA-256 support.

### Changed
- Module element renamed from `mod_lidapp_latest_games` to `mod_sobipro_latest_entries`.
- Logic moved out of the layout file into a dedicated helper.
- CSS class names updated to remove the previous prefix.

### Fixed
- Output of titles and URLs is escaped.
- Database access uses the query builder and `getDbo()`.

## [1.0.0] - 2016-06-15

### Notes
- Original Joomla 2.5 module.

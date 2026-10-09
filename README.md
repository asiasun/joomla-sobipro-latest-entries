# Joomla SobiPro Latest Entries

Site module for [Joomla](https://www.joomla.org/) that lists the latest entries from a [SobiPro](https://www.sigsiu.net/) section.

**Requires:** Joomla 3.10 or 4.x / 5.x and SobiPro.

## Install

Install `mod_sobipro_latest_entries_2.1.0.zip` from **System → Install → Extensions**. Set the field IDs for your section and publish the module.

Existing sites that used `mod_lidapp_latest_games` should uninstall the old module first. Both layouts are included, so a template override is not required.

## Layouts

- **Default:** banner background, title, release date or released state, and up to five category names.
- **Simple:** cover thumbnail and title.

Choose the layout under **Advanced → Layout**.

## Field IDs

Open **Components → SobiPro → your section → Fields** and note the title, cover, banner, date/sort and category field IDs.

## Entry URL

Links are built as `{site}/{entry_base_path}/{sid}-{nid}`.

## Repository

Canonical repo: `https://github.com/asiasun/joomla-sobipro-latest-entries`

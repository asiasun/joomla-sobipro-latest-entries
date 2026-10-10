# Joomla SobiPro Latest Entries

Site module for [Joomla](https://www.joomla.org/) that lists the latest entries from a [SobiPro](https://www.sigsiu.net/) section, ordered by a configurable field.

**Requires:** Joomla 3.10 or 4.x / 5.x and SobiPro.

## Install

Install `mod_sobipro_latest_entries_2.0.0.zip` from **System → Install → Extensions**. Create a site module instance, set the field IDs for your section, and publish it.

Existing sites that used `mod_lidapp_latest_games` should uninstall the old module first. Settings are not migrated automatically because the element name changed.

## Finding field IDs

Most parameters accept a SobiPro field ID (`fid`). On many sites the title field is `1`, but image, category, and date fields vary.

**In SobiPro**

1. Open **Components → SobiPro**.
2. Open the section that holds the entries.
3. Open **Fields**.
4. Note the **ID** of the title field, the image/cover field, the category field, and the field you want to sort by (often a date).

**In the database**

Replace `123` with a real entry `sid`:

```sql
SELECT fid, nid, baseData
FROM #__sobipro_field_data
WHERE sid = 123
ORDER BY fid;
```

Use the `fid` of the row that contains the title, the image data, or the category list.

## Entry URL

The module builds links as:

`{site}/{entry_base_path}/{category_nid}/{sid}-{entry_nid}`

Set **Entry base path** to match your SEF structure (for example `game-profile`). Leave it empty if entries are served from the site root.

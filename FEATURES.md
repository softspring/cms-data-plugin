# CMS Data Plugin Features

Functional definition for `softspring/cms-data-plugin`.

This package extends Softspring CMS with data import, export, and fixture workflows.

## Purpose

- Move CMS data between environments or projects.
- Import exported content and content versions from the admin UI.
- Export content versions from the admin UI.
- Load CMS fixtures from project files.
- Dump current CMS data into reusable fixture files.

## Main Features

- Admin import action for CMS content types.
- Admin import action for CMS content versions.
- Admin export action for CMS content versions.
- Content configuration extension that adds `import`, `version_import`, and `export_version` admin options.
- Data import and export services for CMS content, routes, menus, blocks, and media references.
- Entity transformers for content, pages, routes, menus, blocks, and media.
- Field transformers for arrays, entities, routes, sites, blocks, media, translations, and default scalar values.
- Reference repository used to resolve imported entities and cross-element references.
- Structured YAML and JSON storage helpers.
- ZIP archive extraction and packaging helpers for import/export payloads.
- Doctrine fixtures loader for `cms/fixtures` data.
- CMS purger factory for Doctrine fixtures purge operations.
- `sfs:cms:dump-fixtures` command for exporting current CMS data into fixture files.

## Expected Usage

- Install it in a Symfony project that already uses `softspring/cms-bundle`.
- Enable the plugin through the CMS plugin registration flow.
- Use the added admin actions to import or export content data.
- Store project fixtures under `cms/fixtures` when using fixture loading.
- Use the dump command when you need to regenerate fixture files from existing CMS data.

## Extension Points

- Add custom entity transformers for additional CMS-related entities.
- Add custom field transformers for project-specific serialized fields.
- Listen to the plugin events declared in `SfsCmsDataPlugin` to customize import and export flows.
- Override the admin templates through Symfony template resolution.
- Replace import form types through content configuration when a project needs a custom form.

## Current Limits

- The package depends on `softspring/cms-bundle` and its content configuration model.
- It does not ship frontend assets.
- It currently has no executable PHPUnit test cases in this repository.
- Some default fixture paths are designed around the CMS project directory conventions.

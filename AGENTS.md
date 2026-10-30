# WP AbNet theme: agent instructions

## Scope and starting point

This file applies to the `wp-alexboia-net` theme ("Wp AbNet"), the theme of the AlexBoia.NET blog. It is derived from the stock Twenty Twelve theme and has been extended with its own metadata layer (Open Graph tags, schema.org JSON-LD).

- Treat this directory as the working and Git root. It is nested inside a larger WordPress installation (`d:\Proiecte\Alexboia.NET`) whose other themes and plug-ins have their own repositories; run Git commands here or use `git -C <theme-root>`.
- Inspect `git status --short` and the relevant code before editing. Preserve existing user changes.
- Keep changes within the theme. Do not modify WordPress core, the stock `twenty*` themes or plug-ins as incidental cleanup. A plug-in may have its own `AGENTS.md` (for instance `../../plugins/abp01-travel-tech-box/AGENTS.md`); follow it when a task spans both.

## Repository map

| Location | Responsibility |
| --- | --- |
| `style.css` | Theme metadata header (name, version, text domain) and main stylesheet |
| `functions.php` | Theme setup, assets, widgets, template tags; loads the files below |
| `autoload.php` | Autoloader mapping the `AbNetTheme\` namespace to `includes/` (PSR-4 style) |
| `functions-meta.php` | Hooks `wp_head` to output Open Graph tags and schema.org data; exposes their filters |
| `functions-admin.php` | Admin/editor customizations (TinyMCE, Enlighter code block buttons) |
| `functions-settings.php` | Settings > Meta & SEO page (registration, rendering) |
| `includes/Settings/` | `MetaSettings`: configurable meta attributes (author person, about page, `sameAs`, site name, blog meta description) with their defaults |
| `functions-seo.php` | SEO title/description metabox (post meta `_abnet_seo_title`, `_abnet_seo_description`) and the filters that apply them |
| `functions-plugin-connectors.php` | Small front-end adjustments for third-party plug-ins (e.g. dequeuing assets where they are not used); the only place for plug-in-specific code |
| `includes/OGMetaTags.php`, `includes/SchemaOrgManager.php` | Generation of OG meta tags and schema.org nodes for the current page |
| `includes/Model/` | `ThemeHookContext` (page context), `MetaTag`, `SchemaOrg` (JSON-LD node), `SchemaOrgBuilder` (factories) |
| `includes/Model/SchemaOrg/` | Immutable `*Info` argument objects for the builder factories |
| `*.php` in the root, `content-*.php`, `page-templates/` | Template hierarchy files and template parts, mostly inherited from Twenty Twelve |
| `views/` | PHP views (admin markup such as the SEO metabox), rendered with `abnet_render_view($name, $viewModel)` |
| `includes/ViewModel/` | Explicit view models for the views, implementing the `ViewModel` marker interface |
| `js/`, `js/admin/` | Front-end scripts (navigation, customizer) and editor plug-ins |
| `css/`, `img/` | Auxiliary styles, fonts (`css/fonts/`), images |
| `css/admin/` | Admin styles; `settings.css` gives the theme settings pages the theme identity (scope: `.abnet-settings`, loaded only on those pages) |
| `languages/` | `ro_RO.po`/`ro_RO.mo` translations for the `abnet` text domain |
| `docs/` | Developer documentation and examples for the schema.org layer |

## Coding conventions

### PHP

- **Baseline:** target PHP 8.2 or newer, since the code already relies on `readonly` classes, named arguments and union types. Do not lower the syntax level without reason.
- **Whitespace:** use compact spacing, not the padded WordPress style.
  - Write `if (!empty($attachments)) {`, `get_post($parentId)` and `function foo(array $items): array {`.
  - Use no spaces just inside `()` or `[]`, and none after the unary `!`.
  - Put one space after a comma and around binary operators, assignments and `=>`.
  - Do not align `=>` in associative arrays or `=` in consecutive assignments.
- **Legacy files:** the Twenty Twelve templates still use the padded style (`functions.php` has been converted). Write new and changed code compactly, but do not reformat whole legacy files as a side effect unless asked.
- **Indentation and literals:** indent with tabs. Use `array(...)` for array literals and lowercase `true`, `false` and `null`.
- **Classes:** new classes live under `includes/` in the `AbNetTheme\` namespace, with a file path matching the namespace (the autoloader requires it).
  - Private members use a leading underscore: `$_context`, `_addPostSchema()`.
  - Public methods, parameters and variables use lower camel case.
  - Put the bootstrap guard `if (!defined('ABSPATH')) { exit; }` after the namespace/`use` block, as in the existing classes.
- **Global functions:** prefix them with `abnet_`; use `_abnet_` for internal helpers.
- **Guard files:** every directory carries an `index.php` guard file (`<?php exit;`). Add one when creating a new directory.
- **Views:** markup goes into `views/<name>.php`, not into functions. Each view gets an explicit view model: a `final readonly` class in `includes/ViewModel/` (named `<Name>ViewModel`) that implements `AbNetTheme\ViewModel\ViewModel`, passed to `abnet_render_view()` and available in the view as `$data`. Declare it with a `@var <Name>ViewModel $data` docblock at the top of the view. Do not pass `stdClass` or arrays.
- **Configurable values:** site-level values that may change (names, URLs, profiles, descriptions) belong in a settings class under `includes/Settings/` with a default value, editable from an admin settings page, not in constants or hardcoded strings.
- **Builder arguments:** pass arguments to `SchemaOrgBuilder` factories as `final readonly` `*Info` objects in `includes/Model/SchemaOrg/`, not as long positional parameter lists.

### Hooks

- **Existing hooks:** theme hooks use the `abnet_` prefix (`abnet_og_meta_tags`, `abnet_schema_org`, `abnet_og_post_description`, ...). The schema property filters `wp_abnet_schema_org_properties_person`, `..._breadcrumbs`, `..._blog_posting`, `..._profile_page` and `..._website` use a `wp_abnet_` prefix by deliberate choice. Preserve existing names, argument order and accepted-argument counts, because plug-ins depend on them.
- **Documentation:** document each `apply_filters`/`do_action` call immediately above it with its purpose, `@since WP AbNet 1.0` and ordered `@param` entries.
- **Theme and plug-ins:** the theme does not know about specific plug-ins. It only exposes hooks; plug-ins integrate by hooking into them. For instance, WP Trip Summary adds `contentLocation` to the BlogPosting through `wp_abnet_schema_org_properties_blog_posting`. Do not add plug-in-specific logic to the theme. The one exception is `functions-plugin-connectors.php`, which holds small adjustments to third-party plug-ins' front-end behaviour (such as loading their assets only where they are used); each must check that its plug-in is loaded and do nothing otherwise.
- **`@id` identifiers:** schema.org nodes that others reference keep stable values: `<home url>/#author` (the person behind the blog, also the publisher), `<home url>/#website` and `<permalink>#article`. Changing them breaks references emitted by plug-ins.

### Other assets

- **Translations:** use the `abnet` text domain for user-facing strings, and keep `languages/ro_RO.po` and its compiled `.mo` in sync when adding translatable text.
- **JavaScript:** has no build step. Edit the scripts directly and keep the IIFE wrapper style of the surrounding file.
- **Line endings:** the theme mixes them. The inherited templates use LF; newer files (`functions-meta.php`, `functions-admin.php`, `includes/`, `docs/`) use CRLF. Preserve each file's existing endings and use CRLF for new files.

## Validation

There is no test suite, linter configuration or build tooling. Choose checks that exercise the change:

```text
php -l <each touched PHP file>
git diff --check
```

`git diff --check` reports the CR of CRLF lines as trailing whitespace; that is expected for CRLF files.

For changes to generated markup (meta tags, JSON-LD), render the output against the local installation. The development site runs at `http://alexboia.net.local:8080`.

- Use a throwaway script outside the theme (in a scratch directory) that requires `d:/Proiecte/Alexboia.NET/wp-load.php`, sets up the query for the page type under test, and calls `abnet_output_og_meta_tags()` or `abnet_output_schema_org()`.
- Keep such scripts read-only with respect to the database.
- Validate JSON-LD structure with the schema.org validator or Google's Rich Results Test when available.

For template, CSS or JavaScript changes, check the affected pages in a browser, including the console. Report when browser verification was not possible.

## Completion

- **Before finishing:** inspect the diff and `git status`, and verify that only intended files changed.
- **`error_log`:** it may appear in the theme root; it is a runtime artifact. Do not commit or edit it.
- **Releases:** do not bump the `Version:` in `style.css`, regenerate translations wholesale or commit unless asked.
- **Documentation:** when changing the schema.org layer's public surface (builder signatures, filters, `@id` conventions), update `docs/SCHEMA-README.md` and `docs/SCHEMA_USAGE_EXAMPLES.php` accordingly.

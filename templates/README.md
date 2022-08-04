# Templates

The template directory is intend only to modify post types and custom pages handled by w4os. As such, it should not contain overrides for full page (except for the splash page).

Other overrides must be left to the theme or implemented with blocks.

Eventually, these templates will be overridable by the theme, too.

## Content templates

Only for post types handled by w4os (e.g. to properly display avatar profile). Currently:

- `content-avatar.php` avatar info (profile, likes, wants, etc.)
- `content-page-profile.php` _(will be deprecated in 3.0)_
- `content-configuration.php` automatically generated configuration instructions, including the actual grid settings

Some other templates do remain from legacy version and will eventually get removed or adapted.

## Page templates

Override only for specific use cases

- `page-splash.php` viewer splash page, usually requires to remove everything except content
- `page-profile-viewer.php` content accessed from the viewer like web profile or web search need a cleaner display. It would probably be better managed with filters, though

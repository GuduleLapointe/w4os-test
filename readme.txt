=== w4os - OpenSimulator Web Interface (dev) ===

- Contributors: magicoli69,gudulelapointe
- Donate link: <https://w4os.org/donate/>
- Tags: OpenSimulator, Second Life, metaverse, avatar, web interface, grids, standalone, hypergrid, 3D
- Requires at least: 5.3.0
- Requires PHP: 7.4
- Tested up to: 6.0.1
- Stable tag: 2.3.9
- License: AGPLv3
- License URI: <https://www.gnu.org/licenses/agpl-3.0.txt>

WordPress interface for OpenSimulator (w4os)

== Description ==

**DO NOT USE THIS DEV BRANCH ON A LIVE GRID**. If your grid is live, if you have other users, if you can't afford losing data or functionalties, only use **the latest stable release** [from WordPress directory](https://wordpress.org/plugins/w4os-opensimulator-web-interface/), or [git master branch](https://github.com/GuduleLapointe/w4os/tree/master).

This is a **highly experimental developement branch**. It contains **unfinished code** and is **not really operational** and most likely **not secure**.

That being said...

**w4os** is a ready to use WordPress interface for [OpenSimulator](http://opensimulator.org/). It provides user registration, default avatar model choice, login info, statistics and a web assets server for grids or standalone simulators.

See **Features** and **Roadmap** sections for current and upcoming functionalties.

= Experimental Features (v3 dev) =

- [x] transition from current user-linked avatars (1 wp account = 1 avatar) to dedicated post types (1 or no wp account = 1 or any number of avatars)

  - [x] fix conflicts if multiple avatar were created with the same email address in the database (e.g. if they were create from console)
  - [x] allow such shared accounts to be created by the administrator
  - [ ] allow end user to create several avatars
  - [ ] allow user to create avatars without registering a WP account
  - [x] dedicated avatars list (an avatars column remains in user list)
  - [x] better management of special accounts (default avatars, service accounts, bots...)

- [ ] complete rewrite with better use of classes

  - [ ] will allow creation of other post types (e.g. regions, parcels, events, classifieds, ...)
  - [ ] will allow better helpers integration
  - [ ] clearer settings pages

This is a huge job. The initial user-based integration has repercussions in all other features (avatar registration, profile page, registration page, synchronization, blocks, ...). As long as the transition is not finished, the developement version will not be fully operational and some features could (and probably will) be buggy or even broken.

= Features =

During the 3.x developement, the stable release (2.x) will still receive fixes and minor updates

- **Grid info**: `[gridinfo]` shortcode and admin dashboard widgets
- **Grid status**: `[gridstatus]` shortcode and admin dashboard widgets
- **Avatar creation**:
  - Opensimulator section in standard wp account page
  - `[gridprofile]` shortcode can be inserted in any custom page
  - Avatar tab in account dashboard on WooCommerce websites
- Choose avatar look from default models
- Avatar and website passwords are synchronized
- **Web profiles**: excerpt of the avatar's profile
- **Reserved names**: avatar whose first name or last name is "Default", "Test", "Admin" or the pattern used for appearance models are disallowed for public (such avatars must be created by admins from Robust console)
- **Web assets server**: the needed bridge to display in-world images on a website
- **Helpers**: currency, search, offline messages
- **OpenSimulator settings page**:
  - grid name, login uri and database connection settings
  - naming scheme of default models
  - exclude models from grid stats
- Web asset server
- Login page / Widget
- Manual and cron Grid/WP users sync
- Public avatar profile
- Auth with avatar credentials (if no matching wp account, create one)

= Paid version =

The free version from WordPress plugins directory and the [paid version](https://magiiic.com/wordpress/plugins/w4os/) are technically the same. The only difference is the way you support this plugin developement: with the free version, you join the community experience (please rate and comment), while the paid version helps us to dedicate resources to this project.

== Roadmap ==

See (<https://github.com/GuduleLapointe/w4os/>) for complete status and changelog.

= Medium term =

- [x] get grid info from <http://login.uri:8002/get_grid_info>
- [x] Web Assets server
- [x] Helpers (search, currency, map...)
- [ ] Improve avatar profile
  - Switch to allow web profile
  - Switch set in-world prefs for public profiles
  - Better basic layout
  - Web edit profile
- Admin Start / Stop regions
- Admin Create region
- Admin Use sim/grid configuration file to fetch settings if on the same host

= Long term =

- [x] Admin create users
- Admin create models (from current appearance)
- Deactivate (recommended) or delete (experimental) grid user when deleting wp account
- Choice between Robust console or database connection
- User's own regions control (create, start, stop, backup)
- WooCommerce Subscriptions integration for user-owned Regions or other pay-for services
- 2do HYPEvents project integration <https://2do.pm>
- Gudz Teleport Board project integration (based on user picks)
- separate OpenSimulator libraries and WordPress specific code, to allow easy integration in other CMS

== Frequently Asked Questions ==

= Do I need to run the website on the same server? =

No, if your web server has access to your OpenSimulator database.

= Can I use this plugin for my standalone simulator? =

Yes, it works too. Use OpenSim database credentials when requested for Robust credentials.

= Can I change an avatar name or delete an avatar? =

No. This is an OpenSimulator design limitation. Regions rely on cached data to display avatar information, and once fetched, these are often never updated. As a result, if an avatar's name (or grid URI btw) is changed, the change will not be reflected on regions already visited by this avatar (which will still show the old name), but new visited region will display the new one. This could be somewhat handled for a small standalone grid, but never in hypergrid context. There is no process to force a foreign grid to update its cache, and probably never will.

== Screenshots ==

1. Grid info and grid status examples
2. Avatar registration form in WooCommerce My Account dashboard.
3. Settings page
4. Web assets server settings

== Changelog ==

= Unreleased (3.0.1-dev.985) =
* added Robust INI file option to settings page

= 3.x-dev =

- new **allow multiple avatars** sharing the same email address or wp account
- new **avatar creation from admin area**
- new dedicated **avatar admin list**, including
  - name, WP owner, mail, uuid, last seen and (avatar) born date
  - filter per account type (regular, models, service accounts)
- new **avatar post type** (contains only data gathered from OpenSimulator, if lost or deleted, they will be automatically generated on next cron task)
- default avatar name as wp user name, fallback to randomly generated named (if chosen name is not available)
- splash page improvement (remove headers, footer and sidebars)

= 2.3.11-dev =
* fix w4os_profile_sync() fatal error when profiles are disabled
* fix minor PHP8 warnings
* fix fatal error when wp object is passed as user_id

= 2.3.10 =
- minor fixes (profile page title, profile image, profile text display)
- tested up to WP 6.1, minimum php 7.3

= 2.3.9 =
- tested up to wp 6.0.2
- added password reset link to profile page
* fix userprofile table queried even if not present when User Profiles are not enabled on robust
* fix fatal error Argument #2 ($haystack) must be of type array, bool given
- fix offline messages not forwarded by mail (opensim db not properly loaded by helpers)
- fix profile picture aspect ratio (4/3, as in viewer)
- fix fatal error in helpers for poorly encoded unicode text sources
- fix fatal errors in helpers when database is not connected
- fix #57 password not updated on grid when using password recovery in WordPdress
- fix regression in 2.3.1
- fix fatal error and warnings with popular-places shortcode
- fix fatal error if php xml-rpc is not installed, show error notice instead

= 2.3 =

- new search helper
- new offline messages helper. Messages are stored in OfflineMessageModule V2 format, so one can switch between core and external service (fix #47)
- new currency helpers
- new Popular Places block and [popular-places] shortcode
- new events parser (fetch events from 2do.pm or another HYPEvents server)
- added prebuilt binaries for opensim 0.9.1 and 0.9.2
- added currency conversion rate setting
- dropped aurora and OpenSim 0.6 support
- separate helpers settings page
- helpers migrated from old mysqli db connection method to PDO

= 2.2.10 =

- tested up to wp 5.8.3
- new web assets server
- new profile page
- new config instructions for new grid users
- new blocks support
- new grid and wordpress users sync
- new grid based authentication; if wp user exists, password is reset to grid password; if not, a new wp user is created
- new admin can create avatars for existing users
- new grid info settings are fetched from Robust server if set or localhost:8002
- new check grid info url validity (cron and manual)

- added option to replace name by avatar name in users list

- added profile image to gridprofile

- added assets permalink settings

- added states in admin pages list for known urls (from grid_info)

- added lost password and register links on login page

- added buttons to create missing pages on status dashboard

- added Born and Last Seen columns to users list

- added hop:// link to login uri

- added in-world profile link to profile page

- added Partner, Wants, Skills and RL to web profile

- removed Avatar section from WooCommerce account page until fixed

- removed W4OS Grid Info and W4OS Grid Status widgets (now available as blocks)

- responsive profile display for smartphones

- show image placeholder if profile picture not set

- added imagick to the recommended php extensions

- lighter template for profiles when loaded from the viewer

- guess new avatar name from user_login if first name and last name not provided

- replace wp avatar picture with in-world profile picture if set

- use version provided by .version if present

- More comprehensive database connection error reporting

- show a link to profile page instead of the form in profile shortcode

- fix duplicate admin notices

- fix squished profile picture

- fix avatar not created, or not created at first attempt

- fix inventory items not transferred to new avatars

- fix errors not displayed on avatar creation page

- fix avatar model not shown if default account never connected

- fix missing error messages on login page

- fix user login broken if w4os_login_page is set to profile and OpenSim database is not connected

- fix a couple of fatal errors

- fix slow assets, store cached images in upload folder to serve them directly by the web server

= 2.1 =

- added login form to gridprofile shortcode when not connected instead of login message
- added w4os-shortcode classes
- added screenshots
- fix fatal error when trying to display WooCommerce Avatar tab form in My Account
- fix localisation not loading
- shorter "Avatar" label, removed uuid in gridprofile shortcode

= 2.0.8 =

- renamed plugin as w4os - OpenSimulator Web Interface
- Now distributed via WordPress plugins directory
- renamed [w4os_profile] shortcode as [gridprofile] for consistency. w4os_profile is kept for backwards compatibility
- official git repository changed to GitHub
- use plugin dir to detect slug instead of hardcoded value
- fix localizations not loading
- fix regression, automatic updates restored. Users with version 2.0 to 2.0.3 will need to reinstall the plugin from source. Sorry.

= 1.2.12 =

- added login page link to message displayed when trying to see profile while not connected
- better css loading
- only check once if w4os db is connected
- more detailed error messages for avatar creation
- fix Database check fails if mysql is case insensitive
- fix Database connection error triggered if userprofile table is absent
- fix invalid JSON response when adding [w4os_profile] shortcode element
- fix wrong letter cases in auth table name
- fix only show profile form for current user

= 1.1.4 =

- added changelog, banners and icons to view details
- fix "Yes" and "No" translations
- fix typo in banners and icons urls, can't believe I didn't see this before...
- fixed conflict with other extensions settings pages
- changed update server library to [frogerme's WP Plugin Update Server](https://github.com/froger-me/wp-plugin-update-server)

= Full log =

For full change history see [GitHub repository](https://github.com/GuduleLapointe/w4os/commits/master).

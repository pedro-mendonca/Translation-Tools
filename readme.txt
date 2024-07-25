=== Translation Tools ===
Contributors: pedromendonca
Donate link: https://github.com/sponsors/pedro-mendonca
Tags: internationalization, localization, translation, core, language packs
Requires at least: 4.9
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.7.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Translation tools for your WordPress install.

== Description ==

### Use any Locale, with or without Language Packs ###

With core Language Packs you can easily change the language of your WordPress install.

The Language Packs used to be provided only for 100% translated Locales.

To give teams with less contributors a better chance to get WordPress released into their Locale, since [22nd February 2021](https://make.wordpress.org/polyglots/2021/02/22/wordpress-5-7-ready-to-be-translated/) the required translation status for core Language Packs to be built are as follows:

*   The [Front-end project](https://translate.wordpress.org/projects/wp/dev/) needs to be translated at least <strong>90%</strong>.
*   The [Administration project](https://translate.wordpress.org/projects/wp/dev/admin/) needs to be translated at least <strong>75%</strong>.
*   The [Network Admin](https://translate.wordpress.org/projects/wp/dev/admin/network/) and [Continent & Cities](https://translate.wordpress.org/projects/wp/dev/cc/) projects are not included in the threshold calculation.

If you need a Locale that has no Language Packs yet, this tool helps you by enabling ALL Locales on the list of the available languages.

### Compatible with plugin [Preferred Languages](https://wordpress.org/plugins/preferred-languages/) ###

The plugin [Preferred Languages](https://wordpress.org/plugins/preferred-languages/) overrides the standard languages field for site and user languages.

All the features added by Translation Tools are available for Preferred Languages users.

### Update your WordPress, Plugins or Themes translation, on demand ###

If you need to update your WordPress core, Plugins or Themes translations on demand without waiting for a language pack to be generated, this tool allows you to manually update all the needed files for the installed version, with one click, in a few seconds.

Go to "Translations" on the Updates screen and choose what you want to update.

#### All WordPress core sub-projects ####

*   Development
*   Continents & Cities
*   Administration
*   Network Admin

#### All translation files ####

*   .po (editable translation files)
*   .mo (binary translation files)
*   .l10n.php (PHP performant translation files)
*   .json (JavaScript translation files)

### WordPress Translations tests and info in Site Health ###

Check your WordPress core translations in Site Health tests page.

The Site Health debug info shows the selected site and user languages, including multiple languages from the plugin Preferred Languages.

== Frequently Asked Questions ==

= Where can I find the full list of WordPress Locales? =
Here is the complete list of [all WordPress Locales](https://make.wordpress.org/polyglots/teams/).

= Does my Locale have language packs? =
Here is a list of the [Locales WITH language packs](https://make.wordpress.org/polyglots/teams/#has-language-pack).
Here is a list of the [Locales WITH NO language packs](https://make.wordpress.org/polyglots/teams/#no-language-pack).

= My locale has language packs but the translation isn't complete =
You can force update the WordPress translation right from your Dashboard > Updates screen.
Click on the "Update WordPress Translation" and you're done.
In a few seconds all the needed translation files (.po, .mo and .json) will be generated.

= I can't use my language in WordPress, themes and plugins because the Locale has no Language Packs =
Now you can! Just install and activate this plugin to enable every possible Locales and translations.

= My desired Locale doesn't exist in the list =
If your Locale doesn't exist and you would like to request it, please [click here](https://make.wordpress.org/polyglots/handbook/translating/requesting-a-new-locale/).

= Can I also update Plugins and Themes translations? =
Yes, you can, since version 1.5.0.
On the Updates page you can choose to update translations for WordPress, Plugins, Themes, or all at once.
It will update the .po and .mo files, and also generate the needed .json files for JavaScript translations.

= This plugin generates the new .l10n.php file format of performant translations for WordPress 6.5? =
Yes, since version 1.7.0.
If you're on WordPress 6.5, this plugin will also generate the .l10n.php language files for you.

= Is this plugin compatible with the plugin Preferred Languages? =
Short answer: yes!

The plugin [Preferred Languages](https://wordpress.org/plugins/preferred-languages/) overrides the standard languages field for site and user languages.

Since version 1.6.0, this plugin is compatible with Preferred Languages 2.0.0.

All the features added by Translation Tools are available for Preferred Languages users.

= Can I help translating this plugin to my own language? =
Yes you can! If you want to translate this plugin to your language, please [click here](https://translate.wordpress.org/projects/wp-plugins/translation-tools).

= Can I contribute to this plugin? =
Sure! You are welcome to report any issues or add feature suggestions on the [GitHub repository](https://github.com/pedro-mendonca/Translation-Tools).

== Screenshots ==

1. Language settings include Locales with NO Language Packs
2. Button to Update WordPress Translation on demand
3. Translations of all core sub-projects
4. Sub-project details and files
5. Notification of themes and plugins translations updates for Locales with no Language Packs
6. Automatic theme translation update for Locale with no Language Packs
7. Site Health test recommendation for incomplete WordPress translation
8. Site Health test passed for complete WordPress translation
9. Site Health debug info for site and user WordPress translations, compatible with Preferred Languages

== Changelog ==

= 1.7.2 =
*   Tested up to WP 6.6
*   Fix PHP errors

= 1.7.1 =
*   Update Gettext to v4.8.12
*   Fix deprecation notices with PHP 8.2 or later
*   Fix typos

= 1.7.0 =
*   Tested up to WP 6.5
*   Generate .l10n.php performant translation files for WordPress 6.5

= 1.6.0 =
*   Tested up to WP 6.2
*   Tested up to Preferred Languages 2.0
*   Minimum PHP bumped to 7.4
*   Fixed incorrect list of User Languages on WP < 6.1
*   Updated admin notices, dashicons and CSS
*   Fixed basic compatibility with the plugin Preferred Languages 2.0.0, still some work to do to make the UI seamless
*   Use Composer autoload
*   Rename coding standard rulesets
*   Update dependencies

= 1.5.3 =
*   Better report messages about translation project updates
*   Increase download timeout for slow speed connections
*   New filter to customize download timeout for big translation projects on slow speed connections
*   GitHub release process optimization

= 1.5.2 =
*   Fix i18n issue
*   Increase download timeout for slow speed connections
*   GitHub release process optimization

= 1.5.1 =
*   Add missing vendor files

= 1.5.0 =
*   Update your Plugins and Themes translations! (.po, .mo and .json)
*   Update action now loads on custom update-core dedicated page
*   Code refactoring to extend the update of .po/.mo/.json files to Plugins and Themes
*   Detailed report about translation project updates
*   New filter to customize/reverse the priority of the WP.org Plugins translation projects (defaults to 'Stable' > 'Development')
*   New filter to customize the Plugins and Themes translations to update
*   New filter to disable translations download and generate .json files from your current .po files
*   New filter do disable the update of the .json files from the JavaScript translations
*   GitHub release process optimization
*   Assets folders optimization
*   Debug mode improvements

= 1.4.1 =
*   Improve notices wording about Language Packs availability
*   Fix notice links accessibility

= 1.4.0 =
*   Tested up to WP 5.8
*   More Site Health WordPress translations tests!
*   Site Health test to check if the WordPress Translation API is available from your site
*   Site Health test to report if your WordPress version is already available for translation, useful for beta testing
*   Refactor Site Health tests to allow dependency between tests. ( e.g. Only run a test if another test has the status 'good' )
*   The Site Health Translations tests now have the #WPPolyglots Pink Color for usability
*   New warning notice in the updates screen when your WordPress version is not available for translation yet, useful for beta testing
*   Force checking the updates screen ('force-check') now force updates the WordPress core translation data
*   The WordPress core translation transient data now expire in 1 hour
*   Fix issue with the 'More details' report ID on downloading the Locales translation projects

= 1.3.3 =
*   Tested up to WP 5.7
*   Fix Health Check message i18n

= 1.3.2 =
*   Tested up to WP 5.6
*   Minor code improvements

= 1.3.1 =
*   Fix issue with Locale 'en_US' on user languages in Preferred Languages settings

= 1.3.0 =
*   New Site Health WordPress translations tests and debug info!
*   Site Health tests to show the current Language Packs status for your site and user languages, for your WordPress installed version
*   Site Health debug info includes details about your site and user language
*   Site Health tests and debug info are compatible with multiple languages configured in plugin Preferred Languages
*   Inspired by [ticket #51039](https://core.trac.wordpress.org/ticket/51039) (WIP)

= 1.2.4 =
*   Fix issue with missing strings from .json files, caused by overriding .json files from Development and Administration core sub-projects, instead of merging both.
*   Based on meta [changeset #10064](https://meta.trac.wordpress.org/changeset/10064)

= 1.2.3 =
*   New filter `translation_tools_get_wp_translations_status` to customize the status of the strings to download
*   New filter `translation_tools_show_locale_codes` to append Locale codes to each language
*   New filter `translation_tools_show_locale_colors` to highlight Locales without language packs
*   New filter `translation_tools_translate_url` to override the [translate.w.org/projects/wp/](https://translate.w.org/projects/wp/) with a private GlotPress install with the same exact WP core structure
*   Filter `ttools_get_wp_translations_status` renamed to `translation_tools_get_wp_translations_status`
*   Compatible with plugin [Translation Stats](https://wordpress.org/plugins/translation-stats/) language select field
*   Code optimization

= 1.2.2 =
*   New filter `ttools_get_wp_translations_status` to customize the filtered strings to download, default is 'current'
*   Fix support for core beta versions
*   Improve core translation sub-projects data through translate.wp.org API
*   Tested up to WP 5.5
*   Minor code improvements

= 1.2.1 =
*   Fix invalid plugin header on activate

= 1.2.0 =
*   Include Locales list since [translate.wp.org Languages API](https://translate.wordpress.org/api/languages/) was disabled on meta [changeset #10056](https://meta.trac.wordpress.org/changeset/10056)
*   Compatible with plugin [Preferred Languages](https://wordpress.org/plugins/preferred-languages/) by [Pascal Birchler](https://profiles.wordpress.org/swissspidy/)
*   Update both languages configured in Site and User Language
*   Update the full set of languages configured in plugin [Preferred Languages](https://wordpress.org/plugins/preferred-languages/)

= 1.1.0 =
*   Improve usability, remove extra steps to add Locales with no Language Packs
*   Remove plugin setting to pre-add a Locale to the main list, all existent languages are now available immediately
*   Language settings now include all Locales, grouped by Language Packs status
*   Language settings are now available for site (General Settings screen) and for users (Profile and User Edit screens)
*   Rename additional available languages to "Native name [wp_locale]" format, instead of just the "wp_locale"
*   Link to update WordPress translation on the Site, Profile and User language setting description
*   Localized core update fallback to en_US for Locales with no Language Packs
*   Minor code improvements

= 1.0.1 =
*   Improve shown info when there are no settings yet
*   Improve shown info when there are no Locales missing Language Packs
*   Improve shown info when the translate.wp.org API is unreachable
*   Minor code improvements

= 1.0.0 =
*   Initial release.

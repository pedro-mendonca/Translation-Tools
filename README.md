# Translation Tools #

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/translation-tools?label=Plugin%20Version&logo=wordpress)](https://wordpress.org/plugins/translation-tools/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/stars/translation-tools?label=Plugin%20Rating&logo=wordpress)](https://wordpress.org/support/plugin/translation-tools/reviews/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/translation-tools.svg?label=Downloads&logo=wordpress)](https://wordpress.org/plugins/translation-tools/advanced/)
[![Sponsor](https://img.shields.io/badge/GitHub-🤍%20Sponsor-ea4aaa?logo=github)](https://github.com/sponsors/pedro-mendonca)

[![WordPress Plugin Required PHP Version](https://img.shields.io/wordpress/plugin/required-php/translation-tools?label=PHP%20Required&logo=php&logoColor=white)](https://wordpress.org/plugins/translation-tools/)
[![WordPress Plugin: Required WP Version](https://img.shields.io/wordpress/plugin/wp-version/translation-tools?label=WordPress%20Required&logo=wordpress)](https://wordpress.org/plugins/translation-tools/)
[![WordPress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/translation-tools.svg?label=WordPress%20Tested&logo=wordpress)](https://wordpress.org/plugins/translation-tools/)

[![Coding Standards](https://github.com/pedro-mendonca/Translation-Tools/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/pedro-mendonca/Translation-Tools/actions/workflows/coding-standards.yml)
[![Static Analysis](https://github.com/pedro-mendonca/Translation-Tools/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/pedro-mendonca/Translation-Tools/actions/workflows/static-analysis.yml)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/534909194f4446c3a865f66536ac4e03)](https://app.codacy.com/manual/pedro-mendonca/Translation-Tools?utm_source=github.com&utm_medium=referral&utm_content=pedro-mendonca/Translation-Tools&utm_campaign=Badge_Grade_Settings)

**Contributors:** pedromendonca  
**Donate link:** [github.com/sponsors/pedro-mendonca](https://github.com/sponsors/pedro-mendonca)  
**Tags:** internationalization, i18n, localization, l10n, translation, language packs  
**Requires at least:** 4.9  
**Tested up to:** 5.7  
**Requires PHP:** 5.6  
**Stable tag:** 1.3.3  
**License:** GPLv2  
**License URI:** [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)  

Translation tools for your WordPress install.

## Description ##

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

### Update your WordPress translation, on demand ###

If you need to update your WordPress core translation on demand without waiting for a language pack to be generated, this tool allows you to manually update all the needed files for the installed version, with one click, in a few seconds.  

Go to "Update WordPress Translation" on the Updates screen.  

#### All WordPress core sub-projects ####

*   Development
*   Continents & Cities
*   Administration
*   Network Admin

#### All translation files ####

*   .po (editable translation files)
*   .mo (binary translation files)
*   .json (JavaScript translation files)

### WordPress Translations tests and info in Site Health ###

Check your WordPress core translations in Site Health tests page.  

The Site Health debug info shows the selected site and user languages, including multiple languages from the plugin Preferred Languages.  

## Frequently Asked Questions ##

### Where can I find the full list of WordPress Locales? ###
Here is the complete list of [all WordPress Locales](https://make.wordpress.org/polyglots/teams/).  

### Does my Locale have language packs? ###
Here is a list of the [Locales WITH language packs](https://make.wordpress.org/polyglots/teams/#has-language-pack).  
Here is a list of the [Locales WITH NO language packs](https://make.wordpress.org/polyglots/teams/#no-language-pack).  

### My locale has language packs but the translation isn't complete ###
You can force update the WordPress translation right from your Dashboard > Updates screen.  
Click on the "Update WordPress Translation" and you're done.  
In a few seconds all the needed translation files (.po, .mo and .json) will be generated.  

### I can't use my language in WordPress, themes and plugins because the Locale has no Language Packs ###
Now you can! Just install and activate this plugin to enable every possible Locales and translations.

### My desired Locale doesn't exist in the list ###
If your Locale doesn't exist and you would like to request it, please [click here](https://make.wordpress.org/polyglots/handbook/translating/requesting-a-new-locale/).

### Is this plugin compatible with the plugin Preferred Languages? ###
Short anwser: yes!  
The plugin [Preferred Languages](https://wordpress.org/plugins/preferred-languages/) overrides the standard languages field for site and user languages.  
Since version 1.2.0, this plugin is compatible with Preferred Languages 1.6.0.  
All the features added by Translation Tools are available for Preferred Languages users.  

### Can I help translating this plugin to my own language? ###
Yes you can! If you want to translate this plugin to your language, please [click here](https://translate.wordpress.org/projects/wp-plugins/translation-tools).

### Can I contribute to this plugin? ###
Sure! You are welcome to report any issues or add feature suggestions on the [GitHub repository](https://github.com/pedro-mendonca/Translation-Tools).

## Screenshots ##

1.  Language settings include Locales with NO Language Packs
![screenshot-1](./assets/screenshot-1.png)

2.  Button to Update WordPress Translation on demand
![screenshot-2](./assets/screenshot-2.png)

3.  Translations of all core sub-projects
![screenshot-3](./assets/screenshot-3.png)

4.  Sub-project details and files
![screenshot-4](./assets/screenshot-4.png)

5.  Notification of themes and plugins translations updates for Locales with no Language Packs
![screenshot-5](./assets/screenshot-5.png)

6.  Automatic theme translation update for Locale with no Language Packs
![screenshot-6](./assets/screenshot-6.png)

7.  Site Health test recommendation for incomplete WordPress translation
![screenshot-7](./assets/screenshot-7.png)

8.  Site Health test passed for complete WordPress translation
![screenshot-8](./assets/screenshot-8.png)

9.  Site Health debug info for site and user WordPress translations, compatible with Preferred Languages
![screenshot-9](./assets/screenshot-9.png)

## Changelog ##

### 1.3.3 ###
*   Tested up to WP 5.7
*   Fix Health Check message i18n

### 1.3.2 ###
*   Tested up to WP 5.6
*   Minor code improvements

### 1.3.1 ###
*   Fix issue with Locale 'en_US' on user languages in Preferred Languages settings

### 1.3.0 ###
*   New Site Health WordPress translations tests and debug info!
*   Site Health tests to show the current Language Packs status for your site and user languages, for your WordPress installed version
*   Site Health debug info includes details about your site and user language
*   Site Health tests and debug info are compatible with multiple languages configured in plugin Preferred Languages
*   Inspired by [ticket #51039](https://core.trac.wordpress.org/ticket/51039) (WIP)

### 1.2.4 ###
*   Fix issue with missing strings from .json files, caused by overriding .json files from Development and Administration core sub-projects, instead of merging both.
*   Based on meta [changeset #10064](https://meta.trac.wordpress.org/changeset/10064)

### 1.2.3 ###
*   New filter `translation_tools_get_wp_translations_status` to customize the status of the strings to download
*   New filter `translation_tools_show_locale_codes` to append Locale codes to each language
*   New filter `translation_tools_show_locale_colors` to highlight Locales without language packs
*   New filter `translation_tools_translate_url` to override the [translate.w.org/projects/wp/](https://translate.w.org/projects/wp/) with a private GlotPress install with the same exact WP core structure
*   Filter `ttools_get_wp_translations_status` renamed to `translation_tools_get_wp_translations_status`
*   Compatible with plugin [Translation Stats](https://wordpress.org/plugins/translation-stats/) language select field
*   Code optimization

### 1.2.2 ###
*   New filter `ttools_get_wp_translations_status` to customize the filtered strings to download, default is 'current'
*   Fix support for core beta versions
*   Improve core translation sub-projects data through translate.wp.org API
*   Tested up to WP 5.5
*   Minor code improvements

### 1.2.1 ###
*   Fix invalid plugin header on activate

### 1.2.0 ###
*   Include Locales list since [translate.wp.org Languages API](https://translate.wordpress.org/api/languages/) was disabled on meta [changeset #10056](https://meta.trac.wordpress.org/changeset/10056)
*   Compatible with plugin [Preferred Languages](https://wordpress.org/plugins/preferred-languages/) by [Pascal Birchler](https://profiles.wordpress.org/swissspidy/)
*   Update both languages configured in Site and User Language
*   Update the full set of languages configured in plugin [Preferred Languages](https://wordpress.org/plugins/preferred-languages/)

### 1.1.0 ###
*   Improve usability, remove extra steps to add Locales with no Language Packs
*   Remove plugin setting to pre-add a Locale to the main list, all existent languages are now available immediately
*   Language settings now include all Locales, grouped by Language Packs status
*   Language settings are now available for site (General Settings screen) and for users (Profile and User Edit screens)
*   Rename additional available languages to "Native name \[wp_locale\]" format, instead of just the "wp_locale"
*   Link to update WordPress translation on the Site, Profile and User language setting description
*   Localized core update fallback to en_US for Locales with no Language Packs
*   Minor code improvements

### 1.0.1 ###
*   Improve shown info when there are no settings yet
*   Improve shown info when there are no Locales missing Language Packs
*   Improve shown info when the translate.wp.org API is unreachable
*   Minor code improvements

### 1.0.0 ###
*   Initial release.

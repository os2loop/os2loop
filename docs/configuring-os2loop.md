# Configuring OS2Loop

All OS2Loop settings can be found Administration › Configuration › OS2Loop
(`/admin/config/os2loop`).

After [installing OS2Loop](../README.md#production) you should run through all
the module settings below and make sure that everything is set up as needed.

The basic idea behind site configuration of OS2Loop is that all available
OS2Loop modules (and their dependencies) are installed and all have reasonable
standard configuration. Using the settings outlines below some tweaks to the
default configuration can be made.

## Technical details

Behind the scenes the configuration for all modules is stored in (and may be
imported from) the [`config/sync`](../config/sync) folder and the [`Config
Ignore`](https://www.drupal.org/project/config_ignore) module is used to prevent
overwriting site specific configurations when updating the OS2Loop core.

## OS2Loop settings

Path: `/admin/config/os2loop/settings`

General OS2Loop settings

See
[os2loop_settings/README.md](../web/profiles/custom/os2loop/modules/os2loop_settings/README.md)
for details.

## OS2Loop Member list settings

Path: `/admin/config/os2loop/os2loop_member_list/settings`

See
[os2loop_member_list/README.md](../web/profiles/custom/os2loop/modules/os2loop_member_list/README.md)
for details.

## OS2Loop Search DB settings

Path: `/admin/config/os2loop/os2loop_search_db/settings`

See
[os2loop_search_db/README.md](../web/profiles/custom/os2loop/modules/os2loop_search_db/README.md)
for details.

## OS2Loop Analytics settings

Path: `/admin/config/os2loop/os2loop_analytics/settings`

See
[os2loop_analytics/README.md](../web/profiles/custom/os2loop/modules/os2loop_analytics/README.md)
for details.

## OS2Loop Cookies settings

Path: `/admin/config/os2loop/os2loop_cookies/settings`

See
[os2loop_cookies/README.md](../web/profiles/custom/os2loop/modules/os2loop_cookies/README.md)
for details.

## OS2Loop Flag content settings

Path: `/admin/config/os2loop/os2loop_flag_content/settings`

See
[os2loop_flag_content/README.md](../web/profiles/custom/os2loop/modules/os2loop_flag_content/README.md)
for details.

## OS2Loop Mail notifications settings

Path: `/admin/config/os2loop/os2loop_mail_notifications/settings`

See
[os2loop_mail_notifications/README.md](../web/profiles/custom/os2loop/modules/os2loop_mail_notifications/README.md)
for details.

## OS2Loop Question settings

Path: `/admin/config/os2loop/os2loop_question/settings`

See
[os2loop_question/README.md](../web/profiles/custom/os2loop/modules/os2loop_question/README.md)
for details.

## OS2Loop Share With A Friend settings

Path: `/admin/config/os2loop/os2loop_share_with_a_friend/settings`

See
[os2loop_share_with_a_friend/README.md](../web/profiles/custom/os2loop/modules/os2loop_share_with_a_friend/README.md)
for details.

## OS2Loop Subscriptions settings

Path: `/admin/config/os2loop/os2loop_subscriptions/settings`

See
[os2loop_subscriptions/README.md](../web/profiles/custom/os2loop/modules/os2loop_subscriptions/README.md)
for details.

## OS2Loop user login settings

Path: `/admin/config/os2loop/os2loop_user_login/settings`

See
[os2loop_user_login/README.md](../web/profiles/custom/os2loop/modules/os2loop_user_login/README.md)
for details.

## Modules requiring configuration in `settings.local.php`

Some modules require configuration in `settings.local.php`:

See
[os2loop_documents/README.md](../web/profiles/custom/os2loop/modules/os2loop_documents/README.md)
for details.

See
[os2loop_user_login/README.md](../web/profiles/custom/os2loop/modules/os2loop_user_login/README.md)
for details.
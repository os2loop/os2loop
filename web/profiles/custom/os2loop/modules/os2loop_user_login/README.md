# OS2Loop user login

Log in via OpenID Connect and SAML.

Go to Administration › Configuration › OS2Loop › OS2Loop user login settings
(`/admin/config/os2loop/os2loop_user_login/settings`) to enable login methods.

## OpenID Connect

The modules [OpenID Connect](https://www.drupal.org/project/openid_connect) and
[OpenID Connect Microsoft Azure Active Directory
client](https://www.drupal.org/project/openid_connect_windows_aad) are used for
OpenID Connect login. *Note*: Eventhough it's called “OpenID Connect Microsoft
Azure Active Directory client” it also work with other OpenID Connect identity
providers.

In the default configuration both login methods assume that the identitity
provider returns a `name` claim which is used as the Drupal user name and that a
`groups` claim is a list of groups that can be mapped to Drupal roles.

Any changes to the default configuration can be made in `settings.local.php` as
shown in the following sections.

### Required claims

The following claims are required to make signing in work:

| Claim    | Description                                                                                  |
|----------|----------------------------------------------------------------------------------------------|
| `upn`    | Unique user id, e.g. email address                                                           |
| `name`   | Drupal user name                                                                             |
| `email`  | Drupal user mail                                                                             |
| `groups` | Mapped to Drupal user roles (see [Groups to roles mapping](#groups-to-roles-mapping) below). |

#### Mapping claims

If needed you can map claims from the IdP response to match the claims required
for login (cf. above).

For example to use the `sub` claim as `preferred_username` (which will be used
as Drupal user name) and `upn` as `email`, add this to `settings.local.php`:

```php
// Map IdP claim `sub` to `preferred_username`
$config['os2loop_user_login.settings']['claims_mapping']['preferred_username'] = 'sub';
// Map IdP claim `upn` to `email`
$config['os2loop_user_login.settings']['claims_mapping']['email'] = 'upn';
```

**Note**: Mapping claims will never overwrite an existing claim from the IdP,
i.e. if `email` is aldready set it will no be overwritten (with the value of
`upn`).

### Claim to field mapping

As mentioned above, the default configuration maps the `name` claim to the
Drupal user name and the `email` claim is mapped to the user mail. Further
claims can be mapped and by default the claims `family_name` and `given_name`
are mapped to the user's name fields (`os2loop_user_family_name` and
`os2loop_user_given_name` respectively). See
[`openid_connect.settings.yml`](../../../../../../config/sync/openid_connect.settings.yml)
for details.

Changes and additions to the default field mapping can be made in
`settings.local.php`:

```php
// web/sites/*/settings.local.php
// Use the department claim as user's Place/Department
$config['openid_connect.settings']['userinfo_mappings']['os2loop_user_place'] = 'department';
```

### IdP configuration

Your identity provider must allow `https://«OS2Loop url»/openid-connect/generic`
as a valid return url.

```php
// web/sites/*/settings.local.php
$config['openid_connect.client.generic']['settings']['client_id'] = …; // Get this from your IdP provider
$config['openid_connect.client.generic']['settings']['client_secret'] = …; // Get this from your IdP provider
$config['openid_connect.client.generic']['settings']['authorization_endpoint'] = …; // Get this from your OpenID Connect Discovery endpoint
$config['openid_connect.client.generic']['settings']['token_endpoint'] = …; // Get this from your OpenID Connect Discovery endpoint
// Optional
$config['openid_connect.client.generic']['settings']['end_session_endpoint'] = …; // Get this from your OpenID Connect Discovery endpoint
```

Check your overwrites by running

```sh
vendor/bin/drush config:get --include-overridden openid_connect.client.generic
```

#### Groups to roles mapping

[The default configuration groups to roles
mapping](../../../../../../config/sync/config/sync/openid_connect.settings.yml)
maps groups (in the `groups` claim which must be a list of names) as follows:

| Drupal role                             | group                      |
|-----------------------------------------|----------------------------|
| os2loop_user_administrator              | administrator              |
| os2loop_user_document_author            | document_author            |
| os2loop_user_document_collection_editor | document_collection_editor |
| os2loop_user_documentation_coordinator  | documentation_coordinator  |
| os2loop_user_external_sources_editor    | external_sources_editor    |
| os2loop_user_manager                    | manager                    |
| os2loop_user_post_author                | post_author                |
| os2loop_user_read_only                  | read_only                  |
| os2loop_user_user_administrator         | user_administrator         |

Any changes can be made in `settings.local.php`, e.g

```php
// web/sites/*/settings.local.php
$config['openid_connect.settings']['role_mappings']['os2loop_user_administrator'] = ['Loop-Admin'];
$config['openid_connect.settings']['role_mappings']['os2loop_user_manager'] = ['Loop-Manager'];
```

Check your overwrites by running

```sh
vendor/bin/drush config:get --include-overridden openid_connect.settings
```

## SAML

The [SAML Authentication](https://www.drupal.org/project/samlauth) module is
used for SAML authentication (!).

```php
// web/sites/*/settings.local.php
$config['samlauth.authentication']['sp_entity_id'] = 'os2loop;
$config['samlauth.authentication']['wsp_name_id_format'] = '';
// Folder containing `certs` folder with files `sp.{crt,key}`.
$config['samlauth.authentication']['sp_cert_folder'] = __DIR__.'/../../..';
$config['samlauth.authentication']['idp_entity_id'] = …; // Get this from you IdP metadata.
$config['samlauth.authentication']['idp_single_sign_on_service'] = …; // Get this from you IdP metadata.
$config['samlauth.authentication']['idp_x509_certificate'] = …; // Get this from you IdP metadata.
$config['samlauth.authentication']['unique_id_attribute'] = 'upn';
$config['samlauth.authentication']['create_users'] = TRUE;
$config['samlauth.authentication']['user_name_attribute'] = 'name';
$config['samlauth.authentication']['user_mail_attribute'] = 'mail';
$config['samlauth.authentication']['sp_name_id_format'] = 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress';
```

### Translations

On the login form, the OpenID Connect login buttons will show a generic “Log in
with «login provider name»” text which doesn’t make much sense to users. Under
the reasonable assumption that only one OpenID Connect login method is enabled
we can remedy this by adding a translating that makes sense to the user.

The translation can be made on `/admin/config/regional/translate` or in
`settings.local.php`:

```php
// web/sites/*/settings.local.php
$settings['locale_custom_strings_en'][''] = [
   'Log in with @client_title' => 'Log in with OpenID Connect (employee)',
];

$settings['locale_custom_strings_da'][''] = [
   'Log in with @client_title' => 'Log ind med OpenID Connect (medarbejderlogin)',
];
```

Note that we translate `Log in with @client_title` to a fixed text that doesn't
use the `@client_title` placeholder, and that's why this will only work with just
a single active OpenID login method.

# OS2Loop member list

Adds

* Two fields to the user profile
  * Show user on external members list
  * Show user on internal members list

## Settings

Go to Configuration > OS2Loop > OS2Loop Member list settings
(`/admin/config/os2loop/os2loop_member_list/settings`) to configure the member
list settings.

If enabled, you can add a link to the member list, `/contacts`, in the main
navigation menu (Structure > Menus > Main navigation
[`/admin/structure/menu/manage/main`]) or put a link on a section page, say.

## Development

### Alter query function

Changes the view query depending on whether the user is auth or anonymous.

### Form alter function

Makes the external list checkbox disabled, if the internal list checkbox is not checked.

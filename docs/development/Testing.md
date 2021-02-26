# Testing

## UI tests

We use [Cypress](https://www.cypress.io/) to test the UI of all OS2Loop modules
and we utilize [Cypress for
Drupal](https://github.com/AmazeeLabs/cypress/blob/8.x-1.x/README.md) to make it
easy to create and run UI tests.

### Testing a module

To add Cypress tests to a module, `os2loop_page`, say, add a sub-module called
`os2loop_page_tests_cypress` under the `os2loop_page` module:

```sh
web/profiles/custom/os2loop/modules/os2loop_page
├── modules
│   └── os2loop_page_tests_cypress
```

# LOCAL INSTALLATION GUIDE

## LINKS

- [Novataris' repository](https://bitbucket.org/novataris/loop/src/main/)
- [Public Github repository](https://github.com/os2loop/os2loop)

## DEFINITIONS

- **Base directory**: wherever you have cloned the repository (in my case, `C:\Code\loop`).
- **Theme directory**: the location of the custom os2loop theme (`<Base directory>\web\profiles\custom\os2loop\themes\os2loop_theme`

## STEPS

1. Install Docker
2. Run `docker-compose up --detach` in the **base directory**.
    1. If the install fails, make sure you are on the development branch.
    2. `git status` will report which branch you are on e.g. `On branch main`.
    3. Type `git checkout development` to switch to development.
3. Run `docker ps` to confirm everything is running.
    1. Note the port for `os2loop_ngingx_1`. This is where your site will spin up (ex. `0.0.0.0:53960` => `http://localhost:53960`).
4. Run `docker exec -it os2loop-phpfpm-1 bash` to open terminal on `phpfpm`.
    1. Run `composer install`.
    2. Run `mkdir web/sites/default/files/`.
    3. Run `chmod 777 web/sites/default/files/` to allow access to folder (workaround for install issue).
    4. Run `vendor/bin/drush --yes site:install os2loop --existing-config` and note down username and password.
    5. Run `vendor/bin/drush --yes --uri="http://0.0.0.0:<port from step 3.1>" user:login` and note down admin-sign-in URL (replace `0.0.0.0` with `localhost`).
    6. Run `vendor/bin/drush --yes pm:enable $(find web/profiles/custom/os2loop/modules/ -type f -name 'os2loop_*_fixtures.info.yml' -exec basename -s .info.yml {} \;)`
	to enable fixtures modules.
    7. Run `vendor/bin/drush --yes pm:uninstall entity_reference_integrity_enforce`.
    8. Run `vendor/bin/drush --yes content-fixtures:load` to load the fixtures.
    9. Run `vendor/bin/drush --yes pm:uninstall content_fixtures` to uninstall content_fixtures module.
    10. Run `vendor/bin/drush --yes pm:enable entity_reference_integrity_enforce`.
    11. Run `(cd web && ../vendor/bin/drush locale:import --type=customized --override=none da profiles/custom/os2loop/translations/translations.da.po)` to load translations.
    12. Run `exit` to return to command prompt/PowerShell.
5. Run `cd .\web\profiles\custom\os2loop\themes\os2loop_theme\` to switch to **theme directory**.
6. Run `yarn install`.
7. Run `yarn build`.
8. Run `yarn watch`.
9. Open browser and go to `http://localhost:<port from step 3.1>` and view your handiwork. :)

## Final steps

Pre-commit hooks didn't work out of the box. For whatever reason, it didn't copy the `pre-commit` file correctly, so you may need to grab it from `\scripts` and place it into `.git\hooks`.

### Install
- [XAMPP](https://www.apachefriends.org/download.html)
- [Composer](https://getcomposer.org/download/) 

for the pre-commit hook to work, install them in the written order. Remember to add PHP to your [windows environment path](https://dinocajic.medium.com/add-xampp-php-to-environment-variables-in-windows-10-af20a765b0ce).

- [Twigcs](https://github.com/friendsoftwig/twigcs) by writing the following command in your powershell/cmd:

    ```
    composer global require friendsoftwig/twigcs
    ```

Remember to reopen your powershell/cmd if you haven't since you added to your enviroment path. After installing twigcs

- [Prettier](https://prettier.io/docs/en/install.html) by writing the following in your powershell/cmd:

    ```
    yarn global add prettier
    ```

## NOTES

It was a bit of a struggle to get this working, and in the process I installed _a bunch_ of other stuff (`PHP`, `composer`, `symphony`, etc.). I don't _think_ \
any of these are necessary, but I could be wrong.

Make sure you don't have the latest version of `node` (v17). This causes issues with building, as the standard hasing algorithm is no longer supported...

I also had issues with `prettier` not installing, despite it being in the `devDependencies`. I got around it by installing it globally (`npm install -g prettier`).

To access a normal login, append your localhost path with `/user/login#drupal-login`.
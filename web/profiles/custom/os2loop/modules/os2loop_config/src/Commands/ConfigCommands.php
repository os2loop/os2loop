<?php

namespace Drupal\os2loop_config\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * A drush command file.
 *
 * @package Drupal\os2loop_config\Commands
 */
class ConfigCommands extends DrushCommands {
  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ModuleHandlerInterface $moduleHandler, FileSystemInterface $fileSystem) {
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
    $this->fileSystem = $fileSystem;
  }

  /**
   * Drush command that adds module dependencies on config.
   *
   * @param array $modules
   *   The module names.
   * @param array $options
   *   The options.
   *
   * @see https://www.drupal.org/node/2087879#s-example:~:text=The%20dependencies%20and%20enforced%20keys%20ensure,removed%20when%20the%20module%20is%20uninstalled
   *
   * @option remove-uuid
   *   Remove uuid from config.
   *
   * @command os2loop:config:add-module-config-dependencies
   * @usage os2loop:config:add-module-config-dependencies module another_module
   */
  public function addModuleConfigDependencies(array $modules, array $options = ['remove-uuid' => FALSE]) {
    foreach ($modules as $module) {
      if (!$this->moduleHandler->moduleExists($module)) {
        throw new RuntimeException(sprintf('Invalid module: %s', $module));
      }

      $this->output()->writeln($module);

      $names = array_values(array_filter($this->configFactory->listAll(), static function ($name) use ($module) {
        return preg_match('/[._]' . preg_quote($module, '/') . '/', $name);
      }));

      foreach ($names as $name) {
        $this->output()->writeln($name);
        $config = $this->configFactory->getEditable($name);

        // Config::merge does merge correctly so we do it ourselves.
        $dependencies = $config->get('dependencies') ?? [];

        if (!isset($dependencies['module'])) {
          $dependencies['module'] = [];
        }
        if (!isset($dependencies['enforced'])) {
          $dependencies['enforced'] = [];
        }
        if (!isset($dependencies['enforced']['module'])) {
          $dependencies['enforced']['module'] = [];
        }

        $dependencies['module'][] = $module;
        $dependencies['enforced']['module'][] = $module;

        $dependencies['module'] = array_unique($dependencies['module']);
        sort($dependencies['module']);

        $dependencies['enforced']['module'] = array_unique($dependencies['enforced']['module']);
        sort($dependencies['enforced']['module']);

        $config->set('dependencies', $dependencies);

        if ($options['remove-uuid']) {
          $config->clear('uuid');
        }

        $config->save();
      }
    }
  }

  /**
   * Drush command that moves config info config/install folder in a module.
   *
   * @param array $modules
   *   The module names.
   *
   * @command os2loop:config:move-module-config
   * @usage os2loop:config:move-module-config module another_module
   */
  public function moveModuleConfig(array $modules) {
    foreach ($modules as $module) {
      if (!$this->moduleHandler->moduleExists($module)) {
        throw new RuntimeException(sprintf('Invalid module: %s', $module));
      }

      $this->output()->writeln($module);

      $names = array_values(array_filter($this->configFactory->listAll(), static function ($name) use ($module) {
        return preg_match('/[._]' . preg_quote($module, '/') . '/', $name);
      }));

      $configPath = realpath(DRUPAL_ROOT . '/' . Settings::get('config_sync_directory'));
      $modulePath = $this->moduleHandler->getModule($module)->getPath();
      $moduleConfigPath = $modulePath . '/config/install';
      if (!is_dir($moduleConfigPath)) {
        $this->fileSystem->mkdir($moduleConfigPath, 0755, TRUE);
      }

      foreach ($names as $name) {
        $this->output()->writeln($name);

        $filename = $name . '.yml';
        $source = $configPath . '/' . $filename;
        $destination = $moduleConfigPath . '/' . $filename;

        if (!file_exists($source)) {
          $this->output()->writeln(sprintf('Source file %s does not exist', $source));
          continue;
        }

        $this->fileSystem->move($source, $destination, FileSystemInterface::EXISTS_REPLACE);
        $this->output()->writeln(sprintf('%s -> %s', $source, $destination));
      }
    }
  }

}

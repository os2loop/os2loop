<?php

namespace Drupal\os2loop_lists\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\os2loop_lists\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block with profession related content.
 *
 * @Block(
 *   id = "os2loop_list_user_profession_content",
 *   admin_label = @Translation("Content related to user profession"),
 * )
 */
final class Os2loopListsProfessionBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The helper service.
   *
   * @var \Drupal\os2loop_lists\Helper\Helper
   */
  protected $helper;

  /**
   * Block constructor.
   *
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Block plugin id.
   * @param mixed $plugin_definition
   *   Block plugin definition.
   * @param \Drupal\os2loop_lists\Helper\Helper $helper
   *   Helper service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Helper $helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->helper = $helper;
  }

  /**
   * Create block.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container interface.
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Block plugin id.
   * @param mixed $plugin_definition
   *   Block plugin definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): Os2loopListsProfessionBlock {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get(Helper::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = $this->helper->getContentByUserProfession();

    return [
      '#type' => 'markup',
      '#theme' => 'os2loop_lists_profession',
      '#data' => $data,
    ];
  }

}

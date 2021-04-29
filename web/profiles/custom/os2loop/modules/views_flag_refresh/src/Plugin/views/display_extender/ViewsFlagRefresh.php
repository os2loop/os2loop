<?php

namespace Drupal\views_flag_refresh\Plugin\views\display_extender;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\flag\FlagServiceInterface;

/**
 * Views Flag Refresh display extender plugin.
 *
 * @ingroup views_display_extender_plugins
 *
 * @ViewsDisplayExtender(
 *   id = "views_flag_refresh",
 *   title = @Translation("Refresh view by Flag"),
 *   help = @Translation("Refresh view by Flag settings for this view."),
 *   no_ui = FALSE
 * )
 */
class ViewsFlagRefresh extends DisplayExtenderPluginBase {

  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * Constructs the plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\flag\FlagServiceInterface $flag_service
   *   The flag service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FlagServiceInterface $flag_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->flagService = $flag_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('flag')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['flags'] = ['default' => []];
    $options['noscrolltop'] = ['default' => 0];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    if ($form_state->get('section') == 'views_flag_refresh') {
      $form['#title'] .= $this->t('Refresh view by Flag');

      $form['flags'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Refresh display on flags'),
        '#options' => $this->getFlagsOptions(),
        '#default_value' => $this->options['flags'],
        '#description' => $this->t('Refreshes the display via AJAX whenever a user clicks one of the selected flags. This will only take effect if the <em>Use AJAX</em> option is set to <em>Yes</em>.'),
      ];
      $form['noscrolltop'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Disable scroll to top of this view.'),
        '#default_value' => $this->options['noscrolltop'],
        '#description' => $this->t('Check if you want disable scroll to the top of this view after AJAX update.'),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    if ($form_state->get('section') == 'views_flag_refresh') {
      $this->options['flags'] = array_filter($form_state->getValue('flags'));
      $this->options['noscrolltop'] = $form_state->getValue('noscrolltop');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    $flags = array_intersect_key($this->getFlagsOptions(), $this->options['flags']);
    $options['views_flag_refresh'] = [
      'category' => 'other',
      'title' => $this->t('Flag refresh'),
      'value' => $flags ? implode(', ', $flags) : $this->t('None'),
    ];
  }

  /**
   * Returns available flags options.
   *
   * @return array
   *   Available flags options.
   */
  protected function getFlagsOptions() {
    $options = [];
    foreach ($this->flagService->getAllFlags() as $flag_id => $flag) {
      $options[$flag_id] = $flag->label();
    }
    return $options;
  }

}

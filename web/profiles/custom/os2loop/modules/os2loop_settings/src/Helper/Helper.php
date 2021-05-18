<?php

namespace Drupal\os2loop_settings\Helper;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeForm;
use Drupal\node\NodeInterface;
use Drupal\os2loop_settings\Settings;

/**
 * The helper.
 */
class Helper {
  use StringTranslationTrait;

  /**
   * The settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  private $settings;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * The constructor.
   */
  public function __construct(Settings $settings, MessengerInterface $messenger) {
    $this->settings = $settings;
    $this->messenger = $messenger;
  }

  /**
   * Implements hook_entity_create_access().
   *
   * Checks if a node type is enabled (cf self::contentTypeAccess()).
   */
  public function entityCreateAccess(AccountInterface $account, array $context, $entity_bundle): AccessResult {
    if ('node' === $context['entity_type_id']) {
      return $this->contentTypeAccess($entity_bundle);
    }

    return AccessResult::neutral();
  }

  /**
   * Implements hook_node_access().
   *
   * Checks if a node type is enabled (cf self::contentTypeAccess()).
   */
  public function nodeAccess(NodeInterface $node, $op, AccountInterface $account): AccessResult {
    // Our custom checks should only be applied when not viewing nodes, so we
    // let others decide if a node can be viewed.
    if ('view' === $op) {
      return AccessResult::neutral();
    }

    return $this->contentTypeAccess($node->bundle());
  }

  /**
   * Implements hook_form_alter().
   *
   * Hides field for disabled taxonomies from a form.
   */
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if ($form_state->getFormObject() instanceof NodeForm) {
      $entity = $form_state->getFormObject()->getEntity();

      if (!$this->settings->isContentTypeEnabled($entity->bundle())) {
        $this->messenger->addWarning($this->t("You're editing content of a type that is disabled"));
      }
    }

    $this->hideTaxonomyVocabularies($form);
  }

  /**
   * Implements hook_preprocess_node().
   *
   * Hides disabled taxonomies from a node.
   */
  public function preprocessNode(array &$variables) {
    if (isset($variables['content'])) {
      $this->hideTaxonomyVocabularies($variables['content']);
    }
    if (isset($variables['elements'])) {
      $this->hideTaxonomyVocabularies($variables['elements']);
    }
  }

  /**
   * Check access to a node type.
   */
  private function contentTypeAccess(string $type) {
    $enabledContentTypes = $this->settings->getEnabledContentTypes();

    if (!isset($enabledContentTypes[$type])) {
      return AccessResult::forbidden(sprintf('Content type %s is not enabled', $type));
    }

    return AccessResult::neutral();
  }

  /**
   * Hide taxonomy vocabularies.
   *
   * @param array $element
   *   The element.
   */
  private function hideTaxonomyVocabularies(array &$element) {
    // Vocabulary name => Field name.
    $vocabularyFields = [
      'os2loop_subject' => 'os2loop_shared_subject',
      'os2loop_tag' => 'os2loop_shared_tags',
      'os2loop_profession' => 'os2loop_shared_profession',
    ];

    foreach ($vocabularyFields as $vocabularyName => $fieldName) {
      if (isset($element[$fieldName]) && !$this->settings->isTaxonomyVocabularyEnabled($vocabularyName)) {
        unset($element[$fieldName]);
      }
    }
  }

}

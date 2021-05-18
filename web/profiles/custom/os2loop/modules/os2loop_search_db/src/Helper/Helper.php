<?php

namespace Drupal\os2loop_search_db\Helper;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os2loop_search_db\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Drupal\search_api\Query\Condition;
use Drupal\search_api\Query\ConditionGroupInterface;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api_autocomplete\Suggestion\Suggestion;

/**
 * Search Api Autocomplete Helper.
 *
 * Hook implementations for search_api_autocomplete.
 */
class Helper {
  use StringTranslationTrait;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The OS2Loop settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  private $settings;

  /**
   * Constructor.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
  }

  /**
   * Implements hook_block_access().
   *
   * Hides disabled blocks.
   */
  public function blockAccess(Block $block, $operation, AccountInterface $account) {
    if ('view' === $operation) {

      $filterOnContentType = $this->config->get('filter_content_type');
      if (!$filterOnContentType && 'os2loop_search_db_content_type' === $block->id()) {
        return AccessResult::forbidden();
      }

      // Keep only taxonomy vocabularies that are also as search filters.
      $enabledTaxonomyVocabularies = array_intersect(
        array_keys($this->settings->getEnabledTaxonomyVocabularies()),
        $this->config->get('filter_taxonomy_vocabulary') ?: []
      );

      // Vocabulary name => Block id.
      $vocabularyBlocks = [
        'os2loop_subject' => 'os2loop_search_db_subject',
        'os2loop_tag' => 'os2loop_search_db_tags',
        'os2loop_profession' => 'os2loop_search_db_profession',
      ];
      foreach ($vocabularyBlocks as $vocabularyName => $blockId) {
        if ($blockId === $block->id() && !isset($enabledTaxonomyVocabularies[$vocabularyName])) {
          return AccessResult::forbidden();
        }
      }
    }

    return AccessResult::neutral();
  }

  /**
   * Implements hook_search_api_autocomplete_suggestions_alter().
   *
   * Adds a suggestion, "See all results for …", linking to the full search
   * result for the current query.
   */
  public function alterSuggestions(array &$suggestions, array $alter_params) {
    /** @var string $user_input */
    $user_input = $alter_params['user_input'];

    $suggestion = (new Suggestion())
      ->setSuggestedKeys($user_input)
      ->setLabel($this->t('See all results for %user_input', ['%user_input' => $user_input]));

    $suggestions[] = $suggestion;
  }

  /**
   * Implements hook_search_api_query_alter().
   *
   * Alters query to include comments when filtering select content types.
   */
  public function alterSearchApiQuery(QueryInterface $query) {
    if ('os2loop_search_db_index' === $query->getIndex()->id()) {
      $typeGroups = $this->getConditionGroupsWithTag($query->getConditionGroup()->getConditions(), 'facet:type');
      $contentTypeGroups = $this->getContentTypeGroups();
      foreach ($typeGroups as $group) {
        // Get the content types in the facet filter.
        $types = array_filter(
            array_map(static function (Condition $condition) {
              return 'type' === $condition->getField() ? $condition->getValue() : NULL;
            }, $group->getConditions())
          );
        foreach ($types as $type) {
          switch ($type) {
            case 'os2loop_post':
              $group->addCondition('comment_type', 'os2loop_post_comment', '=');
              break;

            case 'os2loop_question':
              $group->addCondition('comment_type', 'os2loop_question_answer', '=');
              break;
          }

          if (isset($contentTypeGroups[$type])) {
            // Include other content types.
            $group->addCondition('type', $contentTypeGroups[$type], 'IN');
          }
        }
      }
    }
  }

  /**
   * Get condition groups with a specific tag in a query.
   *
   * @param \Drupal\search_api\Query\ConditionGroupInterface[] $conditions
   *   The conditions.
   * @param string $tag
   *   The tag to find.
   *
   * @return \Drupal\search_api\Query\ConditionGroupInterface[]
   *   Conditions with the tag.
   */
  private function getConditionGroupsWithTag(array $conditions, string $tag) {
    $groups = [];
    foreach ($conditions as $condition) {
      if ($condition instanceof ConditionGroupInterface && $condition->hasTag($tag)) {
        $groups[] = $condition;
      }
    }

    return $groups;
  }

  /**
   * Get groups of content types.
   *
   * Content types can be grouped under a single content type and handled os one
   * in facet filters.
   *
   * @return array
   *   The groups.
   */
  public function getContentTypeGroups(): array {
    return $this->config->get('content_type_groups') ??
      [
        // "Document" includes "Collection" and "External".
        'os2loop_documents_document' => [
          'os2loop_documents_collection',
          'os2loop_external',
        ],
      ];
  }

}

<?php

namespace Drupal\os2loop_search_db\Helper;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\os2loop_search_db\Form\SettingsForm;
use Drupal\os2loop_settings\Settings;
use Drupal\search_api\Query\Condition;
use Drupal\search_api\Query\ConditionGroupInterface;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api_autocomplete\Suggestion\Suggestion;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $commentStorage;

  /**
   * Constructor.
   */
  public function __construct(Settings $settings, RequestStack $requestStack, EntityTypeManagerInterface $entityTypeManager) {
    $this->settings = $settings;
    $this->config = $settings->getConfig(SettingsForm::SETTINGS_NAME);
    $this->requestStack = $requestStack;
    $this->entityTypeManager = $entityTypeManager;
    $this->commentStorage = $this->entityTypeManager->getStorage('comment');
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
        'os2loop_category' => 'os2loop_search_db_category',
        'os2loop_subject' => 'os2loop_search_db_subject',
        'os2loop_tag' => 'os2loop_search_db_tags',
        'os2loop_profession' => 'os2loop_search_db_profession',
      ];

      foreach ($vocabularyBlocks as $vocabularyName => $blockId) {
        if ($blockId === $block->id() && !in_array($vocabularyName, $enabledTaxonomyVocabularies)) {
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

  /**
   * Implements hook_form_alter().
   */
  public function formAlter(array &$form, FormStateInterface $form_state, $form_id) {
    if ('views_exposed_form' === $form_id
      && 'views-exposed-form-os2loop-search-db-page-search' === ($form['#id'] ?? NULL)) {
      // Add facet filter query to form to keep the filters when submitting
      // search form.
      $request = $this->requestStack->getCurrentRequest();
      $facetFilterName = 'f';
      $facetFilters = $request->get($facetFilterName);
      if (!empty($facetFilters) && is_array($facetFilters)) {
        $form[$facetFilterName] = [
          '#tree' => TRUE,
        ];
        foreach ($facetFilters as $key => $value) {
          $form[$facetFilterName][$key] = [
            '#type' => 'hidden',
            '#value' => $value,
          ];
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_node().
   */
  public function preprocessNode(&$variables) {
    if ('search_result' === $variables['view_mode']) {
      $bundle = $variables['node']->bundle();
      switch ($bundle) {
        case 'os2loop_question':
          $comment = $this->getSearchedComment($variables['node'], 'os2loop_question_answer');
          $variables['searchedComment'] = $comment;
          break;

        case 'os2loop_post':
          $comment = $this->getSearchedComment($variables['node'], 'os2loop_post_comment');
          $variables['searchedComment'] = $comment;
          break;
      }

      // Disable search result cache to make processors, e.g. highlight, work as
      // expected (cf. https://drupal.stackexchange.com/a/289180).
      $variables['#cache']['max-age'] = 0;
    }
  }

  /**
   * Implements hook_preprocess().
   */
  public function preprocessView(&$variables) {
    $request = $this->requestStack->getCurrentRequest();
    $parameters = $request->query->all();
    if ('os2loop_search_db' == $variables['id']) {
      // Create links for sorting.
      $sortLinks = [
        'sortDefault' => [
          'label' => $this->t('Best match'),
          'requestAlters' => [
            'sort_by' => 'search_api_relevance',
            'sort_order' => 'DESC',
          ],
        ],
        'sortNewest' => [
          'label' => $this->t('Newest first'),
          'requestAlters' => [
            'sort_by' => 'created',
            'sort_order' => 'DESC',
          ],
        ],
        'sortOldest' => [
          'label' => $this->t('Oldest first'),
          'requestAlters' => [
            'sort_by' => 'created',
            'sort_order' => 'ASC',
          ],
        ],
        'sortAlphabetic' => [
          'label' => $this->t('Alphabetic'),
          'requestAlters' => [
            'sort_by' => 'title',
            'sort_order' => 'ASC',
          ],
        ],
      ];

      $variables['sortLinks'] = [];
      foreach ($sortLinks as $type => $values) {
        if (empty($parameters)) {
          // Set default active.
          $variables['sortLinks']['sortDefault']['active'] = TRUE;
        }
        else {
          if (empty(array_diff_assoc($values['requestAlters'], $parameters))) {
            $variables['sortLinks'][$type]['active'] = TRUE;
          }
          else {
            $variables['sortLinks'][$type]['active'] = FALSE;
          }
        }
        $newRequest = array_merge($parameters, $values['requestAlters']);
        $variables['sortLinks'][$type]['url'] = Url::fromRoute('<current>', $newRequest)->toString();
        $variables['sortLinks'][$type]['label'] = $values['label'];
      }
    }
  }

  /**
   * Return the first comment that hits the search string.
   *
   * @param \Drupal\node\Entity\Node $node
   *   A drupal node.
   * @param string $commentField
   *   The name of the comment text field on a comment entity.
   *
   * @return array|null
   *   An array containing a comment and text with marked search string.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getSearchedComment(Node $node, string $commentField) {
    $request = $this->requestStack->getCurrentRequest();
    $searchString = $request->query->get('search_api_fulltext');

    $cids = $this->entityTypeManager
      ->getStorage('comment')
      ->getQuery('AND')
      ->condition('entity_id', $node->id())
      ->condition('entity_type', 'node')
      ->execute();
    foreach ($cids as $cid) {
      $comment = $this->commentStorage->load($cid);
      /** @var \Drupal\comment\CommentInterface $comment */
      $commentText = strip_tags($comment->get($commentField)->getValue()[0]['value']);
      if ($searchString) {
        $hit = stristr($commentText, $searchString);
        if (FALSE !== $hit) {
          $hit = $this->truncateText($hit);
          return [
            'comment' => $comment,
            'comment_text' => '...' . str_ireplace($searchString, '<strong>' . $searchString . '</strong>', $hit),
          ];
        }
      }

    }
    return NULL;
  }

  /**
   * Truncate a text and if so add ellipsis.
   *
   * @param string $text
   *   The text.
   *
   * @return string
   *   The truncated text.
   */
  private function truncateText($text): string {
    $newText = substr($text, 0, 250);
    if (strlen($newText) < strlen($text)) {
      $newText = $newText . ' ...';
    }
    return $newText;
  }

}

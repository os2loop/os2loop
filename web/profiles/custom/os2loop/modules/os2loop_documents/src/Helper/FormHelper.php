<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\MainContent\AjaxRenderer;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form helper.
 */
class FormHelper {
  // @see https://www.drupal.org/project/media_library_form_element/issues/3155313#comment-13859469.
  use DependencySerializationTrait;
  use StringTranslationTrait;

  private const DOCUMENTS = 'os2loop_documents_documents';
  private const DOCUMENTS_TREE = 'os2loop_documents_tree';
  private const DOCUMENTS_MESSAGE = 'documents_message';

  /**
   * The collection helper.
   *
   * @var CollectionHelper
   */
  private $collectionHelper;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * The ajax renderer.
   *
   * @var \Drupal\Core\Render\MainContent\AjaxRenderer
   */
  private $ajaxRenderer;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(CollectionHelper $collectionHelper, RendererInterface $renderer, AjaxRenderer $ajaxRenderer, RequestStack $requestStack, RouteMatchInterface $routeMatch, MessengerInterface $messenger) {
    $this->collectionHelper = $collectionHelper;
    $this->renderer = $renderer;
    $this->ajaxRenderer = $ajaxRenderer;
    $this->requestStack = $requestStack;
    $this->routeMatch = $routeMatch;
    $this->messenger = $messenger;
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   *
   * Hides legacy body field on non-legacy documents (cf.
   * self::isLegacyDocument).
   *
   * Insert UI for adding documents to a collection.
   */
  public function alterForm(array &$form, FormStateInterface $formState, string $formId) {
    // Handle legacy document.
    switch ($formId) {
      case 'node_os2loop_documents_document_form':
      case 'node_os2loop_documents_document_edit_form':
        $node = $this->getNode($formState);
        if (NULL !== $node && !$this->isLegacyDocument($node)) {
          unset($form['os2loop_documents_info_box'], $form['os2loop_documents_document_body']);
        }
    }

    // Handle documents in collection.
    switch ($formId) {
      case 'node_os2loop_documents_collection_form':
        $form['documents'] = [
          '#theme' => 'status_messages',
          '#message_list' => [
            'warning' => [
              $this->t('You must save the collection before you can add documents.'),
            ],
          ],
          '#status_headings' => [
            'status' => $this->t('Documents'),
          ],
        ];

        break;

      case 'node_os2loop_documents_collection_edit_form':
        $node = $this->getNode($formState);
        if (NULL !== $node && $node->getType() === NodeHelper::CONTENT_TYPE_COLLECTION) {
          if (!$formState->isSubmitted()) {
            $collection = $this->collectionHelper->loadCollectionItems($node);
            $data = array_map(static function ($item) {
              return [
                'id' => $item->getDocumentId(),
                'pid' => $item->getParentDocumentId(),
                'weight' => $item->getWeight(),
              ];
            }, $collection);
            $this->setDocumentsData($formState, $data);
            $formState->setRebuild(TRUE);
          }

          $this->buildDocumentTree($form, $formState, $node);
          break;
        }
    }

    $this->checkDocumentDeletion($form, $formState);
  }

  /**
   * Check if a document can be safely deleted.
   *
   * Heavily inspired by the entity_reference_integrity_enforce module.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @see https://www.drupal.org/project/entity_reference_integrity
   */
  private function checkDocumentDeletion(array &$form, FormStateInterface $formState) {
    /** @var \Drupal\Core\Entity\EntityFormInterface $formObject */
    $formObject = $formState->getFormObject();
    if ($formObject instanceof EntityFormInterface && 'delete' === $formObject->getOperation()) {
      $entity = $formObject->getEntity();
      if ($entity instanceof NodeInterface && NodeHelper::CONTENT_TYPE_DOCUMENT === $entity->getType()) {
        $collections = $this->collectionHelper->loadCollections($entity);

        if (!empty($collections)) {
          unset($form['actions']['submit'], $form['description']);

          $form['referencing_document_collections'] = [
            '#weight' => -10,
            'explanation' => [
              '#prefix' => '<div class="messages messages--error">',
              '#markup' => $this->formatPlural(
                count($collections),
                'You can not delete this document as it is being used in a collection.',
                'You can not delete this document as it is being used in @count collections.'
              ),
              '#suffix' => '</div>',
            ],
            'collections' => [
              '#theme' => 'item_list',
              '#items' => array_values(array_map(static function (NodeInterface $collection) {
                return $collection->toLink();
              }, $collections)),
            ],
          ];
        }
      }
    }
  }

  /**
   * Build document tree.
   */
  private function buildDocumentTree(array &$form, FormStateInterface $formState, NodeInterface $node) {
    $form['documents_label'] = [
      '#type' => 'label',
      '#title' => $this->t('Documents'),
    ];

    $form[self::DOCUMENTS] = [
      '#type' => 'container',
      '#title' => $this->t('Documents'),
      '#prefix' => '<div id="collection-documents-wrapper">',
      '#suffix' => '</div>',
    ];

    $form[self::DOCUMENTS][self::DOCUMENTS_TREE] = [
      '#type' => 'table',
      '#empty' => $this->t('No documents added yet.'),
      // TableDrag: Each array value is a list of callback arguments for
      // drupal_add_tabledrag(). The #id of the table is automatically
      // prepended; if there is none, an HTML ID is auto-generated.
      '#tabledrag' => [
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'row-pid',
          'source' => 'row-id',
          'hidden' => TRUE, /* hides the WEIGHT & PARENT tree columns below */
          'limit' => FALSE,
        ],
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'row-weight',
        ],
      ],
    ];

    // Build the table rows and columns.
    //
    // The first nested level in the render array forms the table row, on which
    // you likely want to set #attributes and #weight.
    // Each child element on the second level represents a table column cell in
    // the respective table row, which are render elements on their own. For
    // single output elements, use the table cell itself for the render element.
    // If a cell should contain multiple elements, simply use nested sub-keys to
    // build the render element structure for the renderer service as you would
    // everywhere else.
    // $results = self::getData();
    $results = $this->getCollectionDocuments($formState, $node);

    $treeForm = &$form[self::DOCUMENTS][self::DOCUMENTS_TREE];
    foreach ($results as $row) {
      // TableDrag: Mark the table row as draggable.
      $treeForm[$row['id']]['#attributes']['class'][] = 'draggable';

      // Indent item on load.
      $indentation = [];
      if (isset($row['depth']) && $row['depth'] > 0) {
        $indentation = [
          '#theme' => 'indentation',
          '#size' => $row['depth'],
        ];
      }

      // Some table columns containing raw markup.
      $treeForm[$row['id']]['name'] = [
        '#markup' => $row['name'],
        '#prefix' => !empty($indentation) ? $this->renderer->render($indentation) : '',
      ];

      $treeForm[$row['id']]['actions'] = [
        'remove' => [
          '#type' => 'submit',
          '#submit' => [[$this, 'removeDocumentSubmit']],
          '#ajax' => [
            'callback' => [$this, 'removeDocumentResult'],
            'wrapper' => 'collection-documents-wrapper',
            'progress' => [
              'type' => 'throbber',
              'message' => NULL,
            ],
          ],
          // We must have unique values to make FormState::getTriggeringElement
          // work as expected.
          '#value' => $this->t('Remove @title from collection', ['@title' => $row['name']]),
          '#attributes' => [
            'data-document-id' => $row['id'],
            // @todo Show only on leaf nodes.
            // 'class' => ['visually-hidden'],
          ],
        ],
      ];

      // This is hidden from #tabledrag array (above).
      // TableDrag: Weight column element.
      $treeForm[$row['id']]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for ID @id', ['@id' => $row['id']]),
        '#title_display' => 'invisible',
        '#default_value' => $row['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => [
          'class' => ['row-weight'],
        ],
      ];
      $treeForm[$row['id']]['parent']['id'] = [
        '#parents' => [self::DOCUMENTS_TREE, $row['id'], 'id'],
        '#type' => 'hidden',
        '#value' => $row['id'],
        '#attributes' => [
          'class' => ['row-id'],
        ],
      ];
      $treeForm[$row['id']]['parent']['pid'] = [
        '#parents' => [self::DOCUMENTS_TREE, $row['id'], 'pid'],
        '#type' => 'number',
        '#size' => 3,
        '#min' => 0,
        '#title' => $this->t('Parent ID'),
        '#default_value' => $row['pid'],
        '#attributes' => [
          'class' => ['row-pid'],
        ],
      ];
    }

    $form[self::DOCUMENTS]['add_document'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container-inline']],
      // '#weight' => 100,
      '#prefix' => '<div id="loop-documents-add-document">',
      '#suffix' => '</div>',
    ];

    $form[self::DOCUMENTS]['add_document']['document'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#element_validate' => [[$this, 'validateDocument']],
      '#selection_settings' => [
        'target_bundles' => ['os2loop_documents_document'],
      ],
      '#prefix' => '<div id="loop-documents-menu-document-options">',
      '#suffix' => '</div>',
    ];

    $form[self::DOCUMENTS]['add_document']['message'] = [
      '#markup' => $formState->get(self::DOCUMENTS_MESSAGE) ?? '',
    ];

    $form[self::DOCUMENTS]['add_document']['actions']['submit'] = [
      '#type' => 'submit',
      '#submit' => [[$this, 'addDocumentSubmit']],
      '#name' => 'add-document',
      // @see https://www.drupal.org/docs/drupal-apis/ajax-api/basic-concepts#sub_form
      '#ajax' => [
        'callback' => [$this, 'addDocumentResult'],
        'wrapper' => 'collection-documents-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
      '#value' => $this->t('Add document'),
    ];

    $form['actions']['submit']['#submit'][] = [$this, 'documentsSubmit'];
  }

  /**
   * Add document submit handler.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function addDocumentSubmit(array &$form, FormStateInterface $formState) {
    $data = $formState->getValue(self::DOCUMENTS_TREE) ?: [];
    $documentId = $this->getDocumentId($formState);
    if (NULL !== $documentId) {
      $document = $this->collectionHelper->loadDocument($documentId);
      if ($document && !isset($data[$document->id()])) {
        $weight = (int) (empty($data) ? 0 : max(array_column($data, 'weight')));
        $data[$document->id()] = [
          'weight' => $weight + 1,
          'id' => $document->id(),
          'pid' => 0,
        ];
        $this->messenger->addStatus($this->t('Document @title added to collection.', [
          '@title' => $this->buildDocumentTitle($document),
        ]));
        // Clear the document input field.
        $input = $formState->getUserInput();
        unset($input['document']);
        $formState->setUserInput($input);
      }
      else {
        $this->messenger->addWarning($this->t('Document @title already in collection.', [
          '@title' => $this->buildDocumentTitle($document),
        ]));
      }
    }
    $this->setDocumentsData($formState, $data);
    $formState->setRebuild(TRUE);
  }

  /**
   * Add document ajax callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return array
   *   The form element.
   */
  public function addDocumentResult(array &$form, FormStateInterface $formState) {
    return $form[self::DOCUMENTS];
  }

  /**
   * Remove document submit handler.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function removeDocumentSubmit(array &$form, FormStateInterface $formState) {
    $data = $formState->getValue(self::DOCUMENTS_TREE) ?: [];
    $trigger = $formState->getTriggeringElement();
    if (isset($trigger['#attributes']['data-document-id'])) {
      $documentId = (int) $trigger['#attributes']['data-document-id'];
      // Only leaf documents can be removed.
      if (!$this->collectionHelper->hasChildren($documentId, $data)) {
        unset($data[$documentId]);
        $document = $this->collectionHelper->loadDocument($documentId);
        $this->messenger->addStatus($this->t('Document @title removed from collection.', [
          '@title' => $this->buildDocumentTitle($document),
        ]));
      }
      else {
        $this->messenger->addError($this->t('Only leaf documents can be removed.'));
      }
    }
    $this->setDocumentsData($formState, $data);
    $formState->setRebuild(TRUE);
  }

  /**
   * Remove document ajax callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The form element.
   */
  public function removeDocumentResult(array &$form, FormStateInterface $formState) {
    $element = $this->addDocumentResult($form, $formState);
    $response = $this->ajaxRenderer->renderResponse($element, $this->requestStack->getCurrentRequest(), $this->routeMatch);
    // @todo Trigger "You have unsaved changes" warning.
    // $response->addCommand(…);
    return $response;
  }

  /**
   * Submit handler.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function documentsSubmit(array &$form, FormStateInterface $formState) {
    $data = $formState->getValue(self::DOCUMENTS_TREE);
    if (!is_array($data)) {
      $data = [];
    }
    $node = $this->getNode($formState);
    if (NULL !== $node) {
      $this->collectionHelper->updateCollection($node, $data);
    }
  }

  /**
   * Get node from form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return null|NodeInterface
   *   The node if any.
   */
  private function getNode(FormStateInterface $formState) {
    $form = $formState->getFormObject();
    if ($form instanceof EntityForm) {
      $entity = $form->getEntity();
      if ($entity instanceof NodeInterface) {
        return $entity;
      }
    }

    return NULL;
  }

  /**
   * Set documents data.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $data
   *   The data.
   *
   * @return \Drupal\Core\Form\FormStateInterface
   *   The form state.
   */
  private function setDocumentsData(FormStateInterface $formState, array $data) {
    return $formState->setValue(self::DOCUMENTS_TREE, $data);
  }

  /**
   * Get documents data.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return array
   *   The data.
   */
  private function getDocumentsData(FormStateInterface $formState) {
    return $formState->getValue(self::DOCUMENTS_TREE) ?? [];
  }

  /**
   * Get document id from form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return int|null
   *   The document id if any.
   */
  private function getDocumentId(FormStateInterface $formState): ?int {
    $spec = $formState->getValue('document');
    if (preg_match('/^\d+$/', $spec)) {
      return (int) $spec;
    }
    // Get last decimal number in spec.
    if (preg_match('/(?<id>\d+)(?!.*\d)/', $spec, $matches)) {
      return (int) $matches['id'];
    }

    return NULL;
  }

  /**
   * Validate document.
   */
  public function validateDocument(array &$element, FormStateInterface $formState) {
    // Run only when adding document.
    $trigger = $formState->getTriggeringElement();
    if ('add-document' !== $trigger['#name']) {
      return;
    }
    $documentId = $this->getDocumentId($formState);
    if (NULL !== $documentId) {
      $data = $this->getDocumentsData($formState);
      $document = $this->collectionHelper->loadDocument($documentId);
      $errorMessage = NULL;
      if (NULL === $document) {
        $errorMessage = $this->t('Missing document');
      }
      elseif (NodeHelper::CONTENT_TYPE_DOCUMENT !== $document->getType()) {
        $errorMessage = $this->t('Invalid document type (@type)', [
          '@type' => $document->getType(),
        ]);
      }
      elseif (isset($data[$document->id()])) {
        $errorMessage = $this->t('Document @title already in collection', [
          '@title' => $this->buildDocumentTitle($document),
        ]);
      }
    }
    else {
      $errorMessage = $this->t('No document specified');
    }

    if (NULL !== $errorMessage) {
      $formState->setErrorByName('document', $errorMessage);
    }
  }

  /**
   * Get collection documents.
   */
  private function getCollectionDocuments(FormStateInterface $formState, NodeInterface $node) {
    $data = $this->getDocumentsData($formState);

    return $this->collectionHelper->getCollectionItems($data);
  }

  /**
   * Check if a document is a legacy document.
   *
   * A legacy document is a document with a non-empty body field.
   */
  private function isLegacyDocument(NodeInterface $node) {
    if ($node->isNew()) {
      return FALSE;
    }

    $body = $node->get('os2loop_documents_document_body')->getValue()[0]['value'] ?? NULL;
    return !empty(strip_tags($body ?? ''));
  }

  /**
   * Build unique document title.
   */
  private function buildDocumentTitle(NodeInterface $document) {
    return sprintf('%s (%s)', $document->getTitle(), $document->id());
  }

}

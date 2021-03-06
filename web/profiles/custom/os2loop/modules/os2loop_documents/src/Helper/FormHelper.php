<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Form helper.
 */
class FormHelper {
  // @see https://www.drupal.org/project/media_library_form_element/issues/3155313#comment-13859469.
  use DependencySerializationTrait;
  use StringTranslationTrait;

  private const DOCUMENTS = 'documents';
  private const DOCUMENTS_TREE = 'documents_tree';
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
   * {@inheritdoc}
   */
  public function __construct(CollectionHelper $collectionHelper, RendererInterface $renderer) {
    $this->collectionHelper = $collectionHelper;
    $this->renderer = $renderer;
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  public function alterForm(array &$form, FormStateInterface $formState, string $formId) {
//     $this->collectionHelper->test();

    $node = $formState->getformObject()->getEntity();
    if (NULL !== $node) {
      if ($node->getType() === CollectionHelper::CONTENT_TYPE_COLLECTION) {
        $request = \Drupal::request();
        if ('GET' === $request->getMethod() && !$request->isXmlHttpRequest()) {
          $collection = $this->collectionHelper->loadCollectionItems($node);
          $data = array_map(static function ($item) {
            return [
              'id' => $item->document_id->value,
              'pid' => $item->parent_id->value,
              'weight' => $item->weight->value,
            ];
          }, $collection);
          $data = array_column($data, NULL, 'id');
          $this->setDocumentsData($formState, $data);
        }

        $this->buildDocumentTree($form, $formState, $node);
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
          '#value' => $this->t('Remove %document from collection', ['%document' => $row['name']]),
          '#attributes' => [
            'data-document-id' => $row['id'],
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
   *
   */
  public function addDocumentSubmit(array &$form, FormStateInterface $formState) {
    $data = $this->getDocumentsData($formState);
    $documentId = $this->getDocumentId($formState);
    $document = Node::load($documentId);

    if ($document && !isset($data[$document->id()])) {
      $weight = (int)max(array_column($data, 'weight'));
      $data[$document->id()] = [
        'weight' => $weight + 1,
        'id' => $document->id(),
        'pid' => 0,
      ];

      $this->setDocumentsData($formState, $data);
      $formState->setRebuild(TRUE);
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @return mixed
   */
  public function addDocumentResult(array &$form, FormStateInterface $formState) {
    return $form[self::DOCUMENTS];
  }

  /**
   * Submit handler.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function documentsSubmit(array &$form, FormStateInterface $formState) {
    $data = $formState->getValue(self::DOCUMENTS_TREE);
    $node = $formState->getformObject()->getEntity();
    $this->collectionHelper->updateCollection($node, $data);
  }

  /**
   *
   */
  private function setDocumentsData(FormStateInterface $formState, array $data) {
    return $formState->set(self::DOCUMENTS, $data);
  }

  /**
   *
   */
  private function getDocumentsData(FormStateInterface $formState) {
    return $formState->get(self::DOCUMENTS) ?? [];
  }

  /**
   *
   */
  private function getDocumentId(FormStateInterface $formState) {
    $spec = $formState->getValue('document');
    if (preg_match('/^\d+$/', $spec)) {
      return (int) $spec;
    }
    if (preg_match('/\((?<id>\d+)\)$/', $spec, $matches)) {
      return (int) $matches['id'];
    }

    return NULL;
  }

  /**
   * Validate document.
   */
  public function validateDocument(array &$element, FormStateInterface $formState) {
    // @todo This should only run on document add.
    if (1 === 1) {
      return;
    }
    $trigger = $formState->getTriggeringElement();
    $documentId = $this->getDocumentId($formState);
    if (NULL !== $documentId) {
      $data = $this->getDocumentsData($formState);
      $document = Node::load($documentId);
      $errorMessage = NULL;
      if (NULL === $document) {
        $errorMessage = $this->t('Missing document');
      }
      elseif (CollectionHelper::CONTENT_TYPE_DOCUMENT !== $document->getType()) {
        $errorMessage = $this->t('Invalid document type (@type)', [
          '@type' => $document->getType(),
        ]);
      }
      elseif (isset($data[$document->id()])) {
        $errorMessage = $this->t('Document @title (@id) already in collection', [
          '@title' => $document->getTitle(),
          '@id' => $document->id(),
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
   *
   */
  public function removeDocumentSubmit(array &$form, FormStateInterface $formState) {
    $trigger = $formState->getTriggeringElement();
    $documentId = $trigger['#attributes']['data-document-id'] ?? NULL;
    if (NULL !== $documentId) {
      // Remove document from collection.
      // @todo remove children
      $node = $formState->getformObject()->getEntity();
      $data = $this->getDocumentsData($formState);
      if (isset($data[$documentId])) {
        unset($data[$documentId]);
        $this->setDocumentsData($formState, $data);
        $formState->setRebuild(TRUE);
      }
    }
  }

  /**
   *
   */
  public function removeDocumentResult(array &$form, FormStateInterface $formState) {
    return $this->addDocumentResult($form, $formState);
  }

}

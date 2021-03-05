<?php

namespace Drupal\os2loop_documents\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Collection helper.
 */
class CollectionHelper {
  // Use StringTranslationTrait;.
  public const CONTENT_TYPE_DOCUMENT = 'os2loop_documents_document';
  public const CONTENT_TYPE_COLLECTION = 'os2loop_documents_collection';

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * Constructor.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * Implements hook_form_alter().
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id) {
    $node = $form_state->getformObject()->getEntity();
    if (isset($node)) {
      if ($node->getType() === 'os2loop_documents_document_colle') {
        $this->buildDocumentTree($form, $form_state, $node);
      }

      if (in_array($node->type, [
        'loop_documents_collection', 'loop_documents_document',
      ])) {
        $form['#attached']['css'][] = drupal_get_path('module', 'loop_documents') . '/css/loop_documents.admin.css';
        $form['#attached']['js'][] = drupal_get_path('module', 'loop_documents') . '/js/loop_documents.admin.js';
      }
    }
  }

  /**
   *
   */
  public function hmmValidate(array &$form, FormStateInterface $form_state) {
    $element = $form['hmm-wrapper']['hmm_value'];
    $element = $form['os2loop_documents_documents'];
    $value = $form_state->getValue('os2loop_documents_documents');
    $form_state->setValue('os2loop_documents_documents', [['value' => __METHOD__]]);
    // header('content-type: text/plain'); echo var_export($value, true); die(__FILE__.':'.__LINE__.':'.__METHOD__);
    // $form_state->setValueForElement($element, 'hest');.
    // header('content-type: text/plain'); echo var_export(null, true); die(__FILE__.':'.__LINE__.':'.__METHOD__);
    //    $form_state->setError($element, uniqid(__METHOD__));
    // $form_state->setTemporaryValue()
    //    $form_state->setCached(false);
    //    $form_state->setRebuild(TRUE);
    $value = (int) $form_state->getValue('hmm_value');
    $form_state->setValue('hmm_value', $value + 1);
    // $form_state->setRebuild(TRUE);
  }

  /**
   *
   */
  public function hmmSubmit(array &$form, FormStateInterface $form_state) {
    // $value = (int) $form_state->getValue('hmm_value');
    //
    //    $form_state->setValue('hmm_value', $value +1);
    //    $form_state->setRebuild(TRUE);
  }

  /**
   * Ajax wrapper result.
   */
  public function hmmAdd(array &$form, FormStateInterface $form_state) {
    $element = $form['hmm-wrapper'];
    $element['box']['#markup'] = "Clicked submit ({$form_state->getValue('op')}): " . date('c');
    return $element;
  }

  /**
   * Build document tree.
   */
  private function buildDocumentTree(array &$form, FormStateInterface $form_state, NodeInterface $node) {
    return;
    // $form['hmm-wrapper'] = [
    //       '#type' => 'fieldset',
    //       '#title' => t('Hmm …') . uniqid(),
    //       '#prefix' => $this->renderer->render($label) . '<div id="hmm-wrapper">',
    //       '#suffix' => '</div>',
    //       'hmm_value' => [
    //         '#type' => 'textfield',
    //         // '#default_value' => uniqid(),
    //         // '#default_value' => $form_state->getValue('hmm_value'),
    //         '#description' => uniqid(),
    //       ],
    //       'hmm_submit' => [
    //         '#type' => 'submit',
    //         '#submit' => [get_class($this) . '::hmmSubmit'],
    //         // @see https://www.drupal.org/docs/drupal-apis/ajax-api/basic-concepts#sub_form
    //         '#ajax' => [
    //           'callback' => get_class($this) . '::hmmAdd',
    //           'wrapper' => 'hmm-wrapper',
    //           'progress' => [
    //             'type' => 'throbber',
    //           ],
    //         ],
    //         '#value' => t('Do stuff'),
    //       ],
    //     ];
    //     $form['#validate'][] = [$this, 'hmmValidate'];
    // //    $form_state->setValueForElement($form['os2loop_documents_documents'], 'hest');
    //     // $form_state->setValue('hmm_value', 'aaa');
    //     return;
    // $form['os2loop_documents_documents']['#type'] = 'hidden';
    $form['documents-wrapper'] = [
      '#weight' => $form['os2loop_documents_documents']['#weight'],
      '#type' => 'container',
      '#title' => t('Documents'),
      '#prefix' => $this->renderer->render($label) . '<div id="collection-documents-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['documents-wrapper']['label'] = [
      '#type' => 'label',
      '#title' => t('Documents'),
    ];

    $form['documents-wrapper']['documents'] = [
      '#type' => 'table',
      '#empty' => t('No documents added to collection yet.') . uniqid(),
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

    // @see https://drupal.stackexchange.com/questions/267347/change-value-on-validate-form-when-i-create-or-edit-a-block#comment365273_267441
    $form['os2loop_documents_documents']['widget'][0]['#element_validate'][] = [
      $this,
      'validateDocuments',
    ];
    $form['documents-wrapper']['add_document'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container-inline']],
      '#prefix' => '<div id="loop-documents-add-document">',
      '#suffix' => '</div>',

      'document' => [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#selection_settings' => [
          'target_bundles' => ['os2loop_documents_document'],
        ],
        '#prefix' => '<div id="loop-documents-document-options">',
        '#suffix' => '</div>',
        '#element_validate' => [[$this, 'validateDocument']],
      ],

      'actions' => [
        'submit' => [
          '#type' => 'submit',
          '#submit' => [get_class($this) . '::addDocumentSubmit'],
          // @see https://www.drupal.org/docs/drupal-apis/ajax-api/basic-concepts#sub_form
          '#ajax' => [
            'callback' => get_class($this) . '::addDocumentSet',
            'wrapper' => 'collection-documents-wrapper',
            'progress' => [
              'type' => 'throbber',
              'message' => t('Adding document to collection'),
            ],
          ],
          '#value' => t('Add document'),
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
    $results = $this->getCollectionDocuments($form_state, $node);

    $formElement = &$form['documents-wrapper']['documents'];
    foreach ($results as $row) {
      // TableDrag: Mark the table row as draggable.
      $formElement[$row['id']]['#attributes']['class'][] = 'draggable';

      // Indent item on load.
      $indentation = [
        '#theme' => 'indentation',
        '#size' => $row['depth'] ?? 0,
      ];

      // Some table columns containing raw markup.
      $formElement[$row['id']]['name'] = [
        '#markup' => $row['name'],
        '#prefix' => $this->renderer->render($indentation),
      ];

      // This is hidden from #tabledrag array (above).
      // TableDrag: Weight column element.
      $formElement[$row['id']]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for ID @id', ['@id' => $row['id']]),
        '#title_display' => 'invisible',
        '#default_value' => $row['weight'],
        // Classify the weight element for #tabledrag.
        '#attributes' => [
          'class' => ['row-weight'],
        ],
      ];
      $formElement[$row['id']]['parent']['id'] = [
        '#parents' => ['documents', $row['id'], 'id'],
        '#type' => 'hidden',
        '#value' => $row['id'],
        '#attributes' => [
          'class' => ['row-id'],
        ],
      ];
      $formElement[$row['id']]['parent']['pid'] = [
        '#parents' => ['documents', $row['id'], 'pid'],
        '#type' => 'number',
        '#size' => 3,
        '#min' => 0,
        '#title' => t('Parent ID'),
        '#default_value' => $row['pid'],
        '#attributes' => [
          'class' => ['row-pid'],
        ],
      ];
    }
  }

  /**
   * Ajax submit handler.
   */
  public function addDocumentSubmit(array &$form, FormStateInterface $form_state) {
    return;
    $documents = $form_state->getValue('os2loop_documents_documents')[0]['value'] ?? NULL;
    $data = json_decode($documents, TRUE);
    if (!is_array($data)) {
      $data = [];
    }
    $document_id = $form_state->getValue('document');
    $document = Node::load($document_id);

    // Document has already been validated by self::validateDocument().
    if (NULL !== $document
    && self::CONTENT_TYPE_DOCUMENT === $document->getType()
    && !isset($data[$document->id()])) {
      $data[$document->id()] = [
        'weight' => 1000,
        'id' => $document->id(),
        'pid' => 0,
      ];

      $form_state->setValue('os2loop_documents_documents', [['value' => json_encode($data)]]);
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * Ajax wrapper result.
   */
  public function addDocumentSet(array &$form, FormStateInterface $form_state) {
    return $form['documents-wrapper'];
  }

  /**
   * Get collection documents.
   */
  private function getCollectionDocuments(FormStateInterface $form_state, NodeInterface $node) {
    $data = $form_state->get('documents_data') ?? [];
    if (!is_array($data)) {
      $data = [];
    }

    $nodes = Node::loadMultiple(array_keys($data));
    foreach ($data as &$item) {
      if ('0' === $item['pid']) {
        $item['depth'] = 0;
      }
      $node = $nodes[$item['id']] ?? NULL;
      $item['name'] = $node ? $node->getTitle() : $item['id'];
    }

    foreach ($data as &$item) {
      if (!isset($item['depth'])) {
        $item['depth'] = $data[$item['pid']]['depth'] + 1;
      }
    }

    return $data;
  }

  /**
   * Validate documents.
   *
   * @see https://drupal.stackexchange.com/questions/267347/change-value-on-validate-form-when-i-create-or-edit-a-block#comment365273_267441
   */
  public function validateDocuments(array &$element, FormStateInterface $form_state) {
    $documents = $form_state->getValue('documents');
    if (!is_array($documents)) {
      $documents = (object) [];
    }
    // https://drupal.stackexchange.com/questions/267347/change-value-on-validate-form-when-i-create-or-edit-a-block#comment365273_267441
    $form_state->setValueForElement($element, ['value' => json_encode($documents)]);
  }

  /**
   * Validate document.
   */
  public function validateDocument(array &$element, FormStateInterface $form_state) {
    $spec = $form_state->getValue('document');
    if (preg_match('/\((?<id>\d+)\)$/', $spec, $matches)) {
      $data = NULL;
      $spec = $form_state->getValue('documents_data');
      try {
        $data = json_decode($spec, TRUE);
      }
      catch (\Exception $exception) {
      }

      if (!is_array($data)) {
        $data = [];
      }

      $nid = $matches['id'];
      $document = Node::load($nid);
      $errorMessage = NULL;
      if (NULL === $document) {
        $errorMessage = t('Missing document');
      }
      elseif (self::CONTENT_TYPE_DOCUMENT !== $document->getType()) {
        $errorMessage = t('Invalid document type (@type)', [
          '@type' => $document->getType(),
        ]);
      }
      elseif (isset($data[$document->id()])) {
        $errorMessage = t('Document @title (@id) already in collection', [
          '@title' => $document->getTitle(),
          '@id' => $document->id(),
        ]);
      }

      if (NULL !== $errorMessage) {
        $form_state->setErrorByName('document', $errorMessage);
      }

      // $data[$document->id()] = [
      //   'weight' => 1000,
      //   'id' => $document->id(),
      //   'pid' => 0,
      // ];
      $form_state->set('documents_data', $data);
      $form_state->setRebuild();
    }
    else {
      $form_state->setErrorByName('document', '$errorMessage');
    }
  }

  /**
   * Add depth to items in a list of items with weight and parent id (pid).
   */
  public function addDepths(array &$items, $parentId, $parentDepth = 0) {
  }

  /**
   * Build a tree from a list of items with weight and parent id (pid).
   */
  public function buildTree(array $items) {

  }

}

<?php

namespace Drupal\os2loop_search_db\Plugin\facets\widget;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\CheckboxWidget;
use Drupal\facets\Result\Result;
use Drupal\os2loop_search_db\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A widget that hides some “Document” content types.
 *
 * @FacetsWidget(
 *   id = "os2loop_search_db_document",
 *   label = @Translation("OS2Loop Document"),
 *   description = @Translation("OS2Loop Document")
 * )
 */
final class DocumentWidget extends CheckboxWidget implements ContainerFactoryPluginInterface {
  /**
   * The helper.
   *
   * @var \Drupal\os2loop_search_db\Helper\Helper
   */
  private $helper;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Helper $helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
  public function build(FacetInterface $facet) {
    $contentTypeGroups = $this->helper->getContentTypeGroups();
    // We exclude all content types that are included in another content type.
    $excludedTypes = array_merge(...array_values($contentTypeGroups));
    $results = array_filter($facet->getResults(), static function (Result $result) use ($excludedTypes) {
      return !in_array($result->getRawValue(), $excludedTypes, TRUE);
    });
    $facet->setResults($results);

    return parent::build($facet);
  }

}

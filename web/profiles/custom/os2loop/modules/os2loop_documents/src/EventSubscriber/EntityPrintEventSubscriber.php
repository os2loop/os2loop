<?php

namespace Drupal\os2loop_documents\EventSubscriber;

use Drupal\Core\Url;
use Drupal\entity_print\Event\PreSendPrintEvent;
use Drupal\entity_print\Event\PrintEvents;
use Drupal\entity_print\Plugin\EntityPrint\PrintEngine\PhpWkhtmlToPdf;
use Drupal\node\NodeInterface;
use Drupal\os2loop_documents\Helper\NodeHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber.
 */
class EntityPrintEventSubscriber implements EventSubscriberInterface {

  /**
   * Event callback.
   */
  public function preSend(PreSendPrintEvent $event) {
    $engine = $event->getPrintEngine();
    if ($engine instanceof PhpWkhtmlToPdf) {
      $entities = $event->getEntities();
      $entity = reset($entities);
      if ($entity instanceof NodeInterface
        && in_array($entity->bundle(), [
          NodeHelper::CONTENT_TYPE_COLLECTION,
          NodeHelper::CONTENT_TYPE_DOCUMENT,
        ], TRUE)) {
        $url = Url::fromRoute('os2loop_documents.pdf_region', [
          'node' => $entity->id(),
          'region' => 'header',
        ], [
          'absolute' => TRUE,
        ])->toString(FALSE);
        $engine->setHeaderText($url, 'html');

        $url = Url::fromRoute('os2loop_documents.pdf_region', [
          'node' => $entity->id(),
          'region' => 'footer',
        ], [
          'absolute' => TRUE,
        ])->toString(FALSE);
        $engine->setFooterText($url, 'html');
      }

      $engine->getPrintObject()->setOptions([
        // Match header height (cf. template).
        'margin-top' => 30,
        'margin-right' => 20,
        'margin-bottom' => 20,
        'margin-left' => 20,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PrintEvents::PRE_SEND => 'preSend',
    ];
  }

}

<?php

namespace Drupal\os2loop_documents\EventSubscriber;

use Drupal\Core\Site\Settings;
use Drupal\entity_print\Event\PrintEvents;
use Drupal\entity_print\Event\PrintHtmlAlterEvent;
use Masterminds\HTML5;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Event subscriber.
 */
class EntityPrintEventSubscriber implements EventSubscriberInterface {
  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * EntityPrintEventSubscriber constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * Event callback.
   *
   * Grabbed from Drupal\entity_print\EventSubscriber\PostRenderSubscriber
   * and modified to our needs.
   */
  public function printHtmlAlterEvent(PrintHtmlAlterEvent $event) {
    $entities = $event->getEntities();
    foreach ($entities as $entity) {
      if ($entity->getEntityTypeId() === 'node') {
        $routeName = \Drupal::routeMatch()->getRouteName();
        $pdfCustomBaseUrl = Settings::get('pdf_custom_base_url');

        $base_url = ($routeName == 'entity_print.view' && $pdfCustomBaseUrl) ? $pdfCustomBaseUrl : $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();

        $html_string = &$event->getHtml();
        $html5 = new HTML5();
        $document = $html5->loadHTML($html_string);

        // Only add a base element if there is none set in the html.
        if ($document->getElementsByTagName('base')->count() === 0) {
          $base = $document->createElement('base');
          $base->setAttribute('href', $base_url);

          // Add new base element to the head element or ...
          if ($document->getElementsByTagName('head')->count() !== 0) {
            /** @var \DOMNode $head */
            foreach ($document->getElementsByTagName('head') as $head) {
              $head->appendChild($base);
            }
          }
          // (edge-case) create a head element to add the base element to.
          else {
            $head = $document->createElement('head');
            $document->appendChild($head);
            $head->appendChild($base);
          }
        }

        // Define a function that will convert root relative uris into absolute
        // urls.
        $transform = function ($tag, $attribute) use ($document, $base_url) {
          foreach ($document->getElementsByTagName($tag) as $node) {
            $attribute_value = $node->getAttribute($attribute);

            // Handle protocol agnostic URLs as well.
            if (mb_substr($attribute_value, 0, 2) === '//') {
              $node->setAttribute($attribute, $base_url . mb_substr($attribute_value, 1));
            }
            elseif (mb_substr($attribute_value, 0, 1) === '/') {
              $node->setAttribute($attribute, $base_url . $attribute_value);
            }
          }
        };

        // Transform stylesheets, links and images.
        $transform('link', 'href');
        $transform('a', 'href');
        $transform('img', 'src');

        // Overwrite the HTML.
        $html_string = $html5->saveHTML($document);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PrintEvents::POST_RENDER  => 'printHtmlAlterEvent',
    ];
  }

}

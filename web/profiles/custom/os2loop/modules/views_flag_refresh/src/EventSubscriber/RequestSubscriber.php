<?php

namespace Drupal\views_flag_refresh\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\views_flag_refresh\Ajax\ViewsFlagRefreshCommand;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\views\Ajax\ViewAjaxResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\flag\FlagInterface;

/**
 * Request event subscriber.
 */
class RequestSubscriber implements EventSubscriberInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * RequestSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * Handles response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onResponse(ResponseEvent $event) {
    $route_name = $this->routeMatch->getRouteName();
    $response = $event->getResponse();

    // Check flag/unflag route with AJAX response.
    if (in_array($route_name, [
      'flag.action_link_flag',
      'flag.action_link_unflag',
    ]) && $response instanceof AjaxResponse) {
      if (($flag = $this->routeMatch->getParameter('flag')) instanceof FlagInterface) {
        $command = new ViewsFlagRefreshCommand($flag);
        $response->addCommand($command);
      }
    }
    // Check view AJAX response.
    if ($route_name == 'views.ajax' && $response instanceof ViewAjaxResponse) {
      $extenders = $response->getView()->getDisplay()->getExtenders();
      // Check Views Flag Refresh display extender with
      // enabled noscrolltop option.
      if (isset($extenders['views_flag_refresh']) && !empty($extenders['views_flag_refresh']->options['noscrolltop'])) {
        $this->removeScrollTopCommand($response);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onResponse'];
    return $events;
  }

  /**
   * Remove viewsScrollTop AJAX command from response.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   Response with AJAX commands.
   */
  protected function removeScrollTopCommand(AjaxResponse $response) {
    $commands = &$response->getCommands();
    foreach ($commands as $key => $command) {
      if ($command['command'] == 'viewsScrollTop') {
        unset($commands[$key]);
      }
    }
  }

}

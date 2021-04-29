<?php

namespace Drupal\views_flag_refresh\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\flag\FlagInterface;

/**
 * Refresh view when certain flags are selected.
 *
 * The client side code can be found in js/views-flag-refresh.js.
 */
class ViewsFlagRefreshCommand implements CommandInterface {

  /**
   * The flag entity.
   *
   * @var \Drupal\flag\FlagInterface
   */
  protected $flag;

  /**
   * ViewsFlagRefreshCommand constructor.
   *
   * @param \Drupal\flag\FlagInterface $flag
   *   The flag entity.
   */
  public function __construct(FlagInterface $flag) {
    $this->flag = $flag;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'viewsFlagRefresh',
      'flag' => $this->flag->id(),
    ];
  }

}

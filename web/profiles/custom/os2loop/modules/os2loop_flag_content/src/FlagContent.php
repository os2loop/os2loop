<?php

namespace Drupal\os2loop_flag_content;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Custom twig functions.
 */
class FlagContent extends AbstractExtension {

  /**
   * Returns the available functions.
   */
  public function getFunctions() {
    return [
      new TwigFunction('flag_form', [$this, 'flagForm']),
    ];
  }

  /**
   * Creates a flag form.
   */
  public function flagForm($node) {
    $form = \Drupal::formBuilder()->getForm('Drupal\os2loop_flag_content\Form\FlagContentForm');
    return $form;
  }

}

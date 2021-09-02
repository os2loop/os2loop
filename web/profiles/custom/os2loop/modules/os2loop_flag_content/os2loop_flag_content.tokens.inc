<?php

/**
 * @file
 * Token integration for the views module.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_tokens().
 */
function os2loop_flag_content_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  return Drupal::service('os2loop_flag_content.mail_helper')->tokens($type, $tokens, $data);
}

/**
 * Implements hook_token_info().
 */
function os2loop_flag_content_token_info() {
  return Drupal::service('os2loop_flag_content.mail_helper')->tokenInfo();
}

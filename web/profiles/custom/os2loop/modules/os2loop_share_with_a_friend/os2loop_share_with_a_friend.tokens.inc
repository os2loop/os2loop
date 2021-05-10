<?php

/**
 * @file
 * Token integration for the views module.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_tokens().
 */
function os2loop_share_with_a_friend_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  return Drupal::service('os2loop_share_with_a_friend.mail_helper')->tokens($type, $tokens, $data);
}

/**
 * Implements hook_token_info().
 */
function os2loop_share_with_a_friend_token_info() {
  return Drupal::service('os2loop_share_with_a_friend.mail_helper')->tokenInfo();
}

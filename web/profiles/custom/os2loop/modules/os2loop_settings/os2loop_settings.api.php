<?php

/**
 * @file
 * Hooks specific to the os2loop_settings module.
 */

/**
 * Check if current user is granted a role or permission on an object.
 *
 * @param string $attribute
 *   The attribute to check.
 * @param null|mixed $object
 *   The optional object.
 *
 * @return bool
 *   True if and only if the attribute is granted on the object.
 */
function hook_os2loop_settings_is_granted(string $attribute, $object = NULL): bool {
  if ('can do stuff' === $attribute && $object instanceof \Drupal\node\NodeInterface) {
    return TRUE;
  }
}

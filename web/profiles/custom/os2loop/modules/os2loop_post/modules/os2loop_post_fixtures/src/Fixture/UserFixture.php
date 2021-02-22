<?php

namespace Drupal\os2loop_post_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\User;

/**
 * User fixture.
 *
 * @package Drupal\os2loop_post_fixtures\Fixture
 */
class UserFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $user = User::create([
      'name' => 'os2loop_post_editor',
      'mail' => 'os2loop_post_editor@example.com',
      'pass' => 'os2loop_post_editor-password',
      // Active.
      'status' => 1,
      'roles' => [
        'os2loop_post_editor',
      ],
    ]);
    $user->save();

    $user = User::create([
      'name' => 'os2loop_post_author',
      'mail' => 'os2loop_post_author@example.com',
      'pass' => 'os2loop_post_author-password',
      // Active.
      'status' => 1,
      'roles' => [
        'os2loop_post_author',
      ],
    ]);
    $user->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_user'];
  }

}

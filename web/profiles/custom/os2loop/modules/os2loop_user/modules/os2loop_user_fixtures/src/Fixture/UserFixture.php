<?php

namespace Drupal\os2loop_user_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\User;

/**
 * User fixture.
 *
 * @package Drupal\os2loop_user_fixtures\Fixture
 */
class UserFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $user = User::create([
      'uid' => 2,
      'name' => 'administrator',
      'mail' => 'administrator@example.com',
      'pass' => 'administrator-password',
      'status' => 1, // Active
      'roles' => [
        'os2loop_administrator',
      ]
    ]);
    $user->save();

    $user = User::create([
      'uid' => 3,
      'name' => 'user',
      'mail' => 'user@example.com',
      'pass' => 'user-password',
      'status' => 1, // Active
      'roles' => [
        'authenticated',
      ]
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

<?php

namespace Drupal\os2loop_user_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\os2loop_taxonomy_fixtures\Fixture\ProfessionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\user\Entity\User;

/**
 * User fixture.
 *
 * @package Drupal\os2loop_user_fixtures\Fixture
 */
class UserFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $user = User::create([
      'name' => 'administrator',
      'mail' => 'administrator@example.com',
      'pass' => 'administrator-password',
      // Active.
      'status' => 1,
      'roles' => [
        'os2loop_user_administrator',
      ],
      'os2loop_user_given_name' => 'Admin',
      'os2loop_user_family_name' => 'Jensen',
      'os2loop_user_job_title' => 'Administrator',
      'os2loop_user_place' => 'Headquarters',
    ]);
    $user->save();
    $this->setReference('user:administrator', $user);

    $user = User::create([
      'name' => 'user',
      'mail' => 'user@example.com',
      'pass' => 'user-password',
      // Active.
      'status' => 1,
      'roles' => [
        'authenticated',
      ],
      'os2loop_user_given_name' => 'User',
      'os2loop_user_family_name' => 'User',
      'os2loop_user_job_title' => 'User',
      'os2loop_user_place' => 'The office',
      'os2loop_user_professions' => [
        [
          'target_id' => $this->getReference('os2loop_profession:Administrativ medarbejder')->id(),
        ],
      ],
      'os2loop_user_areas_of_expertise' => [
        [
          'target_id' => $this->getReference('os2loop_subject:Dokumentation')->id(),
        ],
      ],
    ]);
    $user->save();
    $this->setReference('user:user', $user);

    // Create a user for each role in the system.
    foreach ([
      'os2loop_user_document_author',
      'os2loop_user_document_collection_editor',
      'os2loop_user_documentation_coordinator',
      'os2loop_user_external_sources_editor',
      'os2loop_user_manager',
      'os2loop_user_post_author',
      'os2loop_user_read_only',
      'os2loop_user_user_administrator',
    ] as $role) {
      $userName = str_replace('os2loop_user_', '', $role);
      $user = User::create([
        'name' => $userName,
        'mail' => $userName . '@example.com',
        'pass' => $userName . '-password',
        // Active.
        'status' => 1,
        'roles' => [
          $role,
        ],
      ]);
      $user->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      ProfessionFixture::class,
      SubjectFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_user'];
  }

}

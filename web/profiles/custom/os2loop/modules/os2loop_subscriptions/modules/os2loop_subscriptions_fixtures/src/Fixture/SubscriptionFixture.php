<?php

namespace Drupal\os2loop_subscriptions_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\os2loop_question_fixtures\Fixture\QuestionFixture;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;
use Drupal\os2loop_user_fixtures\Fixture\UserFixture;

/**
 * Page fixture.
 *
 * @package Drupal\os2loop_subscriptions_fixtures\Fixture
 */
class SubscriptionFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {
  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  private $flagService;

  /**
   * Constructor.
   */
  public function __construct(FlagServiceInterface $flagService) {
    $this->flagService = $flagService;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $this->flagService->flag(
      $this->flagService->getFlagById('os2loop_subscription_term'),
      $this->getReference('os2loop_subject:Diverse'),
      $this->getReference('user:user'),
    );

    $this->flagService->flag(
      $this->flagService->getFlagById('os2loop_subscription_node'),
      $this->getReference('os2loop_post:the-first-post'),
      $this->getReference('user:user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      SubjectFixture::class,
      UserFixture::class,
      QuestionFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_subscriptions'];
  }

}

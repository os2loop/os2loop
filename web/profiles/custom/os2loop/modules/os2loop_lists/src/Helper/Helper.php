<?php

namespace Drupal\os2loop_lists\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Os2Loop list helper.
 *
 * Helper class for creating content lists.
 */
class Helper {
  /**
   * The logged in user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The full user entity.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * Helper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Logged in user account.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser) {
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get content related by subject.
   *
   * @return array
   *   An array of content that shares subject term with the logged in users
   *   expertises terms.
   */
  public function getContentByUserExpertise(): array {
    $user_expertises = $this->getCurrentUserExpertisesId();
    if (empty($user_expertises)) {
      return [];
    }
    $list = $this->getList('os2loop_shared_subject', $user_expertises);
    // Filter out content that has been replied to.
    foreach ($list as $key => $node) {
      if ($node->hasField('os2loop_question_answers') && $node->get('os2loop_question_answers')->comment_count > 0) {
        unset($list[$key]);
      }
    }
    return $list;
  }

  /**
   * Get content related by profession.
   *
   * @return array
   *   An array of content that shares profession terms with the logged in
   *   users professions terms.
   */
  public function getContentByUserProfession(): array {
    $user_professions = $this->getCurrentUserProfessionsId();
    if (empty($user_professions)) {
      return [];
    }

    $list = $this->getList('os2loop_shared_profession', $user_professions);
    // Filter out content that has been replied to.
    foreach ($list as $key => $node) {
      if ($node->hasField('os2loop_question_answers') && $node->get('os2loop_question_answers')->comment_count > 0) {
        unset($list[$key]);
      }
    }
    return $list;
  }

  /**
   * The logged in users expertises.
   *
   * @return array
   *   An array of logged in users expertise taxonomy term ids.
   */
  private function getCurrentUserExpertisesId(): array {
    $user = $this->userStorage->load($this->currentUser->id());
    /** @var \Drupal\user\UserInterface $user */
    $expertises = $user->get('os2loop_user_areas_of_expertise')->getValue();
    return array_column($expertises, 'target_id');
  }

  /**
   * The logged in users professions.
   *
   * @return array
   *   An array of logged in users profession taxonomy term ids.
   */
  private function getCurrentUserProfessionsId(): array {
    $user = $this->userStorage->load($this->currentUser->id());
    /** @var \Drupal\user\UserInterface $user */
    $professions = $user->get('os2loop_user_professions')->getValue();
    return array_column($professions, 'target_id');
  }

  /**
   * Build list.
   *
   * @param string $field_name
   *   The name of the field to make condition for.
   * @param array $taxonomy_terms
   *   The condition of the field.
   *
   * @return array
   *   A list of nodes with shared taxonomy terms.
   */
  private function getList(string $field_name, array $taxonomy_terms): array {
    return $this->entityTypeManager
      ->getListBuilder('node')
      ->getStorage()
      ->loadByProperties([
        'type' => 'os2loop_question',
        'status' => 1,
        $field_name => $taxonomy_terms,
      ]);
  }

}

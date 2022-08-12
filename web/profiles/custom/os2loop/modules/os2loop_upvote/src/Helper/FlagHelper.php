<?php

namespace Drupal\os2loop_upvote\Helper;

use Drupal\Core\Cache\Cache;
use Drupal\flag\FlagCountManager;

/**
 * Flaghelper for creating top comment and count flags.
 */
class FlagHelper {
  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagCountManager
   */
  protected $flagCountManager;

  /**
   * Constructor.
   *
   * @param \Drupal\flag\FlagCountManager $flagCountManager
   *   The flag count manager.
   */
  public function __construct(FlagCountManager $flagCountManager) {
    $this->flagCountManager = $flagCountManager;
  }

  /**
   * Implements hook_preprocess_field().
   *
   * Find the top comment.
   *
   * If there is a comment marked as "correct answer", this is it.
   * If there is no "correct answer", the top comment will be the most upvoted.
   */
  public function preprocessField(array &$variables) {
    // An array of the two relevant comment fields.
    $field = ['os2loop_question_answers', 'os2loop_post_comments'];
    if (in_array($variables['field_name'], $field)) {
      foreach ($variables['comments'] as $comment) {
        if (isset($comment['#comment'])) {
          // Get flags for this comment: correct answer and upvote.
          $comment_flag_counts = $this->flagCountManager->getEntityFlagCounts($comment['#comment']);
          // If comment is marked as correct answer, it is the top comment.
          if (isset($comment_flag_counts['os2loop_upvote_correct_answer'])) {
            $correct_answer = $comment;
            break;
          }
          else {
            if (isset($comment_flag_counts['os2loop_upvote_upvote_button'])) {
              // If there is no upvoted comment, set it to current comment.
              $upvoted_comment = $upvoted_comment ?? $comment;

              // Get number of upvotes for comment and upvoted comment.
              $upvoted_comment_upvotes = intval($this->flagCountManager->getEntityFlagCounts($upvoted_comment['#comment']));
              $comment_upvotes = intval($comment_flag_counts['os2loop_upvote_upvote_button']);

              if ($comment_upvotes > $upvoted_comment_upvotes) {
                // If current comment has more upvotes make it upvoted comment.
                $upvoted_comment = $comment;
              }
            }
          }
        }
      }

      if (isset($correct_answer)) {
        // Set a top value, used to add a styling class.
        $correct_answer['#top'] = 'correct-answer';
        // Threaded: false, to avoid indentation (styling).
        $correct_answer['#comment_threaded'] = FALSE;
        // Add to top of comment list.
        array_unshift($variables['comments'], $correct_answer);
      }
      elseif (isset($upvoted_comment)) {
        // Set a top value, used to add a styling class.
        $upvoted_comment['#top'] = 'most-upvotes';
        // Threaded: false, to avoid indentation (styling).
        $upvoted_comment['#comment_threaded'] = FALSE;
        // Add to top of comment list.
        array_unshift($variables['comments'], $upvoted_comment);
      }
    }
  }

  /**
   * Implements hook_preprocess_flag().
   *
   * Add number of upvotes to flag.
   */
  public function preprocessFlag(array &$variables) {
    $comment = $variables['flaggable'];
    $flag_counts = $this->flagCountManager->getEntityFlagCounts($comment);
    if (isset($flag_counts['os2loop_upvote_upvote_button'])) {
      $variables['upvotes'] = intval($flag_counts['os2loop_upvote_upvote_button']);
    }
    // Clear cache, so the UI reflects changes in top comment.
    Cache::invalidateTags($comment->getCacheTags());
  }

  /**
   * Implements hook_preprocess_comment().
   *
   * Add styling class to top comment.
   */
  public function preprocessComment(array &$variables) {
    if (isset($variables['elements']['#top'])) {
      $variables['attributes']['class'][] = 'top-comment';
      $variables['attributes']['class'][] = $variables['elements']['#top'];
    }
  }

}

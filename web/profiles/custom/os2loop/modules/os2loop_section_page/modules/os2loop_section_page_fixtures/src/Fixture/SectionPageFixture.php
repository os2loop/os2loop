<?php

namespace Drupal\os2loop_section_page_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Page fixture.
 *
 * @package Drupal\os2loop_section_page_fixtures\Fixture
 */
class SectionPageFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $page = Node::create([
      'type' => 'os2loop_section_page',
      'title' => 'The first section page',
    ]);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_section_page_views_refer',
      'os2loop_section_page_view_header' => 'Popular content',
      'os2loop_section_page_view_text' => [
        'value' => <<<'BODY'
    This is the most read content.
    BODY,
        'format' => 'os2loop_section_page',
      ],
      'os2loop_section_page_view' => [
        'target_id' => 'os2loop_section_page_most_viewed',
        'display_id' => 'block_1',
      ],
    ]);
    $paragraph->save();
    $page->get('os2loop_section_page_paragraph')->appendItem($paragraph);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_section_page_views_refer',
      'os2loop_section_page_view_header' => 'Latest news',
      'os2loop_section_page_view_text' => [
        'value' => <<<'BODY'
    See what's going on.
    BODY,
        'format' => 'os2loop_section_page',
      ],
      'os2loop_section_page_view' => [
        'target_id' => 'os2loop_section_page_newest_content',
        'display_id' => 'block_1',
      ],
    ]);
    $paragraph->save();
    $page->get('os2loop_section_page_paragraph')->appendItem($paragraph);

    $this->setReference($page->getType() . ':' . $page->getTitle(), $page);
    $page->save();

    $page = Node::create([
      'type' => 'os2loop_section_page',
      'title' => 'Another section page',
    ]);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_section_page_views_refer',
      'os2loop_section_page_view_header' => 'Popular content',
      'os2loop_section_page_view_text' => [
        'value' => <<<'BODY'
    This is the most read content.
    BODY,
        'format' => 'os2loop_section_page',
      ],
      'os2loop_section_page_view' => [
        'target_id' => 'os2loop_section_page_most_viewed',
        'display_id' => 'block_1',
      ],
    ]);
    $paragraph->save();
    $page->get('os2loop_section_page_paragraph')->appendItem($paragraph);

    $this->setReference($page->getType() . ':' . $page->getTitle(), $page);
    $page->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_section_page'];
  }

}

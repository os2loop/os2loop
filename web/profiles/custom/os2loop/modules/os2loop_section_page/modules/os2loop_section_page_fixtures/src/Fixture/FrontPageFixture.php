<?php

namespace Drupal\os2loop_section_page_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Front page fixture.
 *
 * @package Drupal\os2loop_section_page_fixtures\Fixture
 */
class FrontPageFixture extends AbstractFixture implements FixtureGroupInterface {
  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $page = Node::create([
      'type' => 'os2loop_section_page',
      'title' => 'Front page',
    ]);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_section_page_views_refer',
      'os2loop_section_page_view_header' => 'This is the front page',
      'os2loop_section_page_view_text' => [
        'value' => <<<'BODY'
    <p>Welcome to the site.</p>
    BODY,
        'format' => 'os2loop_section_page',
      ],
    ]);
    $paragraph->save();
    $page->get('os2loop_section_page_paragraph')->appendItem($paragraph);

    $paragraph = Paragraph::create([
      'type' => 'os2loop_section_page_views_refer',
      'os2loop_section_page_view_header' => 'Search',
      'os2loop_section_page_block' => [
        'plugin_id' => 'views_exposed_filter_block:os2loop_search_db-page_search_form',
      ],
    ]);
    $paragraph->save();
    $page->get('os2loop_section_page_paragraph')->appendItem($paragraph);
    $page->save();

    // Set page as the site front page.
    $config = $this->configFactory->getEditable('system.site');
    $pageConfig = $config->get('page');
    $pageConfig['front'] = $page->toUrl()->toString();
    $config->set('page', $pageConfig);
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_section_page'];
  }

}

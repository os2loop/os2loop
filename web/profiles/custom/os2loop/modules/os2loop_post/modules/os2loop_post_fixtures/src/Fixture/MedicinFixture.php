<?php

namespace Drupal\os2loop_post_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\os2loop_taxonomy_fixtures\Fixture\SubjectFixture;

/**
 * Medicin post fixture.
 *
 * Posts using the "Medicin" subject and having the word "Medicin" in the
 * content.
 *
 * @package Drupal\os2loop_post_fixtures\Fixture
 */
class MedicinFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    // A post with "medicin" in both content and subject.
    Node::create([
      'type' => 'os2loop_post',
      'title' => 'Medicin med det samme',
      'status' => Node::PUBLISHED,
      'os2loop_post_content' => [
        'value' => <<<'BODY'
<p>
årh, årh, årh, årh<br/>
åååårh jeg må ha' medicin medicin medicin med det samme<br/>
jeg må være rask så jeg kan tjene masser af penge<br/>
årh jeg må ha' medicin medicin medicin med det samme<br/>
jeg må være rask min skat forfalder om fjorten dage
</p>

<p>
Er det så mærkeligt at jeg har dårlige nerver<br/>
og at mit hoved er blevet hamret til skærver<br/>
når jeg nu er blevet pengeforladt<br/>
hvergang jeg har betalt min skat<br/>
først ta'r jeg piller for at sove om natten<br/>
og så ta'r jeg piller for at vågne om morgenen<br/>
piller må skal jeg ha'<br/>
jeg ta'r en snes hver eneste dag<br/>
de koster penge<br/>
og dem må jeg tjene<br/>
tanken om det gi'r mig voldsom migræne<br/>
pillerne jeg ta'r for det<br/>
sløver mig så vidt jeg ve'<br/>
så må jeg ta' et par piller der kvikker<br/>
og så er jeg lysvågen hver gang jeg ligger<br/>
og så kan enhver forstå<br/>
at jeg må ha' no'et at sove på<br/>
ingen tager piller bare for sin fornøjelse<br/>
jeg må ta' nogen der hjælper på min fordøjelse<br/>
den slags piller gør mig træt<br/>
for trætheden tager jeg så en tablet<br/>
så er jeg vågen og så må jeg prøve<br/>
nogen der kan dulme og døve og sløve<br/>
når jeg så er slumret hen<br/>
så ska' jeg op og på'en igen
</p>

<p>
årh jeg må ha' medicin medicin medicin med det samme<br/>
jeg må være rask så jeg kan tjene masser af penge<br/>
årh jeg må ha' medicin medicin medicin med det samme<br/>
jeg må være rask min skat forfalder om fjorten dage
</p>
BODY,
        'format' => 'os2loop_post',
      ],
      'os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Medicin')->id(),
      ],
      'os2loop_shared_profession' => [
        'target_id' => $this->getReference('os2loop_profession:Andet')->id(),
      ],
    ])->save();

    // A post with "medicin" in content only.
    Node::create([
      'type' => 'os2loop_post',
      'title' => 'Medicin med det samme',
      'status' => Node::PUBLISHED,
      'os2loop_post_content' => [
        'value' => <<<'BODY'
<p>
årh, årh, årh, årh<br/>
åååårh jeg må ha' medicin medicin medicin med det samme<br/>
jeg må være rask så jeg kan tjene masser af penge<br/>
årh jeg må ha' medicin medicin medicin med det samme<br/>
jeg må være rask min skat forfalder om fjorten dage
</p>

<p>
Er det så mærkeligt at jeg har dårlige nerver<br/>
og at mit hoved er blevet hamret til skærver<br/>
når jeg nu er blevet pengeforladt<br/>
hvergang jeg har betalt min skat<br/>
først ta'r jeg piller for at sove om natten<br/>
og så ta'r jeg piller for at vågne om morgenen<br/>
piller må skal jeg ha'<br/>
jeg ta'r en snes hver eneste dag<br/>
de koster penge<br/>
og dem må jeg tjene<br/>
tanken om det gi'r mig voldsom migræne<br/>
pillerne jeg ta'r for det<br/>
sløver mig så vidt jeg ve'<br/>
så må jeg ta' et par piller der kvikker<br/>
og så er jeg lysvågen hver gang jeg ligger<br/>
og så kan enhver forstå<br/>
at jeg må ha' no'et at sove på<br/>
ingen tager piller bare for sin fornøjelse<br/>
jeg må ta' nogen der hjælper på min fordøjelse<br/>
den slags piller gør mig træt<br/>
for trætheden tager jeg så en tablet<br/>
så er jeg vågen og så må jeg prøve<br/>
nogen der kan dulme og døve og sløve<br/>
når jeg så er slumret hen<br/>
så ska' jeg op og på'en igen
</p>

<p>
årh jeg må ha' medicin medicin medicin med det samme<br/>
jeg må være rask så jeg kan tjene masser af penge<br/>
årh jeg må ha' medicin medicin medicin med det samme<br/>
jeg må være rask min skat forfalder om fjorten dage
</p>
BODY,
        'format' => 'os2loop_post',
      ],
      'os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Diverse')->id(),
      ],
      'os2loop_shared_profession' => [
        'target_id' => $this->getReference('os2loop_profession:Andet')->id(),
      ],
      'created' => (new \DateTimeImmutable('2001-01-01'))->getTimestamp(),
      'changed' => (new \DateTimeImmutable('2001-01-03'))->getTimestamp(),
    ])->save();

    // A post with "medicin" in content only.
    Node::create([
      'type' => 'os2loop_post',
      'title' => 'A post about something',
      'status' => Node::PUBLISHED,
      'os2loop_post_content' => [
        'value' => <<<'BODY'
<p>
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
</p>
BODY,
        'format' => 'os2loop_post',
      ],
      'os2loop_shared_subject' => [
        'target_id' => $this->getReference('os2loop_subject:Medicin')->id(),
      ],
      'os2loop_shared_profession' => [
        'target_id' => $this->getReference('os2loop_profession:Andet')->id(),
      ],
      'created' => (new \DateTimeImmutable('2001-01-02'))->getTimestamp(),
      'changed' => (new \DateTimeImmutable('2001-01-02'))->getTimestamp(),
    ])->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      SubjectFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_post'];
  }

}

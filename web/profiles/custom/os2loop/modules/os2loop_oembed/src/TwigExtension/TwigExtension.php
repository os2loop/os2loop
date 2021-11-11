<?php

namespace Drupal\os2loop_oembed\TwigExtension;

use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\media\OEmbed\UrlResolverInterface;
use GuzzleHttp\ClientInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\os2loop_settings\Settings;

/**
 * Custom twig functions.
 */
class TwigExtension extends AbstractExtension {
  use StringTranslationTrait;

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The url resolver service.
   *
   * @var \Drupal\media\OEmbed\UrlResolverInterface
   */
  protected $urlResolver;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The settings.
   *
   * @var \Drupal\os2loop_settings\Settings
   */
  protected $settings;

  /**
   * TwigExtension constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client.
   * @param \Drupal\media\OEmbed\UrlResolverInterface $urlResolver
   *   The Url resolver service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\os2loop_settings\Settings $settings
   *   Loop settings.
   */
  public function __construct(ClientInterface $http_client, UrlResolverInterface $urlResolver, MessengerInterface $messenger, Settings $settings) {
    $this->httpClient = $http_client;
    $this->urlResolver = $urlResolver;
    $this->messenger = $messenger;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('create_video', [$this, 'createVideo'], ['is_safe' => ['html']]),
    ];
  }

  /**
   * Render a video from an embed url or iframe.
   *
   * @param array $text
   *   The input text that the filter was applied to.
   *
   * @return string
   *   The rendered html.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   *   An exception if guzzle fails.
   * @throws \Exception
   *   The general exception.
   */
  public function createVideo(array $text): string {
    // Set string depending on field type.
    $string = isset($text['#plain_text']) ? $text['#plain_text'] : $text['#context']['value'];
    if (!empty($this->findIframe($string))) {
      $string = $this->handleIframe($string);
    }
    $videoArray = $this->createVideoFromUrl($string);
    $videoArray = $this->applyCookieConsent($videoArray);

    return $videoArray['iframe'];
  }

  /**
   * List of allowed providers.
   *
   * @var array
   *   The allowed providers.
   */
  private const ALLOWED_PROVIDERS = [
    'vimeo.com' => [
      'type' => 'Oembed',
      'provider' => 'Vimeo',
      'requiredCookies' => 'cookie_cat_statistic',
    ],
    'player.skyfish.com' => [
      'type' => 'custom',
      'requiredCookies' => NULL,
    ],
    'media.videotool.dk' => [
      'type' => 'custom',
      'requiredCookies' => NULL,
    ],
    'web.microsoftstream.com' => [
      'type' => 'Oembed',
      'provider' => 'Microsoft Stream',
      'requiredCookies' => NULL,
    ],

  ];

  /**
   * Search a text for an iframe.
   *
   * @param string $text
   *   The text to search for.
   *
   * @return array
   *   An array representing a match.
   */
  private function findIframe($text): array {
    preg_match('/(?:<iframe[^>]*)(?:(?:\/>)|(?:>.*?<\/iframe>))/', $text, $matches);
    return $matches;
  }

  /**
   * Create an array containing video information.
   *
   * @param string $text
   *   The text input to create video from.
   *
   * @return array
   *   The resulting video array.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   *   Exception if oembed fails.
   */
  private function createVideoFromUrl($text): array {
    $video = [];
    if (filter_var($text, FILTER_VALIDATE_URL)) {
      $url = parse_url($text);
      if (array_key_exists($url['host'], self::ALLOWED_PROVIDERS)) {
        $video['host'] = $url['host'];
        // Use oembed to create iframe if possible.
        if ('Oembed' === self::ALLOWED_PROVIDERS[$url['host']]['type']) {
          try {
            $url = $this->urlResolver->getResourceUrl($text);
            $request = $this->httpClient->request('GET', $url);
            $status = $request->getStatusCode();
            if (200 == $status) {
              $video['oembed'] = Json::decode($request->getBody()->getContents());
              $video['iframe'] = $video['oembed']['html'];
            }
          }
          catch (\Exception $e) {
            if ('No matching provider found.' === $e->getMessage()) {
              $this->messenger->addWarning($this->t('Could not build video.'));
              $video = [];
            }
          }
        }
        // If oembed is not an option create iframe from a url.
        elseif ('custom' === self::ALLOWED_PROVIDERS[$url['host']]['type']) {
          $video['custom']['src'] = $text;
          $video['iframe'] = '<iframe src="' . $text . '"></iframe>';
        }
      }
    }

    return $video;
  }

  /**
   * Get source from iframe code.
   *
   * @param string $text
   *   The iframe code.
   *
   * @return string
   *   The iframe source.
   */
  private function handleIframe(string $text): string {
    preg_match('/src="([^"]+)"/', $text, $matches);
    return $matches[1];
  }

  /**
   * Change iframe to support cookie consent.
   *
   * @param array $videoArray
   *   The video array.
   *
   * @return array
   *   The altered array.
   */
  private function applyCookieConsent(array $videoArray): array {
    $config = $this->settings->getConfig('os2loop_cookies.settings');
    $cookieInformationScriptCode = $config->get('os2loop_cookie_information_script');
    $requiredCookies = self::ALLOWED_PROVIDERS[$videoArray['host']]['requiredCookies'];
    if (!empty($cookieInformationScriptCode) && !empty($requiredCookies)) {
      if (isset($videoArray['iframe'])) {
        $videoArray['iframe'] = str_replace(' src="', ' src="" data-category-consent="' . $requiredCookies . '" data-consent-src="', $videoArray['iframe']);
      }
    }
    return $videoArray;
  }

}

<?php

namespace Drupal\calendar_link\Twig;

use Drupal\calendar_link\CalendarLinkException;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Spatie\CalendarLinks\Exceptions\InvalidLink;
use Spatie\CalendarLinks\Link;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extensions class for the `calendar_link` and `calender_links` functions.
 *
 * @package Drupal\calendar_link\Twig
 */
class CalendarLinkTwigExtension extends AbstractExtension {
  use StringTranslationTrait;

  /**
   * Available link types (generators).
   *
   * @var array
   *
   * @see \Spatie\CalendarLinks\Link
   */
  protected static array $types = [
    'google' => 'Google',
    'ics' => 'iCal',
    'yahoo' => 'Yahoo!',
    'webOutlook' => 'Outlook.com',
  ];

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('calendar_link', [$this, 'calendarLink']),
      new TwigFunction('calendar_links', [$this, 'calendarLinks']),
    ];
  }

  /**
   * Create a calendar link.
   *
   * @param string $type
   *   Generator key to use for link building.
   * @param string $title
   *   Calendar entry title.
   * @param \Drupal\Core\Datetime\DrupalDateTime|\DateTime $from
   *   Calendar entry start date and time.
   * @param \Drupal\Core\Datetime\DrupalDateTime|\DateTime $to
   *   Calendar entry end date and time.
   * @param bool $all_day
   *   Indicator for an "all day" calendar entry.
   * @param string $description
   *   Calendar entry description.
   * @param string $address
   *   Calendar entry address.
   *
   * @return string
   *   URL for the specific calendar type.
   */
  public function calendarLink($type, $title, $from, $to, $all_day = FALSE, $description = '', $address = ''): string {
    if (!isset(self::$types[$type])) {
      throw new CalendarLinkException('Invalid calendar link type.');
    }

    try {
      if ($from instanceof DrupalDateTime) {
        $from = $from->getPhpDateTime();
      }
      if ($to instanceof DrupalDateTime) {
        $to = $to->getPhpDateTime();
      }

      $link = Link::create($title, $from, $to, $all_day);
    }
    catch (InvalidLink $e) {
      throw new CalendarLinkException('Invalid calendar link data.');
    }

    if ($description) {
      $link->description($description);
    }

    if ($address) {
      $link->address($address);
    }

    return $link->{$type}();
  }

  /**
   * Create links for all calendar types.
   *
   * @param string $title
   *   Calendar entry title.
   * @param \Drupal\Core\Datetime\DrupalDateTime|\DateTime $from
   *   Calendar entry start date and time.
   * @param \Drupal\Core\Datetime\DrupalDateTime|\DateTime $to
   *   Calendar entry end date and time.
   * @param bool $all_day
   *   Indicator for an "all day" calendar entry.
   * @param string $description
   *   Calendar entry description.
   * @param string $address
   *   Calendar entry address.
   *
   * @return array
   *   - type_key: Machine key for the calendar type.
   *   - type_name: Human-readable name for the calendar type.
   *   - url: URL for the specific calendar type.
   */
  public function calendarLinks($title, $from, $to, $all_day = FALSE, $description = '', $address = ''): array {
    $links = [];

    foreach (self::$types as $type => $name) {
      $links[$type] = [
        'type_key' => $type,
        'type_name' => $name,
        'url' => $this->calendarLink($type, $title, $from, $to, $all_day, $description, $address),
      ];
    }

    return $links;
  }

}

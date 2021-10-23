<?php

namespace Drupal\calendar_link\Twig;

use Drupal\calendar_link\CalendarLinkException;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList;
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
  protected static $types = [
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
   * All data parameters accept multiple types of data and will attempt to get
   * the relevant information from e.g. field instances or content arrays.
   *
   * @param string $type
   *   Generator key to use for link building.
   * @param mixed $title
   *   Calendar entry title.
   * @param mixed $from
   *   Calendar entry start date and time.
   * @param mixed $to
   *   Calendar entry end date and time.
   * @param mixed $all_day
   *   Indicator for an "all day" calendar entry.
   * @param mixed $description
   *   Calendar entry description.
   * @param mixed $address
   *   Calendar entry address.
   *
   * @return string
   *   URL for the specific calendar type.
   */
  public function calendarLink(string $type, $title, $from, $to, $all_day = FALSE, $description = '', $address = ''): string {
    if (!isset(self::$types[$type])) {
      throw new CalendarLinkException('Invalid calendar link type.');
    }

    try {
      $link = Link::create(
        $this->getString($title),
        $this->getDateTime($from),
        $this->getDateTime($to),
        $this->getBoolean($all_day)
      );
    }
    catch (InvalidLink $e) {
      throw new CalendarLinkException('Invalid calendar link data.');
    }

    if ($description) {
      $link->description($this->getString($description));
    }

    if ($address) {
      $link->address($this->getString($address));
    }

    return $link->{$type}();
  }

  /**
   * Create links for all calendar types.
   *
   * All parameters accept multiple types of data and will attempt to get the
   * relevant information from e.g. field instances or content arrays.
   *
   * @param mixed $title
   *   Calendar entry title.
   * @param mixed $from
   *   Calendar entry start date and time. This value can be various DateTime
   *   types, a content field array, or a field.
   * @param mixed $to
   *   Calendar entry end date and time. This value can be various DateTime
   *   types, a content field array, or a field.
   * @param mixed $all_day
   *   Indicator for an "all day" calendar entry.
   * @param mixed $description
   *   Calendar entry description.
   * @param mixed $address
   *   Calendar entry address.
   *
   * @return array
   *   - type_key: Machine key for the calendar type.
   *   - type_name: Human-readable name for the calendar type.
   *   - url: URL for the specific calendar type.
   *
   * @see \Drupal\calendar_link\Twig\CalendarLinkTwigExtension::calendarLink()
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

  /**
   * Gets a boolean value from various types of input.
   *
   * @param mixed $data
   *   A value with a boolean value.
   *
   * @return bool
   *   Boolean from data.
   *
   * @throws \Drupal\calendar_link\CalendarLinkException
   */
  private function getBoolean($data): bool {
    if (is_bool($data)) {
      return $data;
    }

    try {
      $data = $this->getString($data);
      return (bool) $data;
    }
    catch (CalendarLinkException $e) {
      throw new CalendarLinkException('Could not get valid boolean from input.');
    }
  }

  /**
   * Gets a string value from various types of input.
   *
   * @param mixed $data
   *   A value with a string.
   *
   * @return string
   *   String from data.
   *
   * @throws \Drupal\calendar_link\CalendarLinkException
   */
  private function getString($data): string {
    // Content field array. E.g. `label`.
    if (is_array($data) && isset($data['#items'])) {
      $data = $data['#items'];
    }

    // Drupal field instance. E.g. `node.title`.
    if ($data instanceof FieldItemListInterface) {
      $data = $data->getString();
    }

    if (is_string($data)) {
      return $data;
    }

    throw new CalendarLinkException('Could not get valid string from input.');
  }

  /**
   * Gets a PHP \DateTime instance from various types of input.
   *
   * @param mixed $date
   *   A value with \DateTime data.
   *
   * @return \DateTime
   *   The \DateTime instance.
   *
   * @throws \Drupal\calendar_link\CalendarLinkException
   */
  private function getDateTime($date): \DateTime {
    // Content field array. E.g. `content.field_start`.
    if (is_array($date) && isset($date['#items'])) {
      $date = $date['#items'];
    }

    // Drupal field instance. E.g. `node.field_start`.
    if ($date instanceof DateTimeFieldItemList) {
      $date = $date->date;
    }

    // Drupal date time. E.g. `node.field_start.date`.
    if ($date instanceof DrupalDateTime) {
      $date = $date->getPhpDateTime();
    }

    if ($date instanceof \DateTime) {
      return $date;
    }

    throw new CalendarLinkException('Could not get valid \DateTime object from input.');
  }

}

<?php

namespace Drupal\Tests\calendar_link\Kernel;

use Drupal\calendar_link\Twig\CalendarLinkTwigExtension;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Twig extensions.
 *
 * @group calendar_link
 */
class CalendarLinkTwigExtensionTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['calendar_link'];

  /**
   * Tests that Twig extension loads appropriately.
   */
  public function testTwigExtensionLoaded() {
    $twig_service = \Drupal::service('twig');
    $extension = $twig_service->getExtension(CalendarLinkTwigExtension::class);
    $this->assertEquals(
      get_class($extension),
      CalendarLinkTwigExtension::class,
      'Calendar Link extension loaded successfully.'
    );
  }

  /**
   * Tests that the Twig extension functions are registered properly.
   */
  public function testFunctionsRegistered() {
    /** @var \Twig_SimpleFunction[] $functions */
    $registered_functions = \Drupal::service('twig')
      ->getFunctions();

    $functions = ['calendar_link', 'calendar_links'];

    foreach ($functions as $name) {
      $function = $registered_functions[$name];
      $this->assertTrue($function instanceof \Twig_SimpleFunction);
      $this->assertEquals($function->getName(), $name);
      is_callable($function->getCallable(), TRUE, $callable);
    }
  }

  /**
   * Tests the "calendar_link" Twig function.
   */
  public function testCalendarLinkFunction() {
    $template = "{% set title = 'title'|t %}{% set startDate = date('2019-02-24 10:00', 'Etc/UTC') %}{% set endDate = date('2019-02-24 12:00', 'Etc/UTC') %}{% set link = calendar_link('ics', title, startDate, endDate, false, 'description', 'location') %}<a href=\"{{ link }}\">Add to calendar</a>";
    $expected_template_output = '<a href="data:text/calendar;charset=utf8;base64,QkVHSU46VkNBTEVOREFSDQpWRVJTSU9OOjIuMA0KQkVHSU46VkVWRU5UDQpVSUQ6YTc4ZDRjM2NjNzA3YzRjZGU3NjBiYWQzZmJmZjhlYTENClNVTU1BUlk6dGl0bGUNCkRUU1RBUlQ7VFpJRD1FdGMvVVRDOjIwMTkwMjIzVDIzMDAwMA0KRFRFTkQ7VFpJRD1FdGMvVVRDOjIwMTkwMjI0VDAxMDAwMA0KREVTQ1JJUFRJT046ZGVzY3JpcHRpb24NCkxPQ0FUSU9OOmxvY2F0aW9uDQpFTkQ6VkVWRU5UDQpFTkQ6VkNBTEVOREFS">Add to calendar</a>';

    /** @var \Drupal\Core\Template\TwigEnvironment $environment */
    $environment = \Drupal::service('twig');

    $output = (string) $environment->renderInline($template);
    $this->assertEquals($expected_template_output, $output);
  }

  /**
   * Tests the "calendar_links" Twig function.
   */
  public function testCalendarLinksFunction() {
    $template = "{% set title = 'title'|t %}{% set startDate = date('2019-02-24 10:00', 'Etc/UTC') %}{% set endDate = date('2019-02-24 12:00', 'Etc/UTC') %}{% set links = calendar_links(title, startDate, endDate, false, 'description', 'address') %}{% for link in links %}<a href=\"{{ link.url }}\" class=\"calendar-type-{{ link.type_key }}\">Add to {{ link.type_name }}</a>{% endfor %}";
    $expected_template_output = '<a href="https://calendar.google.com/calendar/render?action=TEMPLATE&amp;dates=20190223T230000Z/20190224T010000Z&amp;text=title&amp;details=description&amp;location=address" class="calendar-type-google">Add to Google</a><a href="data:text/calendar;charset=utf8;base64,QkVHSU46VkNBTEVOREFSDQpWRVJTSU9OOjIuMA0KQkVHSU46VkVWRU5UDQpVSUQ6ODdiOGU5OTllNjUzYWNkZmZmN2Y2Yzc4MmQ0YWE5MGUNClNVTU1BUlk6dGl0bGUNCkRUU1RBUlQ7VFpJRD1FdGMvVVRDOjIwMTkwMjIzVDIzMDAwMA0KRFRFTkQ7VFpJRD1FdGMvVVRDOjIwMTkwMjI0VDAxMDAwMA0KREVTQ1JJUFRJT046ZGVzY3JpcHRpb24NCkxPQ0FUSU9OOmFkZHJlc3MNCkVORDpWRVZFTlQNCkVORDpWQ0FMRU5EQVI=" class="calendar-type-ics">Add to iCal</a><a href="https://calendar.yahoo.com/?v=60&amp;view=d&amp;type=20&amp;ST=20190223T230000Z&amp;ET=20190224T010000Z&amp;TITLE=title&amp;DESC=description&amp;in_loc=address" class="calendar-type-yahoo">Add to Yahoo!</a><a href="https://outlook.live.com/calendar/deeplink/compose?path=/calendar/action/compose&amp;rru=addevent&amp;startdt=2019-02-23T23:00:00Z&amp;enddt=2019-02-24T01:00:00Z&amp;subject=title&amp;body=description&amp;location=address" class="calendar-type-webOutlook">Add to Outlook.com</a>';

    /** @var \Drupal\Core\Template\TwigEnvironment $environment */
    $environment = \Drupal::service('twig');

    $output = (string) $environment->renderInline($template);
    $this->assertEquals($expected_template_output, $output);
  }

}

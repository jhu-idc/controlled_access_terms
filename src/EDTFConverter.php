<?php

namespace Drupal\controlled_access_terms;

use Drupal\rdf\CommonDataConverter;
use Datetime;

/**
 * {@inheritdoc}
 */
class EDTFConverter extends CommonDataConverter {

  /**
   * Northern hemisphere season map.
   *
   * @var array
   */
  private $seasonMapNorth = [
  // Spring => March.
    '21' => '03',
  // Summer => June.
    '22' => '06',
  // Autumn => September.
    '23' => '09',
  // Winter => December.
    '24' => '12',
  ];

  /**
   * Southern hemisphere season map.
   *
   * (Currently unused until a config for this is established.)
   *
   * @var array
   */
  private $seasonMapSouth = [
  // Spring => September.
    '21' => '03',
  // Summer => December.
    '22' => '06',
  // Autumn => March.
    '23' => '09',
  // Winter => June.
    '24' => '12',
  ];

  /**
   * Converts EDTF values into ISO values.
   */
  public static function dateIso8601Value(string $edtf, bool $latest = FALSE) {
    $dates = explode('/', $edtf);
    $date = ($latest) ? end($dates) : $dates[0];

    // Strip approximations/uncertainty.
    $date = str_replace(['?', '~'], '', $date);

    $date_parts = explode('-', $date, 3);

    // Replace unspecified.
    if ($latest) {
      // Zero-Year in decade/century.
      $date_parts[0] = str_replace('u', '9', $date_parts[0]);
      // Month.
      if (count($date_parts) > 1) {
        $date_parts[1] = str_replace('uu', '12', $date_parts[1]);
      }
      // Day (find first day then have DateTime give us the last day.)
      if (count($date_parts) > 2) {
        $date_parts[2] = str_replace('uu', '01', $date_parts[2]);
      }
      $d = new DateTime(implode('-', $date_parts));
      $date_parts = explode('-', $d->format('Y-m-t'), 3);

    }
    else {
      // Zero-Year in decade/century.
      $date_parts[0] = str_replace('u', '0', $date_parts[0]);

      // Month.
      if (count($date_parts) > 1) {
        $date_parts[1] = str_replace('uu', '01', $date_parts[1]);
      }

      // Day.
      if (count($date_parts) > 2) {
        $date_parts[2] = str_replace('uu', '01', $date_parts[2]);
      }
    }

    // Seasons map.
    if (in_array($date_parts[1], ['21', '22', '23', '24'])) {
      // TODO: Make hemisphere seasons configurable.
      $season_mapping = $seasonMapNorth;
      $date_parts[1] = $season_mapping[$month];
      $date = implode('-', $date_parts);
    }

    return $date;

  }

}

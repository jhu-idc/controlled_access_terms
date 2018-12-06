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
   * Converts an EDTF text field into an ISO 8601 timestamp string.
   *
   * It assumes the earliest valid date for approximations and intervals.
   *
   * @param array $data
   *   The array containing the 'value' element.
   *
   * @return string
   *   Returns the ISO 8601 timestamp.
   */
  public static function dateIso8601Value($data) {
    return(iso8601Value($data['value']));
  }

  /**
   * Converts EDTF values into ISO values.
   */
  public static function iso8601Value(string $edtf, bool $latest = FALSE) {
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
      } else {
        $date_parts[1] = '12';
      }
      // Day
      if ((count($date_parts) < 3) || (strpos($date_parts[2], 'u') !== false) ) {
        // Day either missing or unspecified, set first so DateTime
        // can give us the last.
        $date_parts[2] = '01';
        $d = new DateTime(implode('-', $date_parts));
        $date_parts = explode('-', $d->format('Y-m-t'), 3);
      }
    } else {
      // Zero-Year in decade/century.
      $date_parts[0] = str_replace('u', '0', $date_parts[0]);

      // Month.
      if (count($date_parts) > 1) {
        $date_parts[1] = str_replace('uu', '01', $date_parts[1]);
      } else {
        $date_parts[1] = '01';
      }

      // Day.
      if (count($date_parts) > 2) {
        $date_parts[2] = str_replace('uu', '01', $date_parts[2]);
      } else {
        $date_parts[2] = '01';
      }
    }

    // Seasons map.
    if ( (count($date_parts) > 1) && (in_array($date_parts[1], ['21', '22', '23', '24']))) {
      // TODO: Make hemisphere seasons configurable.
      $season_mapping = $seasonMapNorth;
      $date_parts[1] = $season_mapping[$month];
    }

    return implode('-', $date_parts);

  }

}

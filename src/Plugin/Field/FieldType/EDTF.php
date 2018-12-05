<?php

namespace Drupal\controlled_access_terms\Plugin\Field\FieldType;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\controlled_access_terms\EDTFComputed;

/**
 * Plugin implementation of the 'edtf' field type.
 *
 * Validates text values for compliance with EDTF 1.0, level 1.
 * http://www.loc.gov/standards/datetime/pre-submission.html.
 *
 * It mimics Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem but
 * doesn't extend it.
 *
 * // TODO: maybe some day support level 2.
 *
 * @FieldType(
 *   id = "edtf",
 *   label = @Translation("Extended Date Time Format, level 1"),
 *   description = @Translation("Stores a date or date range compling to the the Library of Congress Extended Date Time Format."),
 *   default_widget = "edtf_default",
 *   default_formatter = "edtf_default",
 * )
 */
class EDTF extends DateRangeItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['value'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Start date value'))
      ->setComputed(TRUE)
      ->setClass(EDTFComputed::class)
      ->setSetting('latest', FALSE);

    $properties['end_value'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('End date value'))
      ->setComputed(TRUE)
      ->setClass(EDTFComputed::class)
      ->setSetting('latest', TRUE);

    $properties['edtf'] = DataDefinition::create('string')
      ->setLabel(t('EDTF Value'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['edtf'] = [
      'description' => 'The text representation.',
      'type' => 'varchar',
      'length' => 128,
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $edtf = $this->get('edtf')->getValue();
    return ($edtf === NULL || $edtf === '');
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name, $notify = TRUE) {
    // Enforce that the computed date is recalculated.
    if ($property_name == 'edtf') {
      $this->value = NULL;
      $this->end_value = NULL;
    }

    parent::onChange($property_name, $notify);
  }

}

<?php

namespace Drupal\controlled_access_terms;

use Drupal\Core\TypedData\TypedData;

/**
 * A computed property for dates of extended date time field items.
 */
class EDTFComputed extends TypedData {

  /**
   * Cached value.
   *
   * @var string|null
   */
  protected $processed = NULL;

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue() {
    if ($this->processed !== NULL) {
      return $this->processed;
    }
    $edtf = $this->getParent()->edtf;
    if ($edtf === NULL || $edtf === '') {
      return NULL;
    }
    $latest = $this->definition->getSetting('latest');
    $this->processed = EDTFConverter::dateIso8601Value($edtf, $latest);
    return $this->processed;
  }

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::setValue().
   */
  public function setValue($value, $notify = TRUE) {
    $this->processed = $value;

    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

}

<?php
/**
 * Populator
 *
 * Populates the class with values, and fetches them
 * from various places
 */
namespace Former;

use Underscore\Types\Arrays;
use Underscore\Types\String;

class Populator
{

  /**
   * The populated values
   * @var array
   */
  private $values = array();

  /**
   * Build a new Populator instance with a
   * set of values to use
   *
   * @param array $values
   */
  public function __construct($values = array())
  {
    $this->values = $values;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////// INDIVIDUAL VALUES ////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Set the value of a particular field
   *
   * @param string $field The field's name
   * @param mixed  $value Its new value
   *
   * @return void
   */
  public function setValue($field, $value)
  {
    if (is_object($this->values)) {
      $this->values->$field = $value;
    } else {
      $this->values[$field] = $value;
    }
  }

  /**
   * Get the value of a field
   *
   * @param string $field The field's name
   *
   * @return mixed
   */
  public function getValue($field, $fallback = null)
  {
    // Plain array
    if (is_array($this->values)) {
      return Arrays::get($this->values, $field, $fallback);
    }

    // Transform the name into an array
    $value = $this->values;
    $field = String::contains($field, '.') ? explode('.', $field) : (array) $field;

    // Dive into the model
    foreach ($field as $relationship) {

      // Multiple results relation
      if (is_array($value)) {
        $me = $this;
        $value = Arrays::each($value, function($submodel) use ($me, $relationship, $fallback) {
          return $me->getAttributeFromModel($submodel, $relationship, $fallback);
        });

      // Get attribute from model
      } else {
        $value = $this->getAttributeFromModel($value, $relationship, $fallback);
        if ($value === $fallback) break;
      }

    }

    return $value;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// SWAPPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get all values
   *
   * @return mixed
   */
  public function getValues()
  {
    return $this->values;
  }

  /**
   * Replace the values array
   *
   * @param  mixed $values The new values
   *
   * @return void
   */
  public function setValues($values)
  {
    $this->values = $values;
  }

  /**
   * Reset the current values array
   *
   * @return void
   */
  public function reset()
  {
    $this->values = array();
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get an attribute from a model
   *
   * @param object $model     The model
   * @param string $attribute The attribute's name
   * @param string $fallback  Fallback value
   *
   * @return mixed
   */
  public function getAttributeFromModel($model, $attribute, $fallback)
  {
    if(
      isset($model->$attribute) or
      method_exists($model, 'get_'.$attribute) or
      method_exists($model, 'get'.ucfirst($attribute).'Attribute')) {
        return $model->$attribute;
    }

    return $fallback;
  }

}

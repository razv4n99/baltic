<?php

namespace Drupal\fuel_calculator\Service;

class FuelCalculatorService {

  /**
   * Calculate fuel spent and cost.
   */
  public function calculate($distance, $consumption, $price) {
    $fuel_spent = ($distance * $consumption) / 100;
    $fuel_cost = $fuel_spent * $price;
    return [
      'fuel_spent' => $fuel_spent,
      'fuel_cost' => $fuel_cost,
    ];
  }
}
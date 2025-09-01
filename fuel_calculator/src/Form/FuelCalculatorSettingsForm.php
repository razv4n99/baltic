<?php

namespace Drupal\fuel_calculator\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class FuelCalculatorSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['fuel_calculator.settings'];
  }

  public function getFormId() {
    return 'fuel_calculator_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('fuel_calculator.settings');

    $form['default_distance'] = [
      '#type' => 'number',
      '#title' => $this->t('Default distance (km)'),
      '#default_value' => $config->get('default_distance'),
      '#min' => 1,
      '#required' => TRUE,
    ];
    $form['default_consumption'] = [
      '#type' => 'number',
      '#title' => $this->t('Default fuel consumption (L/100km)'),
      '#default_value' => $config->get('default_consumption'),
      '#min' => 0.1,
      '#step' => 0.1,
      '#required' => TRUE,
    ];
    $form['default_price'] = [
      '#type' => 'number',
      '#title' => $this->t('Default fuel price (per L)'),
      '#default_value' => $config->get('default_price'),
      '#min' => 0.01,
      '#step' => 0.01,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('fuel_calculator.settings')
      ->set('default_distance', $form_state->getValue('default_distance'))
      ->set('default_consumption', $form_state->getValue('default_consumption'))
      ->set('default_price', $form_state->getValue('default_price'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
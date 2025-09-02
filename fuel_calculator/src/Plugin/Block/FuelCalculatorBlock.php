<?php

namespace Drupal\fuel_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Fuel Calculator block.
 *
 * @Block(
 * id = "fuel_calculator_block",
 * admin_label = @Translation("Fuel Calculator Block", context = "Fuel Calculator")
 * )
 */
class FuelCalculatorBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new FuelCalculatorBlock instance.
   *
   * @param array $configuration
   * A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   * The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   * The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   * The form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->formBuilder->getForm('\Drupal\fuel_calculator\Form\FuelCalculatorForm');
  }

}
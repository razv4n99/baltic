<?php

namespace Drupal\fuel_calculator\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\fuel_calculator\Service\FuelCalculatorService;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Provides a REST resource for fuel calculation.
 *
 * @RestResource(
 *   id = "fuel_calculator_resource",
 *   label = @Translation("Fuel Calculator Resource"),
 *   uri_paths = {
 *     "create" = "/api/fuel-calculator"
 *   }
 * )
 */
class FuelCalculatorResource extends ResourceBase {

  protected $calculator;
  protected $currentUser;
  protected $requestStack;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    $logger,
    FuelCalculatorService $calculator,
    AccountProxyInterface $current_user,
    RequestStack $request_stack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->calculator = $calculator;
    $this->currentUser = $current_user;
    $this->requestStack = $request_stack;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('fuel_calculator'),
      $container->get('fuel_calculator.calculator'),
      $container->get('current_user'),
      $container->get('request_stack')
    );
  }

  public function post($data) {
    $distance = $data['distance'] ?? null;
    $consumption = $data['consumption'] ?? null;
    $price = $data['price'] ?? null;

    if (!$distance || !$consumption || !$price) {
      return new JsonResponse(['error' => 'Missing parameters.'], 400);
    }

    $result = $this->calculator->calculate($distance, $consumption, $price);

    // Log calculation.
    $ip = $this->requestStack->getCurrentRequest()->getClientIp();
    $user = $this->currentUser->isAuthenticated() ? $this->currentUser->getAccountName() : 'Anonymous';
    \Drupal::logger('fuel_calculator')->notice('API Fuel calculation by @user (@ip): distance=@distance, consumption=@consumption, price=@price, spent=@spent, cost=@cost', [
      '@user' => $user,
      '@ip' => $ip,
      '@distance' => $distance,
      '@consumption' => $consumption,
      '@price' => $price,
      '@spent' => $result['fuel_spent'],
      '@cost' => $result['fuel_cost'],
    ]);

    return new JsonResponse($result);
  }
}
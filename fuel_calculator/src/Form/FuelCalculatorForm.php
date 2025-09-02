<?php

namespace Drupal\fuel_calculator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\fuel_calculator\Service\FuelCalculatorService;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class FuelCalculatorForm extends FormBase {

  protected $calculator;
  protected $requestStack;
  protected $loggerFactory;
  protected $currentUser;
  protected $configFactory;

  public function __construct(FuelCalculatorService $calculator, RequestStack $request_stack, LoggerChannelFactoryInterface $logger_factory, AccountProxyInterface $current_user, ConfigFactoryInterface $config_factory) {
    $this->calculator = $calculator;
    $this->requestStack = $request_stack;
    $this->loggerFactory = $logger_factory;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('fuel_calculator.calculator'),
      $container->get('request_stack'),
      $container->get('logger.factory'),
      $container->get('current_user'),
      $container->get('config.factory')
    );
  }

  public function getFormId() {
    return 'fuel_calculator_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'fuel-calculator-form';
    $form['#attached']['library'][] = 'fuel_calculator/fuel_calculator_styles';
    $config = $this->configFactory->get('fuel_calculator.settings');
    $request = $this->requestStack->getCurrentRequest();

    $distance = $form_state->getValue('distance') ?? $request->query->get('distance', $config->get('default_distance'));
    $consumption = $form_state->getValue('consumption') ?? $request->query->get('consumption', $config->get('default_consumption'));
    $price = $form_state->getValue('price') ?? $request->query->get('price', $config->get('default_price'));

    $form['distance'] = [
      '#type' => 'number',
      '#title' => $this->t('Distance travelled', [], ['context' => 'Fuel Calculator']),
      '#default_value' => $distance,
      '#min' => 1,
      '#required' => TRUE,
      '#description' => $this->t('km', [], ['context' => 'Fuel Calculator']),
    ];
    $form['consumption'] = [
      '#type' => 'number',
      '#title' => $this->t('Fuel consumption', [], ['context' => 'Fuel Calculator']),
      '#default_value' => $consumption,
      '#min' => 0.1,
      '#step' => 0.1,
      '#required' => TRUE,
      '#description' => $this->t('(L/100km)', [], ['context' => 'Fuel Calculator']),
    ];
    $form['price'] = [
      '#type' => 'number',
      '#title' => $this->t('Price per Liter', [], ['context' => 'Fuel Calculator']),
      '#default_value' => $price,
      '#min' => 0.01,
      '#step' => 0.01,
      '#required' => TRUE,
      '#description' => $this->t('EUR', [], ['context' => 'Fuel Calculator']),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Calculate', [], ['context' => 'Fuel Calculator']),
      '#button_type' => 'primary',
    ];
    $form['actions']['reset'] = [
      '#type' => 'link',
      '#title' => $this->t('Reset', [], ['context' => 'Fuel Calculator']),
      '#url' => Url::fromRoute('<current>'),
      '#attributes' => [
        'class' => ['button', 'button--secondary'],
      ],
    ];

    if ($result_data = $form_state->get('result')) {
        $form['result'] = $result_data;
    }

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach (['distance', 'consumption', 'price'] as $field) {
      if ($form_state->getValue($field) <= 0) {
        $form_state->setErrorByName($field, $this->t('@field must be greater than zero.', ['@field' => ucfirst($field)], ['context' => 'Fuel Calculator']));
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $distance = $form_state->getValue('distance');
    $consumption = $form_state->getValue('consumption');
    $price = $form_state->getValue('price');

    $result = $this->calculator->calculate($distance, $consumption, $price);

    $ip = $this->requestStack->getCurrentRequest()->getClientIp();
    $user = $this->currentUser->isAuthenticated() ? $this->currentUser->getAccountName() : 'Anonymous';
    $this->loggerFactory->get('fuel_calculator')->notice('Fuel calculation by @user (@ip): distance=@distance, consumption=@consumption, price=@price, spent=@spent, cost=@cost', [
      '@user' => $user,
      '@ip' => $ip,
      '@distance' => $distance,
      '@consumption' => $consumption,
      '@price' => $price,
      '@spent' => $result['fuel_spent'],
      '@cost' => $result['fuel_cost'],
    ]);

    $form_state->set('result', [
      '#theme' => 'fuel_calculator_result',
      '#fuel_spent' => $result['fuel_spent'],
      '#fuel_cost' => $result['fuel_cost'],
      '#prefix' => '<div class="fuel-calculator-result">',
      '#suffix' => '</div>',
    ]);
    
    $form_state->setRebuild();
  }
}
# Fuel Calculator Module

## Overview

The Fuel Calculator module provides a configurable fuel cost calculator for Drupal 10/11.  
It can be used as a standalone page, embedded as a block, and exposes a REST API for backend calculations.

---

## Features

- **Configurable defaults:** Set default values for distance, consumption, and price via the admin UI.
- **Config install:** Ships with default config for easy deployment.
- **Flexible usage:** Use as a page (`/fuel-calculator`) or as a block.
- **Backend calculations:** All calculations are performed server-side.
- **Input validation:** Ensures all fields are required and positive numbers.
- **Service-based logic:** All calculations are handled by a Drupal service.
- **Prefill via URL:** Calculator fields can be prefilled using URL query parameters.
- **REST API:** Exposes a POST endpoint for programmatic access.
- **Logging:** All calculations are logged with IP, user, input, and result.
- **Custom styling:** Includes a CSS file for a clean, responsive layout.
- **Reset button:** Resets the form to default values.

---

## Installation

1. Copy the `fuel_calculator` module to `web/modules/custom/`.
2. Enable the module via the Drupal admin UI or with Drush:
   ```
   drush en fuel_calculator
   ```
3. (Optional) Place the "Fuel Calculator Block" in any region via the Block Layout UI.
4. The module requires the following dependencies:
   - Core: `rest`, `serialization`: drush en rest serialization
   - Contrib: `restui` (for REST admin UI): composer require drupal/restui & drush en restui

---

## Configuration

- Go to **Configuration > Fuel Calculator settings** (`/admin/config/fuel-calculator/settings`) to set default values for distance, consumption, and price.

---

## Usage

### Calculator Page

- Visit `/fuel-calculator` to use the calculator as a standalone page.

### Calculator Block

- Place the "Fuel Calculator Block" in any region via **Structure > Block layout**.

### Prefill via URL

You can prefill the calculator form using query parameters, e.g.:

```
/fuel-calculator?distance=200&consumption=6.5&price=2.10
```

---

## REST API

### Endpoint

- **POST** `/api/fuel-calculator`

### Request

- Content-Type: `application/json`
- Body:
  ```json
  {
    "distance": 100,
    "consumption": 5,
    "price": 1.5
  }
  ```

### Response

```json
{
  "fuel_spent": 5,
  "fuel_cost": 7.5
}
```

### Authentication

- By default, the API requires authentication (cookie or basic_auth).
- To test with JavaScript in the browser, fetch a CSRF token from `/session/token` and include it as the `X-CSRF-Token` header.

#### Example JS (in browser console):

```js
fetch('/session/token')
  .then(r => r.text())
  .then(token => {
    fetch('/api/fuel-calculator', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
      },
      body: JSON.stringify({distance: 100, consumption: 5, price: 1.5}),
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(console.log);
  });
```

#### Example cURL:

```sh
curl -X POST http://your-site/api/fuel-calculator \
  -H "Content-Type: application/json" \
  -d '{"distance":100,"consumption":5,"price":1.5}'
```

---

## Logging

- All calculations (form and API) are logged in **Reports > Recent log messages** (`/admin/reports/dblog`), including IP, username, input values, and results.

---

## Styling

- The module includes `css/fuel_calculator.css` for custom styling.
- You can further customize the look by editing this file.

---

## Coding Standards

- The module uses PSR-4, dependency injection, and follows Drupal 10/11 best practices.

---

## File Structure

```
fuel_calculator/
  css/
    fuel_calculator.css
  src/
    Form/
      FuelCalculatorForm.php
      FuelCalculatorSettingsForm.php
    Plugin/
      Block/
        FuelCalculatorBlock.php
      rest/
        resource/
          FuelCalculatorResource.php
    Service/
      FuelCalculatorService.php
  config/
    install/
      fuel_calculator.settings.yml
  fuel_calculator.info.yml
  fuel_calculator.libraries.yml
  fuel_calculator.links.menu.yml
  fuel_calculator.routing.yml
  fuel_calculator.services.yml
  README.txt
```

---

## Support

For questions or improvements, contact the module author or open an issue in your project repository.

---
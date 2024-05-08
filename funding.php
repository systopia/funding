<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'funding.civix.php';
// phpcs:enable

use Civi\Funding\Api4\Permissions;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function funding_civicrm_config(\CRM_Core_Config &$config): void {
  _funding_civix_civicrm_config($config);
}

function funding_civicrm_container(ContainerBuilder $container): void {
  if (!interface_exists(Api4Interface::class)) {
    // Extension de.systopia.remotetools is not loaded, yet.
    return;
  }

  $globResource = new GlobResource(__DIR__ . '/services', '/*.php', FALSE);
  // Container will be rebuilt if a *.php file is added to services
  $container->addResource($globResource);
  foreach ($globResource->getIterator() as $path => $info) {
    // Container will be rebuilt if file changes
    $container->addResource(new FileResource($path));
    require $path;
  }

  if (function_exists('_funding_test_civicrm_container')) {
    // Allow to use different services in tests.
    _funding_test_civicrm_container($container);
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function funding_civicrm_pageRun(\CRM_Core_Page $page): void {
  if ($page instanceof \Civi\Angular\Page\Main) {
    \Civi::resources()->addModuleFile(E::LONG_NAME, 'js/json-ptr.js');
  }
}

/**
 * Implements hook_civicrm_permission().
 *
 * @phpstan-param array<string, string|array{string, string}> $permissions
 */
function funding_civicrm_permission(array &$permissions): void {
  $permissions[Permissions::ACCESS_FUNDING] = [
    'label' => E::ts('CiviCRM: access Funding Program Manager'),
    'description' => E::ts('Access non-administrative API of the Funding Program Manager'),
  ];
  $permissions[Permissions::ACCESS_REMOTE_FUNDING] = [
    'label' => E::ts('CiviCRM: remote access to Funding Program Manager'),
    'description' => E::ts('Access remote API of the Funding Program Manager'),
  ];
  $permissions[Permissions::ADMINISTER_FUNDING] = [
    'label' => E::ts('CiviCRM: administer Funding Program Manager'),
    'description' => E::ts('Access administrative and non-administrative API of the Funding Program Manager'),
  ];
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function funding_civicrm_install(): void {
  _funding_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function funding_civicrm_enable(): void {
  _funding_civix_civicrm_enable();
}

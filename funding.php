<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'funding.civix.php';
// phpcs:enable

use Civi\Funding\Api4\Permissions;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function funding_civicrm_config(&$config): void {
  _funding_civix_civicrm_config($config);
}

function funding_civicrm_container(ContainerBuilder $container): void {
  if (!interface_exists(\Civi\RemoteTools\Api4\Api4Interface::class)) {
    // Don't register any service if Remote Tools are not available.
    // We'd get class not found errors from subscribers that depend on classes
    // in Remote Tools.
    return;
  }

  // Allow lazy service instantiation (requires symfony/proxy-manager-bridge)
  if (class_exists(\ProxyManager\Configuration::class) && class_exists(RuntimeInstantiator::class)) {
    $container->setProxyInstantiator(new RuntimeInstantiator());
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
 * Implements hook_civicrm_permission().
 *
 * @phpstan-param array<string, string|array{string, string}> $permissions
 */
function funding_civicrm_permission(array &$permissions): void {
  $permissions[Permissions::ACCESS_FUNDING] = [
    E::ts('CiviCRM: access Funding Program Manager'),
    E::ts('Access non-administrative API of the Funding Program Manager'),
  ];
  $permissions[Permissions::ACCESS_REMOTE_FUNDING] = [
    E::ts('CiviCRM: remote access to Funding Program Manager'),
    E::ts('Access remote API of the Funding Program Manager'),
  ];
  $permissions[Permissions::ADMINISTER_FUNDING] = [
    E::ts('CiviCRM: administer Funding Program Manager'),
    E::ts('Access administrative and non-administrative API of the Funding Program Manager'),
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

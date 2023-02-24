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
    E::ts('CiviCRM: access funding'),
    E::ts('Access non-administrative API of the funding extension'),
  ];
  $permissions[Permissions::ACCESS_REMOTE_FUNDING] = [
    E::ts('CiviCRM: access remote funding'),
    E::ts('Access remote API of the funding extension'),
  ];
  $permissions[Permissions::ADMINISTER_FUNDING] = [
    E::ts('CiviCRM: administer funding'),
    E::ts('Access administrative and non-administrative API of the funding extension'),
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
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function funding_civicrm_postInstall(): void {
  _funding_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function funding_civicrm_uninstall(): void {
  _funding_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function funding_civicrm_enable(): void {
  _funding_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function funding_civicrm_disable(): void {
  _funding_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function funding_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _funding_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function funding_civicrm_entityTypes(&$entityTypes): void {
  _funding_civix_civicrm_entityTypes($entityTypes);
}

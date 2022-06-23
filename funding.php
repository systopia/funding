<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'funding.civix.php';
// phpcs:enable

use Civi\Funding\Contact\FundingRemoteContactIdResolver;
use Civi\Funding\EventSubscriber\RemoteFundingProgramDAOGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\RemoteFundingProgramDAOGetSubscriber;
use Civi\Funding\EventSubscriber\RemoteFundingRequestInitSubscriber;
use Civi\RemoteTools\Api4\Api4;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\EventSubscriber\ApiAuthorizeInitRequestSubscriber;
use Civi\RemoteTools\EventSubscriber\ApiAuthorizeSubscriber;
use Civi\RemoteTools\EventSubscriber\CheckAccessSubscriber;
use CRM_Funding_ExtensionUtil as E;
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
  $container->register(Api4Interface::class, Api4::class);
  $container->register(ApiAuthorizeInitRequestSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->register(ApiAuthorizeSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(CheckAccessSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);

  $container->autowire(FundingRemoteContactIdResolver::class);

  $container->autowire(RemoteFundingRequestInitSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(RemoteFundingProgramDAOGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(RemoteFundingProgramDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
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

function funding_civicrm_permission(array &$permissions): void {
  $permissions['apply Funding'] = E::ts('Funding: make applications');
  $permissions['access Remote Funding'] = E::ts('Funding: access remote API');
}

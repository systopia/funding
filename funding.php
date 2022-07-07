<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'funding.civix.php';
// phpcs:enable

use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewApplicationFormAction;
use Civi\Funding\Contact\FundingRemoteContactIdResolver;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessDAOGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseDAOGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCasePermissionsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseTypeDAOGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseTypeDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramDAOGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramPermissionsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingRequestInitSubscriber;
use Civi\Funding\Remote\RemoteFundingEntityManager;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
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
  $container->setAlias(CiviEventDispatcher::class, 'dispatcher.boot');
  $container->register(Api4Interface::class, Api4::class);
  $container->register(ApiAuthorizeInitRequestSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->register(ApiAuthorizeSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(CheckAccessSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);

  $container->autowire(RemoteFundingEntityManagerInterface::class, RemoteFundingEntityManager::class);
  $container->autowire(FundingRemoteContactIdResolver::class);

  $container->autowire(GetNewApplicationFormAction::class)
    ->setPublic(TRUE)
    ->setShared(FALSE);
  $container->autowire(SubmitNewApplicationFormAction::class)
    ->setPublic(TRUE)
    ->setShared(FALSE);
  $container->autowire(ValidateNewApplicationFormAction::class)
    ->setPublic(TRUE)
    ->setShared(FALSE);
  $container->autowire(GetFormAction::class)
    ->setPublic(TRUE)
    ->setShared(FALSE);
  $container->autowire(SubmitFormAction::class)
    ->setPublic(TRUE)
    ->setShared(FALSE);
  $container->autowire(ValidateFormAction::class)
    ->setPublic(TRUE)
    ->setShared(FALSE);

  $container->autowire(FundingRequestInitSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(ApplicationProcessDAOGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(ApplicationProcessDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(FundingCaseDAOGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(FundingCaseDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(FundingCasePermissionsSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(FundingCaseTypeDAOGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(FundingCaseTypeDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(FundingProgramDAOGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(FundingProgramDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber')
    ->setLazy(TRUE);
  $container->autowire(FundingProgramPermissionsSubscriber::class)
    ->addTag('kernel.event_subscriber');
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

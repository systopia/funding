<?php
declare(strict_types = 1);

require_once 'funding.civix.php';

use Civi\Api4\Generic\Api4;
use Civi\Api4\Generic\Api4Interface;
use Civi\Funding\EventSubscriber\RemoteApiAuthorizeSubscriber;
use Civi\Funding\EventSubscriber\RemoteFundingProgramDAOGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\RemoteFundingProgramDAOGetSubscriber;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function funding_civicrm_config(&$config) {
  _funding_civix_civicrm_config($config);
}

function funding_civicrm_container(ContainerBuilder $container): void {
  $container->register(Api4Interface::class, Api4::class);

  $container->autowire(RemoteApiAuthorizeSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(RemoteFundingProgramDAOGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(RemoteFundingProgramDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber');
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function funding_civicrm_install() {
  _funding_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function funding_civicrm_postInstall() {
  _funding_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function funding_civicrm_uninstall() {
  _funding_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function funding_civicrm_enable() {
  _funding_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function funding_civicrm_disable() {
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
function funding_civicrm_entityTypes(&$entityTypes) {
  _funding_civix_civicrm_entityTypes($entityTypes);
}

function funding_civicrm_permission(array &$permissions): void {
  $permissions['apply Funding'] = E::ts('Funding: make applications');
  $permissions['access Remote Funding'] = E::ts('Funding: access remote API');
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function funding_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function funding_civicrm_navigationMenu(&$menu) {
//  _funding_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _funding_civix_navigationMenu($menu);
//}

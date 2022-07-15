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
use Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1GetNewApplicationFormSubscriber;
use Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1SubmitNewApplicationFormSubscriber;
use Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1ValidateNewApplicationFormSubscriber;
use Civi\Funding\EventSubscriber\FundingCasePermissionsGetSubscriber;
use Civi\Funding\EventSubscriber\FundingProgramPermissionsGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseTypeDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseTypeGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingRequestInitSubscriber;
use Civi\Funding\Form\Validation\FormValidator;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\OpisValidatorFactory;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\Permission\ContactRelation\ContactChecker;
use Civi\Funding\Permission\ContactRelation\ContactRelationshipChecker;
use Civi\Funding\Permission\ContactRelation\ContactTypeChecker;
use Civi\Funding\Permission\ContactRelation\ContactTypeRelationshipChecker;
use Civi\Funding\Permission\ContactRelationCheckerCollection;
use Civi\Funding\Permission\ContactRelationCheckerInterface;
use Civi\Funding\Remote\RemoteFundingEntityManager;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use Civi\RemoteTools\Api4\Api4;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\EventSubscriber\ApiAuthorizeInitRequestSubscriber;
use Civi\RemoteTools\EventSubscriber\ApiAuthorizeSubscriber;
use Civi\RemoteTools\EventSubscriber\CheckAccessSubscriber;
use CRM_Funding_ExtensionUtil as E;
use Opis\JsonSchema\Validator;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
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

  $container->setAlias(CiviEventDispatcher::class, 'dispatcher.boot');

  $container->autowire(ContactChecker::class)
    ->addTag('funding.permission.contact_relation_checker');
  $container->autowire(ContactRelationshipChecker::class)
    ->addTag('funding.permission.contact_relation_checker');
  $container->autowire(ContactTypeChecker::class)
    ->addTag('funding.permission.contact_relation_checker');
  $container->autowire(ContactTypeRelationshipChecker::class)
    ->addTag('funding.permission.contact_relation_checker');
  $container->register(ContactRelationCheckerInterface::class, ContactRelationCheckerCollection::class)
    ->addArgument(new TaggedIteratorArgument('funding.permission.contact_relation_checker'));

  $container->register(Api4Interface::class, Api4::class);
  $container->register(ApiAuthorizeInitRequestSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->register(ApiAuthorizeSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(CheckAccessSubscriber::class)
    ->addTag('kernel.event_subscriber');

  $container->register(Validator::class)->setFactory([OpisValidatorFactory::class, 'getValidator']);
  $container->autowire(FormValidatorInterface::class, FormValidator::class);

  $container->autowire(RemoteFundingEntityManagerInterface::class, RemoteFundingEntityManager::class);
  $container->autowire(FundingRemoteContactIdResolver::class);
  $container->autowire(FundingCaseTypeProgramRelationChecker::class);

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
  $container->autowire(ApplicationProcessGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(ApplicationProcessDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(FundingCaseGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(FundingCaseDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(FundingCasePermissionsGetSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(AVK1GetNewApplicationFormSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(AVK1SubmitNewApplicationFormSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(AVK1ValidateNewApplicationFormSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(FundingCaseTypeGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(FundingCaseTypeDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(FundingProgramGetFieldsSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(FundingProgramDAOGetSubscriber::class)
    ->addTag('kernel.event_subscriber');
  $container->autowire(FundingProgramPermissionsGetSubscriber::class)
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

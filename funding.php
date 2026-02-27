<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'funding.civix.php';
// phpcs:enable

use Civi\Api4\FundingCase;
use Civi\Funding\Api4\Permissions;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

function _funding_composer_autoload(): void {
  if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function funding_civicrm_config(\CRM_Core_Config &$config): void {
  _funding_composer_autoload();
  _funding_civix_civicrm_config($config);
}

function funding_civicrm_container(ContainerBuilder $container): void {
  _funding_composer_autoload();

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
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function funding_civicrm_postInstall(): void {
  /**
   * We need a collation that is accent-sensitive and case-sensitive. However, the
   * chosen collation utf8mb4_bin doesn't sort in natural order. Collations
   * that are also sorting in natural order are named differently on MariaDB
   * and MySQL, though. (MariaDB: utf8mb4_0900_as_cs, MySQL:
   * utf8mb4_0900_as_cs) Since MariaDB 11.4.5 there's a mapping of MySQL
   * collations: https://jira.mariadb.org/browse/MDEV-20912.
   */
  CRM_Core_DAO::executeQuery(
    'ALTER TABLE civicrm_funding_form_string_translation
             MODIFY COLUMN msg_text VARCHAR(8000) COLLATE utf8mb4_bin,
             MODIFY COLUMN new_text VARCHAR(8000) COLLATE utf8mb4_bin NOT NULL'
  );

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
 * Reset permissions of funding cases to the ones configured in the funding
 * program.
 *
 * @phpstan-param array<mixed> $where
 *   An APIv4 where array for the FundingCase entity. If not given the
 *   permissions of all funding cases will be reset.
 *
 * @throws \CRM_Core_Exception
 */
function funding_case_reset_permissions(array $where = []): void {
  $fundingCaseIds = FundingCase::get(FALSE)
    ->addSelect('id')
    ->setWhere($where)
    ->execute()
    ->column('id');

  foreach ($fundingCaseIds as $fundingCaseId) {
    FundingCase::resetPermissions(FALSE)
      ->setId($fundingCaseId)
      ->execute();
  }
}

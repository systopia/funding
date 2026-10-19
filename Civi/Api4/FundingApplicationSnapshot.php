<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingCase\ReferencingDAOGetAction;
use Civi\Funding\Api4\Traits\AccessROPermissionsTrait;

/**
 * FundingApplicationSnapshot entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingApplicationSnapshot extends Generic\DAOEntity {

  use AccessROPermissionsTrait;

  public static function get($checkPermissions = TRUE) {
    return (new ReferencingDAOGetAction('FundingApplicationSnapshot', 'application_process_id.funding_case_id'))
      ->setCheckPermissions($checkPermissions);
  }

}

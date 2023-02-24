<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Traits\AdministerPermissionsTrait;
use Civi\RemoteTools\Api4\Traits\EntityNameTrait;

/**
 * FundingRecipientContactRelation entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingRecipientContactRelation extends Generic\DAOEntity {

  use AdministerPermissionsTrait;

  use EntityNameTrait;

}

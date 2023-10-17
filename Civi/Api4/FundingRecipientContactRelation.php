<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Traits\AdministerPermissionsTrait;

/**
 * FundingRecipientContactRelation entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingRecipientContactRelation extends Generic\DAOEntity {

  use AdministerPermissionsTrait;

}

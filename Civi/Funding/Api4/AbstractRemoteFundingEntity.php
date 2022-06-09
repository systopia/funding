<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4;

use Civi\Api4\Generic\AbstractEntity;
use Civi\Funding\Api4\Action\RemoteFundingCheckAccessAction;
use Civi\Funding\Api4\Action\RemoteFundingGetFieldsAction;

class AbstractRemoteFundingEntity extends AbstractEntity {

  /**
   * @inerhitDoc
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public static function checkAccess() {
    return new RemoteFundingCheckAccessAction(static::getEntityName(), __FUNCTION__);
  }

  /**
   * @inheritDoc
   */
  public static function getFields() {
    return new RemoteFundingGetFieldsAction(static::getEntityName(), __FUNCTION__);
  }

  /**
   * @inheritDoc
   *
   * @return array<string, array<string|string[]>>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public static function permissions(): array {
    return [
      'meta' => ['access CiviCRM', 'access Remote Funding'],
      'default' => ['administer CiviCRM'],
      'get' => ['access CiviCRM', 'access Remote Funding'],
    ];
  }

}

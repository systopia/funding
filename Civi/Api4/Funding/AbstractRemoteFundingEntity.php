<?php
declare(strict_types = 1);

namespace Civi\Api4\Funding;

use Civi\Api4\Action\RemoteEventCheckAccessAction;
use Civi\Api4\Action\RemoteEventGetFieldsAction;
use Civi\Api4\Generic\AbstractEntity;

class AbstractRemoteFundingEntity extends AbstractEntity {

  /**
   * @inerhitDoc
   */
  public static function checkAccess() {
    return new RemoteEventCheckAccessAction(static::getEntityName());
  }

  /**
   * @inheritDoc
   */
  public static function getFields() {
    return new RemoteEventGetFieldsAction(static::getEntityName());
  }

  /**
   * @inheritDoc
   */
  public static function permissions(): array {
    return [
      'meta' => ['access CiviCRM', 'access Remote Funding'],
      'default' => ['administer CiviCRM'],
      'get' => ['access CiviCRM', 'access Remote Funding'],
    ];
  }

}

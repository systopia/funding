<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4;

final class Permissions {

  /**
   * CiviCRM core permission.
   *
   * @see \CRM_Core_Permission::getCorePermissions For CiviCRM core permissions.
   */
  public const ACCESS_CIVICRM = 'access CiviCRM';

  public const ACCESS_FUNDING = 'access Funding';

  public const ACCESS_REMOTE_FUNDING = 'access Remote Funding';

  public const ADMINISTER_FUNDING = 'administer Funding';

}

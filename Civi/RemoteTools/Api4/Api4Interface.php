<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

interface Api4Interface {

  /**
   * @param string $entityName
   * @param string $action
   * @param array<string, mixed> $params
   *
   * @return \Civi\Api4\Generic\AbstractAction
   *
   * @throws \Civi\API\Exception\NotImplementedException
   */

  public function createAction(string $entityName, string $action, array $params = []): AbstractAction;

  /**
   * @throws \API_Exception
   */
  public function executeAction(AbstractAction $action): Result;

  /**
   * @param string $entityName
   * @param string $actionName
   * @param array<string, mixed> $params
   *
   * @return \Civi\Api4\Generic\Result
   *
   * @throws \API_Exception
   */
  public function execute(string $entityName, string $actionName, array $params = []): Result;

}

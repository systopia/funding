<?php
declare(strict_types = 1);

namespace Civi\Api4\Generic;

interface Api4Interface {

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  public function createAction(string $entityName, string $action, array $params = []): AbstractAction;

  /**
   * @throws \API_Exception
   */
  public function executeAction(AbstractAction $action): Result;

  /**
   * @throws \API_Exception
   */
  public function execute(string $entityName, string $actionName, array $params = []): Result;

}

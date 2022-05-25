<?php
declare(strict_types = 1);

namespace Civi\Api4\Generic;

use Civi\API\Request;

final class Api4 implements Api4Interface {

  private static self $instance;

  public static function getInstance(): Api4 {
    return self::$instance ?? new self();
  }

  public function __construct() {
    self::$instance = $this;
  }

  public function createAction(string $entityName, string $action, array $params = []): AbstractAction {
    return Request::create($entityName, $action, $params);
  }

  public function executeAction(AbstractAction $action): Result {
    return $action->execute();
  }

  public function execute(string $entityName, string $actionName, array $params = []): Result {
    return $this->createAction($entityName, $actionName, $params)->execute();
  }

}

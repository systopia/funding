<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4;

use Civi\API\Request;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Webmozart\Assert\Assert;

final class Api4 implements Api4Interface {

  private static self $instance;

  public static function getInstance(): Api4 {
    return self::$instance ?? new self();
  }

  public function __construct() {
    self::$instance = $this;
  }

  /**
   * @inheritDoc
   */
  public function createAction(string $entityName, string $action, array $params = []): AbstractAction {
    if (isset($params['version'])) {
      Assert::same($params['version'], 4);
    }
    else {
      $params['version'] = 4;
    }

    return Request::create($entityName, $action, $params);
  }

  /**
   * @inheritDoc
   */
  public function executeAction(AbstractAction $action): Result {
    return $action->execute();
  }

  /**
   * @inheritDoc
   */
  public function execute(string $entityName, string $actionName, array $params = []): Result {
    return $this->createAction($entityName, $actionName, $params)->execute();
  }

}

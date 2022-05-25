<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Api4\Generic\AbstractAction;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractApiEvent extends Event {

  private string $entityName;

  private string $actionName;

  protected bool $checkPermissions = TRUE;

  protected bool $debug = FALSE;

  private array $debugOutput = [];

  /**
   * @param \Civi\Api4\Generic\AbstractAction $apiRequest
   *
   * @return static
   */
  public static function fromApiRequest(AbstractAction $apiRequest): self {
    return new static($apiRequest->getEntityName(), $apiRequest->getActionName(), $apiRequest->getParams());
  }

  public static function getEventName(string $entityName = NULL, string $actionName = NULL): string {
    $eventName = static::class;
    if (NULL !== $entityName) {
      $eventName .= '@' . $entityName;
    }

    if (NULL !== $actionName) {
      if (NULL === $entityName) {
        throw new \InvalidArgumentException('entityName is required if actionName is specified');
      }

      $eventName .= '::' . $actionName;
    }

    return $eventName;
  }

  public function __construct(string $entityName, string $actionName, array $params) {
    $missingParams = array_diff($this->getRequiredParams(), array_keys($params));
    if ([] !== $missingParams) {
      throw new \InvalidArgumentException(sprintf('Required params missing: %s', implode(', ', $missingParams)));
    }

    $this->entityName = $entityName;
    $this->actionName = $actionName;
    $this->setParams($params);
  }

  public function getEntityName(): string {
    return $this->entityName;
  }

  public function getActionName(): string {
    return $this->actionName;
  }

  public function isCheckPermissions(): bool {
    return $this->checkPermissions;
  }

  public function isDebug(): bool {
    return $this->debug;
  }

  public function getDebugOutput(): array {
    return $this->debugOutput;
  }

  public function addDebugOutput(string $key, array $debugOutput): self {
    $this->debugOutput[$key] = $debugOutput;

    return $this;
  }

  protected function getRequiredParams(): array {
    return [];
  }

  private function setParams(array $params): void {
    foreach ($params as $key => $value) {
      if (property_exists($this, $key)) {
        $this->{$key} = $value;
      }
    }
  }

}

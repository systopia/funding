<?php
declare(strict_types=1);

namespace Civi\RemoteTools\Event;

use Civi\API\Event\RequestTrait;
use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Symfony\Component\EventDispatcher\Event;
use Webmozart\Assert\Assert;

/**
 * @method \Civi\Api4\Generic\AbstractAction&\Civi\RemoteTools\Api4\Action\EventActionInterface getApiRequest()
 *
 * @phpstan-consistent-constructor
 */
abstract class AbstractApiRequestEvent extends Event {

  use RequestTrait;

  public static function fromApiRequest(AbstractAction $apiRequest): self {
    return new static($apiRequest);
  }

  public function __construct(AbstractAction $apiRequest) {
    Assert::isInstanceOf($apiRequest, EventActionInterface::class);
    $this->apiRequest = $apiRequest;
  }

}

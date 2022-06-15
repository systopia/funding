<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\Event\RemoteFundingDAOGetEvent;
use Civi\Funding\EventSubscriber\Traits\RemoteFundingCaseTypeSubscriberTrait;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetSubscriber;

final class RemoteFundingCaseTypeDAOGetSubscriber extends AbstractRemoteDAOGetSubscriber {

  use RemoteFundingCaseTypeSubscriberTrait;

  protected const DAO_ENTITY_NAME = 'FundingCaseType';

  protected const ENTITY_NAME = 'RemoteFundingCaseType';

  protected const EVENT_CLASS = RemoteFundingDAOGetEvent::class;

}

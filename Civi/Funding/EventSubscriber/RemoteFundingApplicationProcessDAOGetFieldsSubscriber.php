<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\Event\RemoteFundingGetFieldsEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetFieldsSubscriber;

final class RemoteFundingApplicationProcessDAOGetFieldsSubscriber extends AbstractRemoteDAOGetFieldsSubscriber {

  protected const DAO_ENTITY_NAME = 'FundingApplicationProcess';

  protected const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  protected const EVENT_CLASS = RemoteFundingGetFieldsEvent::class;

}

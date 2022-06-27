<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\Event\RemoteFundingGetFieldsEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetFieldsSubscriber;

final class RemoteFundingCaseDAOGetFieldsSubscriber extends AbstractRemoteDAOGetFieldsSubscriber {

  protected const DAO_ENTITY_NAME = 'FundingCase';

  protected const ENTITY_NAME = 'RemoteFundingCase';

  protected const EVENT_CLASS = RemoteFundingGetFieldsEvent::class;

}

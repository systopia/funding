<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\Event\RemoteFundingGetFieldsEvent;
use Civi\Funding\EventSubscriber\Traits\RemoteFundingCaseTypeSubscriberTrait;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetFieldsSubscriber;

final class RemoteFundingCaseTypeDAOGetFieldsSubscriber extends AbstractRemoteDAOGetFieldsSubscriber {

  use RemoteFundingCaseTypeSubscriberTrait;

  protected const DAO_ENTITY_NAME = 'FundingCaseType';

  protected const ENTITY_NAME = 'RemoteFundingCaseType';

  protected const EVENT_CLASS = RemoteFundingGetFieldsEvent::class;

}

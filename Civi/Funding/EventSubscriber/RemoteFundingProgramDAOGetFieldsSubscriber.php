<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\EventSubscriber\Traits\RemoteFundingProgramSubscriberTrait;

class RemoteFundingProgramDAOGetFieldsSubscriber extends AbstractRemoteDAOGetFieldsSubscriber {

  use RemoteFundingProgramSubscriberTrait;

  protected const DAO_ENTITY_NAME = 'FundingProgram';

  protected const ENTITY_NAME = 'RemoteFundingProgram';

}

<?php

declare(strict_types = 1);

/**
 * @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/FundingAmountApprovedChangeRequest/Api4/ActionHandler',
  'Civi\\Funding\\FundingAmountApprovedChangeRequest\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCase/Api4/ActionHandler/RemoteAmountApprovedChangeRequest',
  'Civi\\Funding\\FundingCase\\Api4\\ActionHandler\\RemoteAmountApprovedChangeRequest',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

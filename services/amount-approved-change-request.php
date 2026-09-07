<?php

declare(strict_types = 1);

/**
 * @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Api4/ActionHandler/RemoteAmountApprovedChangeRequest',
  'Civi\\Funding\\Api4\\ActionHandler\\RemoteAmountApprovedChangeRequest',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\Contact\FundingCaseRecipientLoader;
use Civi\Funding\Contact\FundingCaseRecipientLoaderInterface;
use Civi\Funding\Contact\PossibleRecipientsLoaderCollection;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Contact\RecipientsLoader\DefaultPossibleRecipientsLoader;
use Civi\Funding\Contact\RelatedContactsLoaderCollection;
use Civi\Funding\Contact\RelatedContactsLoaderInterface;
use Civi\Funding\Contact\Relation\RelationTypeContainer;
use Civi\Funding\Contact\Relation\RelationTypeInterface;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

$container->autowire(FundingCaseRecipientLoaderInterface::class, FundingCaseRecipientLoader::class);

$container->register(PossibleRecipientsLoaderInterface::class, PossibleRecipientsLoaderCollection::class)
  ->addArgument(new TaggedIteratorArgument(PossibleRecipientsLoaderInterface::SERVICE_TAG));
$container->autowire(DefaultPossibleRecipientsLoader::class)
  ->addTag(PossibleRecipientsLoaderInterface::SERVICE_TAG);

$container->register('funding.contact_relation_type_container', RelationTypeContainer::class)
  ->addArgument(new TaggedIteratorArgument('funding.contact_relation_type'))
  // Used in API action.
  ->setPublic(TRUE);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Contact/Relation/Types',
  'Civi\\Funding\\Contact\\Relation\\Types',
  RelationTypeInterface::class,
  ['funding.contact_relation_type' => []],
);

$container->register(RelatedContactsLoaderInterface::class, RelatedContactsLoaderCollection::class)
  ->addArgument(new TaggedIteratorArgument('funding.related_contacts_loader'));

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Contact/Relation/Loaders',
  'Civi\\Funding\\Contact\\Relation\\Loaders',
  RelatedContactsLoaderInterface::class,
  ['funding.related_contacts_loader' => []],
);

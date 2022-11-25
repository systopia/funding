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

use Civi\Funding\Contact\Relation\RelationTypeContainer;
use Civi\Funding\Permission\ContactRelation\Checker\ContactChecker;
use Civi\Funding\Permission\ContactRelation\Checker\ContactRelationshipChecker;
use Civi\Funding\Permission\ContactRelation\Checker\ContactTypeChecker;
use Civi\Funding\Permission\ContactRelation\Checker\ContactTypeRelationshipChecker;
use Civi\Funding\Permission\ContactRelation\ContactRelationCheckerCollection;
use Civi\Funding\Permission\ContactRelation\ContactRelationCheckerInterface;
use Civi\Funding\Permission\ContactRelation\Types\Contact;
use Civi\Funding\Permission\ContactRelation\Types\ContactRelationship;
use Civi\Funding\Permission\ContactRelation\Types\ContactType;
use Civi\Funding\Permission\ContactRelation\Types\ContactTypeRelationship;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Reference;

$container->autowire(\Civi\Funding\Api4\Action\FundingCaseContactRelationType\GetFieldsAction::class);
$container->autowire(\Civi\Funding\Api4\Action\FundingCaseContactRelationType\GetAction::class)
  ->setArgument('$relationTypeContainer', new Reference('funding.permission.contact_relation_type_container'))
  ->setPublic(TRUE)
  ->setShared(TRUE);

$container->autowire(\Civi\Funding\Api4\Action\FundingProgramContactRelationType\GetFieldsAction::class);
$container->autowire(\Civi\Funding\Api4\Action\FundingProgramContactRelationType\GetAction::class)
  ->setArgument('$relationTypeContainer', new Reference('funding.permission.contact_relation_type_container'))
  ->setPublic(TRUE)
  ->setShared(TRUE);

$container->register('funding.permission.contact_relation_type_container', RelationTypeContainer::class)
  ->addArgument(new TaggedIteratorArgument('funding.permission.contact_relation_type'));
$container->autowire(Contact::class)
  ->addTag('funding.permission.contact_relation_type');
$container->autowire(ContactRelationship::class)
  ->addTag('funding.permission.contact_relation_type');
$container->autowire(ContactType::class)
  ->addTag('funding.permission.contact_relation_type');
$container->autowire(ContactTypeRelationship::class)
  ->addTag('funding.permission.contact_relation_type');

$container->register(ContactRelationCheckerInterface::class, ContactRelationCheckerCollection::class)
  ->addArgument(new TaggedIteratorArgument('funding.permission.contact_relation_checker'));
$container->autowire(ContactChecker::class)
  ->addTag('funding.permission.contact_relation_checker');
$container->autowire(ContactRelationshipChecker::class)
  ->addTag('funding.permission.contact_relation_checker');
$container->autowire(ContactTypeChecker::class)
  ->addTag('funding.permission.contact_relation_checker');
$container->autowire(ContactTypeRelationshipChecker::class)
  ->addTag('funding.permission.contact_relation_checker');

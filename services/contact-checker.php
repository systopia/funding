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

use Civi\Funding\Permission\ContactRelation\ContactChecker;
use Civi\Funding\Permission\ContactRelation\ContactRelationshipChecker;
use Civi\Funding\Permission\ContactRelation\ContactTypeChecker;
use Civi\Funding\Permission\ContactRelation\ContactTypeRelationshipChecker;
use Civi\Funding\Permission\ContactRelationCheckerCollection;
use Civi\Funding\Permission\ContactRelationCheckerInterface;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

$container->autowire(ContactChecker::class)
  ->addTag('funding.permission.contact_relation_checker');
$container->autowire(ContactRelationshipChecker::class)
  ->addTag('funding.permission.contact_relation_checker');
$container->autowire(ContactTypeChecker::class)
  ->addTag('funding.permission.contact_relation_checker');
$container->autowire(ContactTypeRelationshipChecker::class)
  ->addTag('funding.permission.contact_relation_checker');
$container->register(ContactRelationCheckerInterface::class, ContactRelationCheckerCollection::class)
  ->addArgument(new TaggedIteratorArgument('funding.permission.contact_relation_checker'));

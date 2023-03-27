<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeDocumentRenderer;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeDocumentStore;
use Civi\Funding\DocumentRender\DocumentRendererInterface;
use Civi\Funding\DocumentRender\Token\TokenNameExtractor;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorCacheDecorator;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use Civi\Funding\DocumentRender\Token\TokenResolver;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// Don't fail in event subscriber pass, when CiviOffice is not available.
if (class_exists(\CRM_Civioffice_DocumentStore::class)) {
  $container->autowire(CiviOfficeDocumentStore::class)
    ->addTag('kernel.event_subscriber');

  ServiceRegistrator::autowireAllImplementing(
    $container,
    __DIR__ . '/../Civi/Funding/EventSubscriber/CiviOffice',
    'Civi\\Funding\\EventSubscriber\\CiviOffice',
    EventSubscriberInterface::class,
    ['kernel.event_subscriber' => []],
    ['lazy' => TRUE],
  );
}

$container->autowire(CiviOfficeContextDataHolder::class);
$container->autowire(DocumentRendererInterface::class, CiviOfficeDocumentRenderer::class);

$container->autowire(TokenNameExtractorInterface::class, TokenNameExtractor::class);
$container->autowire(TokenNameExtractorCacheDecorator::class)
  ->setDecoratedService(TokenNameExtractorInterface::class);
$container->autowire(TokenResolverInterface::class, TokenResolver::class);

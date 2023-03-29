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

namespace Civi\Funding\DocumentRender\CiviOffice;

use Civi\Core\Event\GenericHookEvent;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This document store allows to read files on file system with their path
 * prefixed by "file://". It only allows to access single files by their URI.
 * This makes it possible to just pass the path of a template file to CiviOffice.
 *
 * @codeCoverageIgnore
 */
final class CiviOfficeDocumentStore extends \CRM_Civioffice_DocumentStore implements EventSubscriberInterface {

  public const SCHEME = 'file';

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return ['civi.civioffice.documentStores' => 'onRegister'];
  }

  public function __construct() {
    parent::__construct('funding', E::ts('Files in filesystem (Funding Program Manager)'));
  }

  public function onRegister(GenericHookEvent $event): void {
    $event->document_stores[] = $this;
  }

  /**
   * @inheritDoc
   *
   * @phpstan-return array<string>
   */
  public function getDocuments($path = NULL): array {
    return [];
  }

  /**
   * @inheritDoc
   *
   * @phpstan-return array<string>
   */
  public function getPaths($path = NULL): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function isReadOnly(): bool {
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function getDocumentByURI($uri) {
    if ($this->isStoreURI($uri) && is_file($uri)) {
      return new CiviOfficeDocument($this, $uri);
    }

    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function isStoreURI($uri): bool {
    return str_starts_with($uri, self::SCHEME . '://');
  }

  /**
   * @inheritDoc
   */
  public function getConfigPageURL(): string {
    return '';
  }

  /**
   * @inheritDoc
   */
  public function isReady(): bool {
    return TRUE;
  }

}

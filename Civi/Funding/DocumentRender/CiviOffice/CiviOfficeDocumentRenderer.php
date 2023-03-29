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

use Civi\Funding\DocumentRender\DocumentRendererInterface;
use Civi\RemoteTools\Api3\Api3Interface;
use Webmozart\Assert\Assert;

class CiviOfficeDocumentRenderer implements DocumentRendererInterface {

  private Api3Interface $api3;

  private CiviOfficeContextDataHolder $contextDataHolder;

  private string $mimeType;

  private string $rendererUri;

  public function __construct(
    Api3Interface $api3,
    CiviOfficeContextDataHolder $contextDataHolder,
    string $rendererUri = 'unoconv-local',
    string $mimeType = 'application/pdf'
  ) {
    $this->api3 = $api3;
    $this->contextDataHolder = $contextDataHolder;
    $this->rendererUri = $rendererUri;
    $this->mimeType = $mimeType;
  }

  public function getMimeType(): string {
    return $this->mimeType;
  }

  public function render(
    string $templateFile,
    string $entityName,
    int $entityId,
    array $data = []
  ): string {
    $this->contextDataHolder->addEntityData($entityName, $entityId, $data);

    try {
      /** @phpstan-var array{values: array{string}} $result */
      $result = $this->api3->execute('CiviOffice', 'convert', [
        'document_uri' => 'file://' . $templateFile,
        'entity_type' => $entityName,
        'entity_ids' => [$entityId],
        'target_mime_type' => $this->mimeType,
        'renderer_uri' => $this->rendererUri,
      ]);
    }
    finally {
      $this->contextDataHolder->removeEntityData($entityName, $entityId);
    }

    $documentStoreUri = $result['values'][0];
    $documentStore = \CRM_Civioffice_Configuration::getDocumentStore($documentStoreUri);
    Assert::notNull($documentStore, sprintf('No CiviOffice document store with URI "%s" found.', $documentStoreUri));
    /** @var \CRM_Civioffice_Document $document */
    foreach ($documentStore->getDocuments() as $document) {
      // Avoid unnecessary file copy.
      if (method_exists($document, 'getAbsolutePath')) {
        return $document->getAbsolutePath();
      }

      return $document->getLocalTempCopy();
    }

    throw new \RuntimeException('No rendered file found.');
  }

}

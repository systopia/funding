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

final class CiviOfficeDocument extends \CRM_Civioffice_Document {

  private string $path;

  public function __construct(CiviOfficeDocumentStore $documentStore, string $uri) {
    parent::__construct($documentStore, $uri, basename($uri));
    // @phpstan-ignore-next-line
    $this->path = preg_replace('#^' . CiviOfficeDocumentStore::SCHEME . '://#', '', $this->uri);
  }

  /**
   * @inheritDoc
   */
  public function getContent(): string {
    $content = file_get_contents($this->getPath());
    if (!is_string($content)) {
      throw new \RuntimeException(sprintf('Could not read file "%s"', $this->uri));
    }

    return $content;
  }

  /**
   * @inheritDoc
   */
  public function updateFileContent(string $data): void {
    if (FALSE === file_put_contents($this->getPath(), $data)) {
      throw new \RuntimeException(sprintf('Could not write file "%s"', $this->uri));
    }
  }

  /**
   * @inheritDoc
   */
  public function getPath(): string {
    return $this->path;
  }

  /**
   * @inheritDoc
   */
  public function isEditable(): bool {
    return is_writable($this->getPath());
  }

}

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

namespace Civi\Funding\EntityFactory;

use Civi\Funding\Entity\ExternalFileEntity;

/**
 * @phpstan-type externalFileT array{
 *   id?: int,
 *   file_id?: int,
 *   source?: string,
 *   filename?: string,
 *   extension?: string,
 *   identifier?: string,
 *   custom_data?: ?array<mixed>,
 *   status?: string,
 *   download_start_date?: ?string,
 *   download_try_count?: int,
 *   last_modified?: ?string,
 *   uri?: non-empty-string,
 *   file_file_type_id?: ?int,
 *   file_mime_type?: string,
 *   file_description?: ?string,
 *   file_upload_date?: string,
 *   file_created_id?: ?int,
 *   file_uri?: string,
 * }
 */
final class ExternalFileFactory {

  /**
   * @phpstan-param externalFileT $values
   */
  public static function create(array $values = []): ExternalFileEntity {
    return ExternalFileEntity::fromArray($values + [
      'id' => 2,
      'file_id' => 3,
      'source' => 'https://example.org/test.txt',
      'filename' => 'test.txt',
      'extension' => 'funding',
      'identifier' => 'test',
      'custom_data' => NULL,
      'status' => 'new',
      'download_start_date' => NULL,
      'download_try_count' => 0,
      'last_modified' => NULL,
      'uri' => 'http://example.org/civicrm/files/test.txt',
      'file_file_type_id' => NULL,
      'file_mime_type' => 'application/octet-stream',
      'file_description' => NULL,
      'file_upload_date' => '2023-07-07 07:07:07',
      'file_created_id' => 1,
      'file_uri' => 'test.txt',
    ]);
  }

}

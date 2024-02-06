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

namespace Civi\Funding\Fixtures;

use Civi\Api4\File;
use Civi\Funding\Entity\AttachmentEntity;

final class AttachmentFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(
    string $entityTable,
    int $entityId,
    string $filename,
    array $values = []
  ): AttachmentEntity {
    $result = civicrm_api3('Attachment', 'create', $values + [
      'entity_table' => $entityTable,
      'entity_id' => $entityId,
      'name' => basename($filename),
      'mime_type' => mime_content_type($filename),
      'content' => file_get_contents($filename),
      'sequential' => 1,
    ]);

    register_shutdown_function(function (string $path) {
      if (is_file($path)) {
        @unlink($path);
      }
    },
      // @phpstan-ignore-next-line
      $result['values'][0]['path']
    );

    // @phpstan-ignore-next-line
    $attachment = AttachmentEntity::fromApi3Values($result['values'][0]);

    // Attachment API ignores file_type_id.
    if (isset($values['file_type_id'])) {
      File::update()
        ->setCheckPermissions(FALSE)
        ->addValue('file_type_id', $values['file_type_id'])
        ->addWhere('id', '=', $attachment->getId())
        ->execute();
    }
    if (isset($values['file_type_id:name'])) {
      File::update()
        ->setCheckPermissions(FALSE)
        ->addValue('file_type_id:name', $values['file_type_id:name'])
        ->addWhere('id', '=', $attachment->getId())
        ->execute();
    }

    return $attachment;
  }

}

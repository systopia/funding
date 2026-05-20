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

namespace Civi\Funding\Entity;

/**
 * @phpstan-type externalFileT array{
 *   id: int,
 *   file_id: int,
 *   source: string,
 *   filename: string,
 *   extension: string,
 *   identifier: string,
 *   custom_data: ?array<mixed>,
 *   status: string,
 *   download_start_date: ?string,
 *   download_try_count: int,
 *   last_modified: ?string,
 *   uri: non-empty-string,
 *   file_file_type_id: ?int,
 *   file_mime_type: string,
 *   file_description: ?string,
 *   file_upload_date: string,
 *   file_created_id: ?int,
 *   file_uri: string,
 * }
 *
 * @phpstan-extends AbstractEntity<externalFileT>
 *
 * @codeCoverageIgnore
 */
final class ExternalFileEntity extends AbstractEntity {

  /**
   * @return int ID of referenced File entity.
   */
  public function getFileId(): int {
    return $this->values['file_id'];
  }

  public function setFileId(int $fileId): self {
    $this->values['file_id'] = $fileId;

    return $this;
  }

  public function getSource(): string {
    return $this->values['source'];
  }

  public function getFilename(): string {
    return $this->values['filename'];
  }

  public function getExtension(): string {
    return $this->values['extension'];
  }

  public function getIdentifier(): string {
    return $this->values['identifier'];
  }

  public function setIdentifier(string $identifier): self {
    $this->values['identifier'] = $identifier;

    return $this;
  }

  /**
   * @phpstan-return array<mixed>|null JSON serializable array or NULL.
   */
  public function getCustomData(): ?array {
    return $this->values['custom_data'];
  }

  /**
   * @phpstan-param array<int|string, mixed>|null $customData JSON serializable.
   */
  public function setCustomData(?array $customData): self {
    $this->values['custom_data'] = $customData;

    return $this;
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function setStatus(string $status): self {
    $this->values['status'] = $status;

    return $this;
  }

  public function getDownloadStartDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['download_start_date']);
  }

  public function setDownloadStartDate(?\DateTimeInterface $downloadStartDate): self {
    $this->values['download_start_date'] = static::toDateTimeStrOrNull($downloadStartDate);

    return $this;
  }

  public function getDownloadTryCount(): int {
    return $this->values['download_try_count'];
  }

  public function incDownloadTryCount(): self {
    ++$this->values['download_try_count'];

    return $this;
  }

  public function getLastModified(): ?string {
    return $this->values['last_modified'];
  }

  public function setLastModified(string $lastModified): self {
    $this->values['last_modified'] = $lastModified;

    return $this;
  }

  /**
   * @phpstan-return non-empty-string
   */
  public function getUri(): string {
    return $this->values['uri'];
  }

  public function getFileFileTypeId(): ?int {
    return $this->values['file_file_type_id'];
  }

  public function getFileMimeType(): string {
    return $this->values['file_mime_type'];
  }

  public function getFileDescription(): ?string {
    return $this->values['file_description'];
  }

  public function getFileCreatedId(): ?int {
    return $this->values['file_created_id'];
  }

  public function getFileUri(): string {
    return $this->values['file_uri'];
  }

}

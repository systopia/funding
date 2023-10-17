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

namespace Civi\Funding\SammelantragKurs\Application\Validation;

use Civi\Funding\Form\Application\ValidatedApplicationDataInterface;

/**
 * @phpstan-type kursValidatedDataT array<string, mixed>&array{
 *   action: string,
 *   grunddaten: array{
 *     titel: string,
 *     kurzbeschreibungDerInhalte: string,
 *     zeitraeume: non-empty-array<array{beginn: string, ende: string}>,
 *   },
 *   zuschuss: array{gesamt: float},
 *   comment?: array{text: string, type: string},
 * }
 * zeitraeume: Entries ordered ascending by "beginn".
 */
final class KursApplicationValidatedData implements ValidatedApplicationDataInterface {

  /**
   * @phpstan-var kursValidatedDataT
   */
  private array $data;

  private int $recipientContactId;

  /**
   * @phpstan-param kursValidatedDataT $validatedData
   */
  public function __construct(array $validatedData, int $recipientContactId) {
    $this->data = $validatedData;
    $this->recipientContactId = $recipientContactId;
  }

  public function getAction(): string {
    return $this->data['action'];
  }

  public function getTitle(): string {
    return $this->data['grunddaten']['titel'];
  }

  public function getShortDescription(): string {
    return $this->data['grunddaten']['kurzbeschreibungDerInhalte'];
  }

  public function getRecipientContactId(): int {
    return $this->recipientContactId;
  }

  public function getStartDate(): \DateTimeInterface {
    return new \DateTime($this->data['grunddaten']['zeitraeume'][0]['beginn']);
  }

  public function getEndDate(): \DateTimeInterface {
    $zeitraeume = $this->data['grunddaten']['zeitraeume'];

    return new \DateTime($zeitraeume[count($zeitraeume) - 1]['ende']);
  }

  public function getAmountRequested(): float {
    return $this->data['zuschuss']['gesamt'];
  }

  public function getComment(): ?array {
    return $this->data['comment'] ?? NULL;
  }

  public function getApplicationData(): array {
    $data = $this->data;
    unset($data['action']);
    unset($data['comment']);

    return $data;
  }

  public function getRawData(): array {
    return $this->data;
  }

}

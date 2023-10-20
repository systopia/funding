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

namespace Civi\Funding\SonstigeAktivitaet\Application\Validation;

use Civi\Funding\Form\Application\ValidatedApplicationDataInterface;

/**
 * @phpstan-type avk1ValidatedDataT array<string, mixed>&array{
 *   action: string,
 *   grunddaten: array{
 *      titel: string,
 *      kurzbeschreibungDesInhalts: string,
 *      zeitraeume: non-empty-array<array{beginn: string, ende: string}>,
 *    },
 *   empfaenger: int,
 *   finanzierung: array{beantragterZuschuss: float},
 *   comment?: array{text: string, type: string},
 * }
 * zeitraeume: Entries ordered ascending by "beginn".
 */
final class AVK1ValidatedData implements ValidatedApplicationDataInterface {

  /**
   * @phpstan-var avk1ValidatedDataT
   */
  private array $data;

  /**
   * @phpstan-param avk1ValidatedDataT $validatedData
   */
  public function __construct(array $validatedData) {
    $this->data = $validatedData;
  }

  public function getAction(): string {
    return $this->data['action'];
  }

  public function getTitle(): string {
    return $this->data['grunddaten']['titel'];
  }

  public function getShortDescription(): string {
    return $this->data['grunddaten']['kurzbeschreibungDesInhalts'];
  }

  public function getRecipientContactId(): int {
    return $this->data['empfaenger'];
  }

  public function getStartDate(): \DateTimeInterface {
    return new \DateTime($this->data['grunddaten']['zeitraeume'][0]['beginn']);
  }

  public function getEndDate(): \DateTimeInterface {
    $zeitraeume = $this->data['grunddaten']['zeitraeume'];

    return new \DateTime($zeitraeume[count($zeitraeume) - 1]['ende']);
  }

  public function getAmountRequested(): float {
    return $this->data['finanzierung']['beantragterZuschuss'];
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

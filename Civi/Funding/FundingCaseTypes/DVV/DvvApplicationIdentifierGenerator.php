<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\DVV;

use Civi\Funding\ApplicationProcess\ApplicationIdentifierGeneratorInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\DvvKursMetaData;
use Civi\Funding\FundingCaseTypes\DVV\SonstigeAktivitaet\AVK1MetaData;

/**
 * Note: This generator can be used with MariaDB, but not MySQL.
 *
 * @codeCoverageIgnore
 */
final class DvvApplicationIdentifierGenerator implements ApplicationIdentifierGeneratorInterface {

  public const SUPPORTED_FUNDING_CASE_TYPES = [
    DvvKursMetaData::NAME,
    AVK1MetaData::NAME,
  ];

  public static function getSequenceName(string $identifierPrefix): string {
    return '`application_identifier_' . self::escapeIdentifier($identifierPrefix) . '`';
  }

  private static function escapeIdentifier(string $identifier): string {
    return strtr($identifier, [
      '`' => '``',
      '-' => '_',
      ';' => '_',
      ',' => '_',
      ' ' => '_',
    ]);
  }

  public function generateIdentifier(
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): string {
    $identifierPrefix = $applicationProcessBundle->getFundingProgram()->getIdentifierPrefix();
    $sequenceName = self::getSequenceName($identifierPrefix);

    $number = \CRM_Core_DAO::singleValueQuery("SELECT NEXTVAL($sequenceName)");
    if (NULL === $number || '' === $number) {
      throw new \RuntimeException("Failed to get next sequence number for $sequenceName");
    }

    return "$identifierPrefix$number";
  }

  public function generateIdentifierOnFundingCaseChange(
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): string {
    return $applicationProcessBundle->getApplicationProcess()->getIdentifier();
  }

}

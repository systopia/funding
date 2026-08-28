<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\DVV\SonstigeAktivitaet;

use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessActions;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemType;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessStatuses;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessActions;

final class AVK1MetaData extends AbstractFundingCaseTypeMetaData {

  public const NAME = AVK1Constants::FUNDING_CASE_TYPE_NAME;

  public function getName(): string {
    return self::NAME;
  }

  /**
   * @phpstan-var array<string, CostItemType>
   */
  private ?array $costItemTypes = NULL;

  /**
   * @phpstan-var array<string, ResourcesItemType>
   */
  private ?array $resourcesItemTypes = NULL;

  /**
   * @inheritDoc
   */
  public function getApplicationProcessActions(): array {
    return DefaultApplicationProcessActions::getAll() + ReworkApplicationProcessActions::getAll();
  }

  /**
   * @inheritDoc
   */
  public function getApplicationProcessStatuses(): array {
    return DefaultApplicationProcessStatuses::getAll() + ReworkApplicationProcessStatuses::getAll();
  }

  /**
   * @inheritDoc
   */
  public function getCostItemTypes(): array {
    return $this->costItemTypes ??= [
      'unterkunftUndVerpflegung' => new CostItemType([
        'name' => 'unterkunftUndVerpflegung',
        'label' => 'Unterkunft und Verpflegung',
        'clearable' => TRUE,
      ]),
      'honorar' => new CostItemType([
        'name' => 'honorar',
        'label' => 'Honorar',
        'clearable' => TRUE,
      ]),
      'fahrtkosten/teilnehmer' => new CostItemType([
        'name' => 'fahrtkosten/teilnehmer',
        'label' => 'Fahrtkosten Teilnehmer*innen',
        'clearable' => TRUE,
      ]),
      'fahrtkosten/referenten' => new CostItemType([
        'name' => 'fahrtkosten/referenten',
        'label' => 'Fahrtkosten Referent*innen',
        'clearable' => TRUE,
      ]),
      'sachkosten/ausstattung' => new CostItemType([
        'name' => 'sachkosten/ausstattung',
        'label' => 'Sachkosten',
        'clearable' => TRUE,
      ]),
      'sonstigeAusgabe' => new CostItemType([
        'name' => 'sonstigeAusgabe',
        'label' => 'Sonstige Ausgabe',
        'clearable' => TRUE,
      ]),
    ];
  }

  /**
   * @inheritDoc
   */
  public function getFundingCaseActions(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemTypes(): array {
    return $this->resourcesItemTypes ??= [
      'teilnehmerbeitraege' => new ResourcesItemType([
        'name' => 'teilnehmerbeitraege',
        'label' => 'Teilnehmer*innenbeiträge',
        'clearable' => TRUE,
      ]),
      'eigenmittel' => new ResourcesItemType([
        'name' => 'eigenmittel',
        'label' => 'Eigenmittel',
        'clearable' => TRUE,
      ]),
      'spenden' => new ResourcesItemType([
        'name' => 'spenden',
        'label' => 'Spenden',
        'clearable' => TRUE,
      ]),
      'oeffentlicheMittel/europa' => new ResourcesItemType([
        'name' => 'oeffentlicheMittel/europa',
        'label' => 'Finanzierung durch Europa-Mittel',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
      'oeffentlicheMittel/bundeslaender' => new ResourcesItemType([
        'name' => 'oeffentlicheMittel/bundeslaender',
        'label' => 'Finanzierung durch Bundesländer',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
      'oeffentlicheMittel/staedteUndKreise' => new ResourcesItemType([
        'name' => 'oeffentlicheMittel/staedteUndKreise',
        'label' => 'Finanzierung durch Städte und Kreise',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
    ];
  }

}

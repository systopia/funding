<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\IJB\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type kostenT array{
 *    unterkunftUndVerpflegung: float,
 *    honorare: array<array{
 *       _identifier?: string,
 *       stunden: float,
 *       verguetung: float,
 *       leistung: string,
 *       qualifikation: string,
 *       betrag: float,
 *     }>,
 *     fahrtkosten: array{
 *       flug: float,
 *       programm: float,
 *       anTeilnehmerErstattet: float,
 *     },
 *     programmkosten: array{
 *       programmkosten: float,
 *       arbeitsmaterial: float,
 *     },
 *     sonstigeKosten: array<array{
 *       _identifier?: string,
 *       gegenstand: string,
 *       betrag: float,
 *     }>,
 *     sonstigeAusgaben: array<array{
 *       _identifier?: string,
 *       zweck: string,
 *       betrag: float,
 *     }>,
 *     zuschlagsrelevanteKosten: array{
 *       programmabsprachen: float,
 *       vorbereitungsmaterial: float,
 *       veroeffentlichungen: float,
 *       honorare: float,
 *       fahrtkostenUndVerpflegung: float,
 *       reisekosten: float,
 *       miete: float,
 *     },
 *  }
 */
class IJBFormDataKostenFactory {

  private ApplicationCostItemManager $costItemManager;

  public function __construct(ApplicationCostItemManager $costItemManager) {
    $this->costItemManager = $costItemManager;
  }

  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh

  /**
   * @phpstan-return kostenT
   *
   * @throws \CRM_Core_Exception
   */
  public function createKosten(ApplicationProcessEntity $applicationProcess): array {
    $kosten = [
      'unterkunftUndVerpflegung' => 0.0,
      'honorare' => [],
      'fahrtkosten' => [
        'flug' => 0.0,
        'programm' => 0.0,
        'anTeilnehmerErstattet' => 0.0,
      ],
      'programmkosten' => [
        'programmkosten' => 0.0,
        'arbeitsmaterial' => 0.0,
      ],
      'sonstigeKosten' => [],
      'sonstigeAusgaben' => [],
      'zuschlagsrelevanteKosten' => [
        'programmabsprachen' => 0.0,
        'vorbereitungsmaterial' => 0.0,
        'veroeffentlichungen' => 0.0,
        'honorare' => 0.0,
        'fahrtkostenUndVerpflegung' => 0.0,
        'reisekosten' => 0.0,
        'miete' => 0.0,
      ],
    ];

    $items = $this->costItemManager->getByApplicationProcessId($applicationProcess->getId());
    foreach ($items as $item) {
      [$type, $subType] = explode('/', $item->getType(), 2) + [NULL, NULL];
      if (NULL !== $subType && is_array($kosten[$type] ?? NULL) && isset($kosten[$type][$subType])) {
        $kosten[$type][$subType] = $item->getAmount();
        /** @phpstan-var kostenT $kosten */
      }
      elseif (NULL === $subType && is_float($kosten[$type] ?? NULL)) {
        $kosten[$type] = $item->getAmount();
        /** @phpstan-var kostenT $kosten */
      }
      elseif ('honorar' === $type) {
        $stunden = $item->getProperties()['stunden'];
        Assert::numeric($stunden);
        $verguetung = $item->getProperties()['verguetung'];
        Assert::numeric($verguetung);
        $leistung = $item->getProperties()['leistung'] ?? '';
        Assert::string($leistung);
        $qualifikation = $item->getProperties()['qualifikation'] ?? '';
        Assert::string($qualifikation);
        $kosten['honorare'][] = [
          '_identifier' => $item->getIdentifier(),
          'betrag' => $item->getAmount(),
          'stunden' => (float) $stunden,
          'verguetung' => (float) $verguetung,
          'leistung' => $leistung,
          'qualifikation' => $qualifikation,
        ];
      }
      elseif ('sonstigeKosten' === $type) {
        $gegenstand = $item->getProperties()['gegenstand'];
        Assert::string($gegenstand);
        $kosten['sonstigeKosten'][] = [
          '_identifier' => $item->getIdentifier(),
          'betrag' => $item->getAmount(),
          'gegenstand' => $gegenstand,
        ];
      }
      elseif ('sonstigeAusgabe' === $type) {
        $leistung = $item->getProperties()['zweck'];
        Assert::string($leistung);
        $kosten['sonstigeAusgaben'][] = [
          '_identifier' => $item->getIdentifier(),
          'betrag' => $item->getAmount(),
          'zweck' => $leistung,
        ];
      }
    }

    return $kosten;
  }

  // phpcs:enable

}

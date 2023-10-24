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

namespace Civi\Funding\SonstigeAktivitaet\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type kostenT array{
 *   unterkunftUndVerpflegung: float,
 *   honorare: array<array{
 *     _identifier: string,
 *     berechnungsgrundlage: string,
 *     dauer: float,
 *     verguetung: float,
 *     leistung: string,
 *     qualifikation: string,
 *     betrag: float,
 *   }>,
 *   fahrtkosten: array{
 *     intern: float,
 *     anTeilnehmerErstattet: float,
 *   },
 *   sachkosten: array{
 *     ausstattung: array<array{
 *       _identifier: string,
 *       gegenstand: string,
 *       betrag: float,
 *     }>,
 *   },
 *   sonstigeAusgaben: array<array{
 *     _identifier: string,
 *     betrag: float,
 *     zweck: string,
 *   }>,
 *   versicherung: array{
 *     teilnehmer: float,
 *   }
 * }
 */
class AVK1KostenFactory {

  private ApplicationCostItemManager $costItemManager;

  public function __construct(ApplicationCostItemManager $costItemManager) {
    $this->costItemManager = $costItemManager;
  }

  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh

  /**
   * @phpstan-return kostenT
   */
  public function createKosten(ApplicationProcessEntity $applicationProcess): array {
    $kosten = [
      'unterkunftUndVerpflegung' => 0.0,
      'honorare' => [],
      'fahrtkosten' => [
        'anTeilnehmerErstattet' => 0.0,
        'intern' => 0.0,
      ],
      'sachkosten' => [
        'ausstattung' => [],
      ],
      'sonstigeAusgaben' => [],
      'versicherung' => [
        'teilnehmer' => 0.0,
      ],
    ];

    $items = $this->costItemManager->getByApplicationProcessId($applicationProcess->getId());
    foreach ($items as $item) {
      [$type, $subType] = explode('/', $item->getType(), 2) + [NULL, NULL];
      if ('unterkunftUndVerpflegung' === $type) {
        $kosten['unterkunftUndVerpflegung'] = $item->getAmount();
      }
      elseif ('honorar' === $type) {
        $berechnungsgrundlage = $item->getProperties()['berechnungsgrundlage'];
        Assert::string($berechnungsgrundlage);
        $dauer = $item->getProperties()['dauer'];
        Assert::numeric($dauer);
        $verguetung = $item->getProperties()['verguetung'];
        Assert::numeric($verguetung);
        $leistung = $item->getProperties()['leistung'] ?? '';
        Assert::string($leistung);
        $qualifikation = $item->getProperties()['qualifikation'] ?? '';
        Assert::string($qualifikation);
        $kosten['honorare'][] = [
          '_identifier' => $item->getIdentifier(),
          'betrag' => $item->getAmount(),
          'berechnungsgrundlage' => $berechnungsgrundlage,
          'dauer' => (float) $dauer,
          'verguetung' => (float) $verguetung,
          'leistung' => $leistung,
          'qualifikation' => $qualifikation,
        ];
      }
      elseif ('fahrtkosten' === $type) {
        if ('intern' === $subType || 'anTeilnehmerErstattet' === $subType) {
          $kosten['fahrtkosten'][$subType] = $item->getAmount();
        }
      }
      elseif ('sachkosten' === $type) {
        if ('ausstattung' === $subType) {
          $gegenstand = $item->getProperties()['gegenstand'];
          Assert::string($gegenstand);
          $kosten['sachkosten']['ausstattung'][] = [
            '_identifier' => $item->getIdentifier(),
            'betrag' => $item->getAmount(),
            'gegenstand' => $gegenstand,
          ];
        }
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
      elseif ('versicherung' === $type) {
        if ('teilnehmer' === $subType) {
          $kosten['versicherung']['teilnehmer'] = $item->getAmount();
        }
      }
    }

    return $kosten;
  }

  // phpcs:enable

}

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

namespace Civi\Funding\SonstigeAktivitaet;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type kostenT array{
 *   unterkunftUndVerpflegung: float,
 *   honorare: array<array{
 *     _identifier: string,
 *     stunden: float,
 *     verguetung: float,
 *     zweck: string,
 *     betrag: float,
 *   }>,
 *   fahrtkosten: array{
 *     intern: float,
 *     anTeilnehmerErstattet: float,
 *   },
 *   sachkosten: array{
 *     haftungKfz: float,
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
 *   versicherungTeilnehmer: float,
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
        'haftungKfz' => 0.0,
        'ausstattung' => [],
      ],
      'sonstigeAusgaben' => [],
      'versicherungTeilnehmer' => 0.0,
    ];

    $items = $this->costItemManager->getByApplicationProcessId($applicationProcess->getId());
    foreach ($items as $item) {
      [$type, $subType] = explode('/', $item->getType(), 2) + [NULL, NULL];
      if ('unterkunftUndVerpflegung' === $type) {
        $kosten['unterkunftUndVerpflegung'] = $item->getAmount();
      }
      elseif ('honorar' === $type) {
        $stunden = $item->getProperties()['stunden'];
        Assert::numeric($stunden);
        $verguetung = $item->getProperties()['verguetung'];
        Assert::numeric($verguetung);
        $zweck = $item->getProperties()['zweck'];
        Assert::string($zweck);
        $kosten['honorare'][] = [
          '_identifier' => $item->getIdentifier(),
          'betrag' => $item->getAmount(),
          'stunden' => (float) $stunden,
          'verguetung' => (float) $verguetung,
          'zweck' => $zweck,
        ];
      }
      elseif ('fahrtkosten' === $type) {
        if ('intern' === $subType || 'anTeilnehmerErstattet' === $subType) {
          $kosten['fahrtkosten'][$subType] = $item->getAmount();
        }
      }
      elseif ('sachkosten' === $type) {
        if ('haftungKfz' === $subType) {
          $kosten['sachkosten']['haftungKfz'] = $item->getAmount();
        }
        elseif ('ausstattung' === $subType) {
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
        $zweck = $item->getProperties()['zweck'];
        Assert::string($zweck);
        $kosten['sonstigeAusgaben'][] = [
          '_identifier' => $item->getIdentifier(),
          'betrag' => $item->getAmount(),
          'zweck' => $zweck,
        ];
      }
      elseif ('versicherungTeilnehmer' === $type) {
        $kosten['versicherungTeilnehmer'] = $item->getAmount();
      }
    }

    return $kosten;
  }

  // phpcs:enable

}

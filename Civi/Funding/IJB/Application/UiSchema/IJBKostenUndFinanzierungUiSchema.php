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

namespace Civi\Funding\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;

final class IJBKostenUndFinanzierungUiSchema extends JsonFormsCategory {

  public function __construct(string $currency) {
    parent::__construct('Geplante Kosten & Finanzierung',
      [
        new IJBKostenUiSchema($currency),
        new IJBFinanzierungUiSchema($currency),
      ],
      <<<'EOD'
Der Kosten- und Finanzierungsplan muss ausgeglichen sein, damit der Antrag
beantragt werden kann. Wenn die Finanzierung noch nicht endgültig geklärt ist,
kann ein möglicher Fehlbetrag vorübergehend bei Sonstige Mittel als N.N.
eingetragen werden, wenn diese noch akquiriert werden sollen.
EOD
    );
  }

}

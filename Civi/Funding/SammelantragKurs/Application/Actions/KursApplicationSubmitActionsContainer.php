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

namespace Civi\Funding\SammelantragKurs\Application\Actions;

use Civi\Funding\ApplicationProcess\ActionsContainer\AbstractApplicationSubmitActionsContainer;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

/**
 * @codeCoverageIgnore
 */
final class KursApplicationSubmitActionsContainer extends AbstractApplicationSubmitActionsContainer {

  use KursSupportedFundingCaseTypesTrait;

  public function __construct() {
    $this
      ->add('save&new', 'Speichern und neu')
      ->add('save', 'Speichern')
      ->add('modify', 'Bearbeiten')
      ->add('withdraw', 'Zurückziehen', 'Möchten Sie diesen Kurs wirklich zurückziehen?')
      ->add('delete', 'Löschen', 'Möchten Sie diesen Kurs wirklich löschen?');
  }

}
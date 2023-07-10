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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\Command\ApplicationFilesAddIdentifiersCommand;

interface ApplicationFilesAddIdentifiersHandlerInterface {

  public const SERVICE_TAG = 'funding.application.files_add_identifiers_handler';

  /**
   * Adds identifiers (UUIDs) to files in request data of application process,
   * where necessary. This identifiers can later be used when storing the
   * external files.
   *
   * @return array<string, mixed> Request data with added identifiers.
   */
  public function handle(ApplicationFilesAddIdentifiersCommand $command): array;

}

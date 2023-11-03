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

namespace Civi\Funding\Api4;

use Civi\Api4\Generic\DAOCreateAction;
use Civi\Api4\Generic\DAODeleteAction;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Generic\DAOSaveAction;
use Civi\Api4\Generic\DAOUpdateAction;

interface DAOActionFactoryInterface {

  public function create(string $entityName, bool $checkPermissions = FALSE): DAOCreateAction;

  public function delete(string $entityName, bool $checkPermissions = FALSE): DAODeleteAction;

  public function get(string $entityName, bool $checkPermissions = FALSE): DAOGetAction;

  public function getFields(string $entityName, bool $checkPermissions = FALSE): DAOGetFieldsAction;

  public function save(string $entityName, bool $checkPermissions = FALSE): DAOSaveAction;

  public function update(string $entityName, bool $checkPermissions = FALSE): DAOUpdateAction;

}

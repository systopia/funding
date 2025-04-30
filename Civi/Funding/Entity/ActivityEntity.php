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

namespace Civi\Funding\Entity;

/**
 * @phpstan-import-type activityT from AbstractActivityEntity
 *
 * status_id:name can be used on create or update, but is normally not returned
 * on get.
 *
 * assignee_contact_id can be used on create or update, but is not returned on
 * get.
 *
 * @phpstan-extends AbstractActivityEntity<activityT>
 *
 * @codeCoverageIgnore
 */
final class ActivityEntity extends AbstractActivityEntity {

}

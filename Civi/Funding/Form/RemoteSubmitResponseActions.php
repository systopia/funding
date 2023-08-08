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

namespace Civi\Funding\Form;

final class RemoteSubmitResponseActions {

  public const CLOSE_FORM = 'closeForm';

  /**
   * Has to be combined with attributes "entity_type" and "entity_id".
   */
  public const LOAD_ENTITY = 'loadEntity';

  public const RELOAD_FORM = 'reloadForm';

  /**
   * Has to be combined with attribute "errors".
   */
  public const SHOW_VALIDATION = 'showValidation';

}

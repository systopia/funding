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

namespace Civi\Funding\FundingCase\Actions;

final class FundingCaseActions {

  public const APPROVE = 'approve';

  public const DELETE = 'delete';

  public const FINISH_CLEARING = 'finish-clearing';

  public const RECREATE_TRANSFER_CONTRACT = 'recreate-transfer-contract';

  public const SET_RECIPIENT_CONTACT = 'set-recipient-contact';

  public const UPDATE_AMOUNT_APPROVED = 'update-amount-approved';

}

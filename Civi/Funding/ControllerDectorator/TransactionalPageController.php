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

namespace Civi\Funding\ControllerDectorator;

use Civi\Funding\Controller\PageControllerInterface;
use Civi\RemoteTools\Database\TransactionFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TransactionalPageController implements PageControllerInterface {

  private PageControllerInterface $controller;

  private TransactionFactory $transactionFactory;

  public function __construct(PageControllerInterface $controller, TransactionFactory $transactionFactory) {
    $this->controller = $controller;
    $this->transactionFactory = $transactionFactory;
  }

  /**
   * @inheritDoc
   */
  public function handle(Request $request): Response {
    $transaction = $this->transactionFactory->createTransaction();
    try {
      $result = $this->controller->handle($request);
      $transaction->commit();

      return $result;
    }
    catch (\Throwable $e) {
      $transaction->rollback()->commit();

      throw $e;
    }
  }

}

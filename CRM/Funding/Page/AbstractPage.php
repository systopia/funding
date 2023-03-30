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

use Civi\Funding\Controller\PageControllerInterface;
use Civi\Funding\Session\FundingSessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * @codeCoverageIgnore
 *
 * phpcs:disable Generic.NamingConventions.AbstractClassNamePrefix.Missing
 */
abstract class CRM_Funding_Page_AbstractPage extends \CRM_Core_Page {

  public function run(): void {
    $request = Request::createFromGlobals();
    try {
      $response = $this->handle($request);
    }
    catch (HttpExceptionInterface $e) {
      \Civi::log()->info(
        sprintf(
          'Access to "%s" failed with status code %d: %s',
          $request->getRequestUri(),
          $e->getStatusCode(),
          $e->getMessage(),
        ),
        [
          'exception' => $e,
          'sessionContactId' => $this->getSessionContactId(),
        ],
      );

      $message = $e->getMessage() !== '' ? $e->getMessage() : (Response::$statusTexts[$e->getStatusCode()] ?? '');
      $response = new Response($message, $e->getStatusCode(), $e->getHeaders());
    }

    $response->send();
    \CRM_Utils_System::civiExit();
  }

  abstract protected function getController(): PageControllerInterface;

  /**
   * @throws \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
   */
  protected function handle(Request $request): Response {
    return $this->getController()->handle($request);
  }

  private function getSessionContactId(): ?int {
    /** @var \Civi\Funding\Session\FundingSessionInterface $session */
    $session = \Civi::service(FundingSessionInterface::class);

    try {
      return $session->getContactId();
    }
    catch (\Exception $e) {
      if ($session->isRemote()) {
        // Resolving remote contact ID failed.
        return NULL;
      }

      throw $e;
    }
  }

}

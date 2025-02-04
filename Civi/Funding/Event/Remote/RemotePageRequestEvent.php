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

namespace Civi\Funding\Event\Remote;

use Civi\Funding\Page\AbstractRemoteControllerPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

final class RemotePageRequestEvent extends Event {

  private AbstractRemoteControllerPage $page;

  private Request $request;

  public function __construct(AbstractRemoteControllerPage $page, Request $request) {
    $this->page = $page;
    $this->request = $request;
  }

  public function getPage(): AbstractRemoteControllerPage {
    return $this->page;
  }

  public function getRequest(): Request {
    return $this->request;
  }

}

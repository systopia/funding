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

namespace Civi\Funding;

use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Psr\Container\ContainerInterface;

final class FundingCaseTypeServiceLocator implements FundingCaseTypeServiceLocatorInterface {

  private ContainerInterface $locator;

  public function __construct(ContainerInterface $locator) {
    $this->locator = $locator;
  }

  public function getApplicationFormNewCreateHandler(): ApplicationFormNewCreateHandlerInterface {
    return $this->locator->get(ApplicationFormNewCreateHandlerInterface::class);
  }

  public function getApplicationFormNewValidateHandler(): ApplicationFormNewValidateHandlerInterface {
    return $this->locator->get(ApplicationFormNewValidateHandlerInterface::class);
  }

  public function getApplicationFormNewSubmitHandler(): ApplicationFormNewSubmitHandlerInterface {
    return $this->locator->get(ApplicationFormNewSubmitHandlerInterface::class);
  }

  public function getApplicationFormDataGetHandler(): ApplicationFormDataGetHandlerInterface {
    return $this->locator->get(ApplicationFormDataGetHandlerInterface::class);
  }

  public function getApplicationFormCreateHandler(): ApplicationFormCreateHandlerInterface {
    return $this->locator->get(ApplicationFormCreateHandlerInterface::class);
  }

  public function getApplicationFormValidateHandler(): ApplicationFormValidateHandlerInterface {
    return $this->locator->get(ApplicationFormValidateHandlerInterface::class);
  }

  public function getApplicationFormSubmitHandler(): ApplicationFormSubmitHandlerInterface {
    return $this->locator->get(ApplicationFormSubmitHandlerInterface::class);
  }

}
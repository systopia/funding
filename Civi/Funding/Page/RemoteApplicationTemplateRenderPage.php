<?php

declare(strict_types = 1);

namespace Civi\Funding\Page;

use Civi\Funding\Controller\ApplicationTemplateRenderController;
use Civi\Funding\Controller\PageControllerInterface;

/**
 * @codeCoverageIgnore
 */
final class RemoteApplicationTemplateRenderPage extends AbstractRemoteControllerPage {

  protected function getController(): PageControllerInterface {
    return \Civi::service(ApplicationTemplateRenderController::class);
  }

}

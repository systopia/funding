<?php

declare(strict_types = 1);

use Civi\Funding\Controller\PageControllerInterface;
use Civi\Funding\Controller\ApplicationTemplateRenderController;

/**
 * @codeCoverageIgnore
 */
final class CRM_Funding_Page_RemoteApplicationTemplateRender extends CRM_Funding_Page_AbstractRemotePage {

  protected function getController(): PageControllerInterface {
    return \Civi::service(ApplicationTemplateRenderController::class);
  }

}

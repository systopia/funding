<?php

declare(strict_types = 1);

use Civi\Funding\Controller\PageControllerInterface;
use Civi\Funding\Controller\DrawdownDocumentDownloadController;

/**
 * @codeCoverageIgnore
 */
class CRM_Funding_Page_DrawdownDocumentDownload extends CRM_Funding_Page_AbstractPage {

  protected function getController(): PageControllerInterface {
    return \Civi::service(DrawdownDocumentDownloadController::class);
  }

}

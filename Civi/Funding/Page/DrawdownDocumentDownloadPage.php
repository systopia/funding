<?php

declare(strict_types = 1);

namespace Civi\Funding\Page;

use Civi\Funding\Controller\PageControllerInterface;
use Civi\Funding\Controller\DrawdownDocumentDownloadController;

/**
 * @codeCoverageIgnore
 */
final class DrawdownDocumentDownloadPage extends AbstractControllerPage {

  protected function getController(): PageControllerInterface {
    return \Civi::service(DrawdownDocumentDownloadController::class);
  }

}

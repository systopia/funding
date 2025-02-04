<?php

declare(strict_types = 1);

namespace Civi\Funding\Page;

use Civi\Funding\Controller\PageControllerInterface;
use Civi\Funding\Controller\TransferContractDownloadController;

/**
 * @codeCoverageIgnore
 */
class RemoteTransferContractDownloadPage extends AbstractRemoteControllerPage {

  protected function getController(): PageControllerInterface {
    return \Civi::service(TransferContractDownloadController::class);
  }

}

<?php

declare(strict_types = 1);

namespace Civi\Funding\Page;

use Civi\Funding\Controller\DrawdownAcceptController;
use Civi\Funding\Controller\PageControllerInterface;

/**
 * @codeCoverageIgnore
 */
final class DrawdownAcceptPage extends AbstractControllerPage {

  protected function getController(): PageControllerInterface {
    return \Civi::service(DrawdownAcceptController::class);
  }

}

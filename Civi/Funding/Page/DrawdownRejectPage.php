<?php

declare(strict_types = 1);

namespace Civi\Funding\Page;

use Civi\Funding\Controller\PageControllerInterface;
use Civi\Funding\Controller\DrawdownRejectController;

/**
 * @codeCoverageIgnore
 */
final class DrawdownRejectPage extends AbstractControllerPage {

  protected function getController(): PageControllerInterface {
    return \Civi::service(DrawdownRejectController::class);
  }

}

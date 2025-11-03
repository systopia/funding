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

namespace Civi\Funding\DocumentRender\CiviOffice;

use Civi\Funding\Util\TestFileUtil;

final class UnoconvLocalTestConfigurator {

  private const UNOCONV_BINARY_PATH_SETTINGS_KEY = 'unoconv_binary_path';

  private const UNOCONV_LOCK_FILE_PATH_SETTINGS_KEY = 'unoconv_lock_file_path';

  private const PHPWORD_TOKENS_SETTINGS_KEY = 'phpword_tokens';

  private static ?string $unoconv;

  private static bool $unoconvSearched = FALSE;

  public static function isAvailable(): bool {
    return NULL !== self::findUnoconv();
  }

  public static function configure(): void {
    $tmpDir = TestFileUtil::createTempDir('/funding-phpunit-test');
    \Civi::settings()->set(\CRM_Civioffice_DocumentStore_Local::LOCAL_TEMP_PATH_SETTINGS_KEY, $tmpDir);
    $unoconvLockFile = $tmpDir . '/unoconv.lock';
    \Civi::settings()->set('civioffice_renderer_unoconv-local', [
      'type' => 'unoconv-local',
      self::UNOCONV_BINARY_PATH_SETTINGS_KEY => self::getUnoconv(),
      self::UNOCONV_LOCK_FILE_PATH_SETTINGS_KEY => $unoconvLockFile,
      self::PHPWORD_TOKENS_SETTINGS_KEY => TRUE,
    ]);
    \Civi::settings()->set('civioffice_renderers', ['unoconv-local' => 'unoconv Renderer']);
    touch($unoconvLockFile);
  }

  private static function findUnoconv(): ?string {
    $unoconv = getenv('UNOCONV');
    if (is_string($unoconv) && '' !== $unoconv) {
      if (!is_executable($unoconv)) {
        throw new \RuntimeException(
          sprintf('The path "%s" in environment variable "UNOCONV" ist not executable', $unoconv)
        );
      }

      return $unoconv;
    }

    // @phpstan-ignore-next-line
    $paths = explode(\PATH_SEPARATOR, getenv('PATH'));
    foreach ($paths as $path) {
      $unoconv = $path . '/unoconv';
      if (is_executable($unoconv)) {
        return $unoconv;
      }
    }

    return NULL;
  }

  private static function getUnoconv(): ?string {
    if (!self::$unoconvSearched) {
      self::$unoconv = self::findUnoconv();
      self::$unoconvSearched = TRUE;
    }

    return self::$unoconv;
  }

}

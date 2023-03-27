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

namespace Civi\Funding\Util;

use Webmozart\Assert\Assert;

final class TestFileUtil {

  public static function createTempFile(string $prefix = 'funding-phpunit-test'): string {
    $tmpFile = tempnam(sys_get_temp_dir(), $prefix);
    Assert::string($tmpFile);
    static::deleteFileOnScriptShutdown($tmpFile);

    return $tmpFile;
  }

  public static function createTempDir(string $name): string {
    $tmpDir = sys_get_temp_dir() . '/' . $name;
    self::removeRecursive($tmpDir);
    mkdir($tmpDir);
    register_shutdown_function(
      function (string $tmpDir) {
        self::removeRecursive($tmpDir);
      }, $tmpDir
    );

    return $tmpDir;
  }

  public static function deleteFileOnScriptShutdown(string $filename): void {
    register_shutdown_function(function (string $filename) {
      if (is_file($filename)) {
        unlink($filename);
      }
    }, $filename);
  }

  private static function removeRecursive(string $dir): bool {
    if (!is_dir($dir)) {
      return FALSE;
    }

    /** @var \SplFileInfo[] $files */
    $files = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileInfo) {
      if ($fileInfo->isDir()) {
        if (!@rmdir($fileInfo->getPathname())) {
          return FALSE;
        }
      }
      elseif (!@unlink($fileInfo->getPathname())) {
        return FALSE;
      }
    }

    return @rmdir($dir);
  }

}

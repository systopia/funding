<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Translation\EventSubscriber;

use Civi\Core\DAO\Event\PreUpdate;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class TranslationStringPreUpdateSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return ['civi.dao.preUpdate' => 'onPreUpdate'];
  }

  public function onPreUpdate(PreUpdate $event): void {
    // \CRM_Core_DAO::copyValues() replaces the empty string with "null", so we
    // have to revert this. In case somebody really wants to persist "null", it
    // would have been replaced by "Null" in FormattingUtil::formatWriteParams()
    if ('civicrm_funding_form_string_translation' === $event->object->tableName()) {
      if ('null' === $event->object->new_text) {
        $event->object->new_text = '';
      }
      elseif (NULL !== $event->object->new_text) {
        $sanitizerConfig = (new HtmlSanitizerConfig())->allowSafeElements();
        $htmlSanitizer = new HtmlSanitizer($sanitizerConfig);
        $event->object->new_text = $htmlSanitizer->sanitize($event->object->new_text);
      }
    }
  }

}

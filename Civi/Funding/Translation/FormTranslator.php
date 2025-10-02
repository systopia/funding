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

namespace Civi\Funding\Translation;

use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\JsonFormsFormInterface;

final class FormTranslator implements FormTranslatorInterface {

  private JsonSchemaStringTranslator $jsonSchemaStringTranslator;

  private FormStringTranslationLoader $translationLoader;

  private UiSchemaStringTranslator $uiSchemaStringTranslator;

  public function __construct(
    JsonSchemaStringTranslator $jsonSchemaStringTranslator,
    FormStringTranslationLoader $translationLoader,
    UiSchemaStringTranslator $uiSchemaStringTranslator
  ) {
    $this->jsonSchemaStringTranslator = $jsonSchemaStringTranslator;
    $this->translationLoader = $translationLoader;
    $this->uiSchemaStringTranslator = $uiSchemaStringTranslator;
  }

  public function translateForm(
    JsonFormsFormInterface $form,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): void {
    $translations = $this->translationLoader->getTranslations($fundingProgram, $fundingCaseType);
    // Even if $translation is empty, the form spec might contain texts that
    // need to be formatted so we have to call translateStrings() anyway.
    $defaultLocale = \CRM_Core_I18n::getLocale();
    $this->jsonSchemaStringTranslator->translateStrings($form->getJsonSchema(), $translations, $defaultLocale);
    $this->uiSchemaStringTranslator->translateStrings($form->getUiSchema(), $translations, $defaultLocale);
  }

}

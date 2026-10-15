<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Token;

use Civi\Funding\Api4\Util\ContactUtil;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\DocumentRender\Token\ResolvedToken;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\DocumentRender\Token\TokenValueLoader;
use Civi\Funding\DocumentRender\Token\ValueConverter;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Form\Application\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\JsonSchema\Util\JsonSchemaValueUtil;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use Webmozart\Assert\Assert;

/**
 * @implements TokenResolverInterface<\Civi\Funding\Entity\ApplicationProcessEntity>
 */
class ApplicationProcessTokenResolver implements TokenResolverInterface {

  /**
   * @var array<int, JsonSchema>
   */
  private $jsonSchemas = [];

  /**
   * @phpstan-param TokenResolverInterface<\Civi\Funding\Entity\ApplicationProcessEntity> $tokenResolver
   */
  public function __construct(
    private readonly Api4Interface $api4,
    private readonly ApplicationProcessManager $applicationProcessManager,
    private readonly ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    private readonly TokenResolverInterface $tokenResolver,
    private readonly TokenValueLoader $tokenValueLoader,
  ) {}

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function resolveToken(string $entityName, AbstractEntity $entity, string $tokenName): ResolvedToken {
    return match ($tokenName) {
      'first_application_contact_display_name' => new ResolvedToken(
        $this->getContactDisplayName($entity->getFirstApplicationContactId()), 'text/plain'
      ),
      'last_application_contact_display_name' => new ResolvedToken(
        $this->getContactDisplayName($entity->getLastApplicationContactId()), 'text/plain'
      ),
      default => 1 === preg_match('/^request_data::.+:label$/', $tokenName)
        ? $this->resolveTokenLabel($entityName, $entity, $tokenName)
        : $this->tokenResolver->resolveToken($entityName, $entity, $tokenName)
    };
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getContactDisplayName(?int $contactId): string {
    if (NULL === $contactId) {
      return '';
    }

    /** @phpstan-var array{id: int, display_name: string|null} $contact */
    $contact = $this->api4->execute('Contact', 'get', [
      'select' => ['id', 'display_name'],
      'where' => [['id', '=', $contactId]],
    ])->single();

    return ContactUtil::getDisplayName($contact);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function resolveTokenLabel(
    string $entityName,
    ApplicationProcessEntity $applicationProcess,
    string $tokenName
  ): ResolvedToken {
    $tokenName = substr($tokenName, 0, -6);
    [$fieldName, $path] = explode('::', $tokenName, 2);
    $value = $this->tokenValueLoader->getValue($entityName, $applicationProcess, $tokenName);

    $jsonSchema = $this->getJsonSchema($applicationProcess);
    $propertySchema = JsonSchemaUtil::getPropertySchemaAt($jsonSchema, explode('.', $path));

    if (NULL === $propertySchema) {
      return ValueConverter::toResolvedToken($value);
    }

    if (is_array($value)) {
      Assert::allNullOrScalar($value);

      return ValueConverter::toResolvedToken(
        array_map(fn ($v) => JsonSchemaValueUtil::valueToString($v, $propertySchema), $value)
      );
    }

    Assert::nullOrScalar($value);

    return ValueConverter::toResolvedToken(JsonSchemaValueUtil::valueToString($value, $propertySchema));
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getJsonSchema(ApplicationProcessEntity $applicationProcess): JsonSchema {
    if (isset($this->jsonSchemas[$applicationProcess->getId()])) {
      return $this->jsonSchemas[$applicationProcess->getId()];
    }

    $applicationProcessBundle = $this->applicationProcessManager->getBundleForObject($applicationProcess);
    $statusList = $this->applicationProcessManager->getStatusList($applicationProcessBundle);

    return $this->jsonSchemas[$applicationProcess->getId()]
      = $this->jsonSchemaFactory->createJsonSchemaExisting($applicationProcessBundle, $statusList);
  }

}

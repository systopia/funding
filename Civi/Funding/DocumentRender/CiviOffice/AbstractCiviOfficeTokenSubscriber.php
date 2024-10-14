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

use Civi\Core\Event\GenericHookEvent;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Token\AbstractTokenSubscriber;
use Civi\Token\Event\TokenValueEvent;
use Civi\Token\TokenProcessor;
use Civi\Token\TokenRow;
use Webmozart\Assert\Assert;

/**
 * @template T of \Civi\Funding\Entity\AbstractEntity
 */
abstract class AbstractCiviOfficeTokenSubscriber extends AbstractTokenSubscriber {

  protected CiviOfficeContextDataHolder $contextDataHolder;

  /**
   * @phpstan-var TokenResolverInterface<T>
   */
  protected TokenResolverInterface $tokenResolver;

  public static function getPriority(): int {
    return 0;
  }

  public static function getSubscribedEvents(): array {
    return [
      'civi.civioffice.tokenContext' => ['onCiviOfficeTokenContext', static::getPriority()],
      // The priority is necessary to add related values to the context.
      'civi.token.eval' => ['evaluateTokens', static::getPriority()],
    ] + parent::getSubscribedEvents();
  }

  /**
   * @phpstan-param TokenResolverInterface<T> $tokenResolver
   */
  public function __construct(
    CiviOfficeContextDataHolder $contextDataHolder,
    TokenResolverInterface $tokenResolver,
    TokenNameExtractorInterface $tokenNameExtractor
  ) {
    $this->contextDataHolder = $contextDataHolder;
    $this->tokenResolver = $tokenResolver;
    parent::__construct(
      \CRM_Core_DAO_AllCoreTables::convertEntityNameToLower($this->getApiEntityName()),
      $tokenNameExtractor->getTokenNames($this->getApiEntityName(), $this->getEntityClass()),
    );
  }

  public function onCiviOfficeTokenContext(GenericHookEvent $event): void {
    if ($this->getApiEntityName() === $event->entity_type) {
      $event->context[$this->getContextKey()] = $this->getEntity($event->entity_id);
      Assert::notNull(
        $event->context[$this->getContextKey()],
        sprintf('No %s with ID %d found', $event->entity_type, $event->entity_id),
      );
    }
    else {
      $entity = $this->contextDataHolder->getEntityDataValue(
        $event->entity_type,
        $event->entity_id,
        $this->getContextKey(),
      );
      $entityClass = $this->getEntityClass();
      if ($entity instanceof $entityClass) {
        $event->context[$this->getContextKey()] = $entity;
      }
    }
  }

  public function checkActive(TokenProcessor $processor): bool {
    if ('CRM_Civioffice_Page_Tokens' === ($processor->context['controller'] ?? NULL)) {
      return TRUE;
    }

    $active = in_array($this->getContextKey(), $processor->context['schema'] ?? [], TRUE)
      || in_array($this->getContextKey() . 'Id', $processor->context['schema'] ?? [], TRUE);

    if (!$active) {
      /** @var \Civi\Token\TokenRow $row */
      foreach ($processor->getRows() as $row) {
        if (isset($row->context[$this->getContextKey()]) ||
          isset($row->context[$this->getContextKey() . 'Id'])) {
          $processor->context['schema'][] = $this->getContextKey();
          $active = TRUE;

          break;
        }
      }
    }

    if ($active) {
      $processor->context['schema'] = array_unique(
        array_merge($processor->context['schema'], $this->getRelatedContextSchemas())
      );
    }

    return $active;
  }

  /**
   * @inheritDoc
   */
  public function evaluateToken(TokenRow $row, $entityName, $field, $prefetch = NULL): void {
    /** @phpstan-var T $entity */
    $entity = $row->context[$this->getContextKey()];

    $resolvedToken = $this->tokenResolver->resolveToken($this->getApiEntityName(), $entity, $field);
    $row->format($resolvedToken->format);
    $row->tokens($entityName, $field, $resolvedToken->value);
  }

  public function getActiveTokens(TokenValueEvent $event) {
    $this->addRelatedContextValues($event);

    $messageTokens = $event->getTokenProcessor()->getMessageTokens()[$this->entity] ?? [];
    $activeTokens = array_values(array_intersect($messageTokens, array_keys($this->tokenNames)));

    // Add tokens with path for array value access.
    foreach ($messageTokens as $token) {
      /** @var string $fieldName */
      [$fieldName, $path] = explode('::', $token, 2) + [NULL, NULL];
      if (NULL !== $path && isset($this->tokenNames[$fieldName . '::'])) {
        $activeTokens[] = $token;
      }
    }

    return $activeTokens;
  }

  protected function getContextKey(): string {
    return lcfirst($this->getApiEntityName());
  }

  /**
   * @phpstan-return T
   */
  abstract protected function getEntity(int $id): ?AbstractEntity;

  /**
   * @phpstan-return class-string<T>
   */
  protected function getEntityClass(): string {
    // @phpstan-ignore-next-line
    return 'Civi\\Funding\\Entity\\' . $this->getApiEntityName() . 'Entity';
  }

  abstract protected function getApiEntityName(): string;

  /**
   * @phpstan-return list<string>
   */
  abstract protected function getRelatedContextSchemas(): array;

  /**
   * @phpstan-param T $entity
   *
   * @phpstan-return array<string, mixed>
   */
  abstract protected function getRelatedContextValues(AbstractEntity $entity): array;

  private function addRelatedContextValues(TokenValueEvent $event): void {
    /** @var \Civi\Token\TokenRow $row */
    foreach ($event->getRows() as $row) {
      $entity = $row->context[$this->getContextKey()] ?? NULL;
      if (NULL === $entity && isset($row->context[$this->getContextKey() . 'Id'])) {
        $entityId = $row->context[$this->getContextKey() . 'Id'];
        Assert::integer($entityId, sprintf('Context value "%s" must be an integer', $this->getContextKey() . 'Id'));
        $entity = $row->context[$this->getContextKey()] = $this->getEntity($entityId);
        Assert::notNull(
          $entity,
          sprintf('No %s with ID %d found', $this->getApiEntityName(), $entityId),
        );
      }

      Assert::isInstanceOf(
        $entity,
        $this->getEntityClass(),
        sprintf('Context value "%s" must be an instance of "%s"', $this->getContextKey(), $this->getEntityClass()),
      );

      // @phpstan-ignore argument.type
      foreach ($this->getRelatedContextValues($entity) as $key => $value) {
        $row->context[$key] ??= $value;
      }
    }
  }

}

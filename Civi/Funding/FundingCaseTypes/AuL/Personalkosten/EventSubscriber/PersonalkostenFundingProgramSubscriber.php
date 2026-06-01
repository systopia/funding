<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\EventSubscriber;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingProgram;
use Civi\Core\Event\PostEvent;
use Civi\Core\Event\PreEvent;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\PersonalkostenApplicationProcessUpdater;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\PersonalkostenMetaData;
use Civi\Funding\Permission\CiviPermissionChecker;
use Civi\Funding\Util\FloatUtil;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class PersonalkostenFundingProgramSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  private ApplicationProcessManager $applicationProcessManager;

  private PersonalkostenApplicationProcessUpdater $personalkostenApplicationProcessUpdater;

  private CiviPermissionChecker $permissionChecker;

  private FundingCaseManager $fundingCaseManager;

  /**
   * @var array<int, array{
   *   foerderquote: int,
   *   sachkostenpauschale: float
   * }>
   *   Mapping of funding program ID to Förderquote and Sachkostenpauschale.
   */
  private array $pending = [];

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_civicrm_pre::FundingProgram' => ['onPreFundingProgram', PHP_INT_MIN],
      'hook_civicrm_post::FundingProgram' => 'onPostFundingProgram',
    ];
  }

  public function __construct(
    Api4Interface $api4,
    ApplicationProcessManager $applicationProcessManager,
    PersonalkostenApplicationProcessUpdater $personalkostenApplicationProcessUpdater,
    CiviPermissionChecker $permissionChecker,
    FundingCaseManager $fundingCaseManager,
  ) {
    $this->api4 = $api4;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->personalkostenApplicationProcessUpdater = $personalkostenApplicationProcessUpdater;
    $this->permissionChecker = $permissionChecker;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  public function onPreFundingProgram(PreEvent $event): void {
    if (
      'edit' !== $event->action
      || (!isset($event->params['funding_program_extra.foerderquote'])
        && !isset($event->params['funding_program_extra.sachkostenpauschale']))
    ) {
      return;
    }

    $id = (int) $event->id;

    $fundingProgramGet = FundingProgram::get(FALSE)
      ->addWhere('id', '=', $id)
      ->setSelect([
        'funding_program_extra.foerderquote',
        'funding_program_extra.sachkostenpauschale',
      ])
      ->addJoin(
        'FundingCaseTypeProgram AS funding_case_type_program',
        'INNER',
        NULL,
        ['funding_case_type_program.funding_program_id', '=', 'id'],
      )
      ->addJoin(
        'FundingCaseType AS funding_case_type',
        'INNER',
        NULL,
        ['funding_case_type.id', '=', 'funding_case_type_program.funding_case_type_id'],
        ['funding_case_type.name', '=', '"' . PersonalkostenMetaData::NAME . '"'],
      );
    $fundingProgram = $this->api4->executeAction($fundingProgramGet)->first();
    if (NULL === $fundingProgram) {
      return;
    }

    $previousFoerderquote = $fundingProgram['funding_program_extra.foerderquote'];
    if (isset($event->params['funding_program_extra.foerderquote'])) {
      Assert::integerish($event->params['funding_program_extra.foerderquote']);
      $newFoerderquote = $event->params['funding_program_extra.foerderquote'] =
        (int) $event->params['funding_program_extra.foerderquote'];
      Assert::positiveInteger($newFoerderquote);
      Assert::lessThanEq($newFoerderquote, 100);
    }
    else {
      $newFoerderquote = $previousFoerderquote;
    }

    $previousSachkostenpauschale = (float) $fundingProgram['funding_program_extra.sachkostenpauschale'];
    if (isset($event->params['funding_program_extra.sachkostenpauschale'])) {
      Assert::numeric($event->params['funding_program_extra.sachkostenpauschale']);
      $newSachkostenpauschale = $event->params['funding_program_extra.sachkostenpauschale'] =
        round((float) $event->params['funding_program_extra.sachkostenpauschale'], 2);
      Assert::greaterThanEq($newSachkostenpauschale, 0.0);
    }
    else {
      $newSachkostenpauschale = $previousSachkostenpauschale;
    }

    if (
      $previousFoerderquote !== $newFoerderquote
      || !FloatUtil::isMoneyEqual($previousSachkostenpauschale, $newSachkostenpauschale)
    ) {
      if (!$this->permissionChecker->checkPermission(Permissions::ADMINISTER_FUNDING)) {
        throw new UnauthorizedException(sprintf(
          'Changing Förderquote or Sachkostenpauschale requires CiviCRM permission "%s"',
          Permissions::ADMINISTER_FUNDING
        ));
      }

      $this->pending[$id] = [
        'foerderquote' => $newFoerderquote,
        'sachkostenpauschale' => $newSachkostenpauschale,
      ];
    }
  }

  public function onPostFundingProgram(PostEvent $event): void {
    if (!isset($this->pending[$event->id])) {
      return;
    }

    $newFoerderquote = $this->pending[$event->id]['foerderquote'];
    $newSachkostenpauschale = $this->pending[$event->id]['sachkostenpauschale'];
    unset($this->pending[$event->id]);

    $fundingCaseBundles = $this->fundingCaseManager->getBundleBy(CompositeCondition::new('AND',
      Comparison::new('funding_program_id', '=', $event->id),
      Comparison::new('status', 'IN', [FundingCaseStatus::OPEN, FundingCaseStatus::ONGOING]),
      Comparison::new('funding_case_type_id.name', '=', PersonalkostenMetaData::NAME)
    ));

    foreach ($fundingCaseBundles as $fundingCaseBundle) {
      $fundingCase = $fundingCaseBundle->getFundingCase();
      $applicationProcessBundles = $this->applicationProcessManager->getBundlesByFundingCaseId($fundingCase->getId());
      foreach ($applicationProcessBundles as $applicationProcessBundle) {
        $this->personalkostenApplicationProcessUpdater->updateApplicationProcess(
          $applicationProcessBundle,
          $newFoerderquote,
          $newSachkostenpauschale
        );
      }
    }
  }

}

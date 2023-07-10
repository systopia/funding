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

namespace Civi\Funding\EventSubscriber\ExternalFile;

use Civi\ExternalFile\Entity\AttachmentEntity;
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Civi\ExternalFile\Event\AuthorizeFileDownloadEvent;
use Civi\ExternalFile\ExternalFileStatus;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Civi\Funding\EventSubscriber\ExternalFile\AuthorizeExternalFileDownloadSubscriber
 */
final class AuthorizeExternalFileDownloadSubscriberTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private AuthorizeExternalFileDownloadSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->subscriber = new AuthorizeExternalFileDownloadSubscriber($this->api4Mock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      AuthorizeFileDownloadEvent::class => 'onAuthorize',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testAuthorized(): void {
    $event = $this->createEvent();
    $this->api4Mock->method('countEntities')
      ->with('TestEntity', Comparison::new('id', '=', 4), ['checkPermissions' => FALSE])
      ->willReturn(1);

    $this->subscriber->onAuthorize($event);
    static::assertTrue($event->isAuthorized());
  }

  public function testNotAuthorized(): void {
    $event = $this->createEvent();
    $this->api4Mock->method('countEntities')
      ->with('TestEntity', Comparison::new('id', '=', 4), ['checkPermissions' => FALSE])
      ->willReturn(0);

    $this->subscriber->onAuthorize($event);
    static::assertFalse($event->isAuthorized());
  }

  public function testUnhandledExtension(): void {
    $event = $this->createEvent('test');
    $this->api4Mock->expects(static::never())->method('countEntities');

    $this->subscriber->onAuthorize($event);
    static::assertFalse($event->isAuthorized());
  }

  private function createEvent(string $extension = 'funding'): AuthorizeFileDownloadEvent {
    $attachment = AttachmentEntity::fromArray([
      'id' => 2,
      'mime_type' => 'application/octet-stream',
      'description' => NULL,
      'upload_date' => '2023-01-02 03:04:05',
      'entity_table' => 'civicrm_external_file',
      'entity_id' => 3,
      'path' => '/path/to/file',
    ]);
    $externalFile = ExternalFileEntity::fromArray([
      'id' => 3,
      'file_id' => 2,
      'source' => 'https://example.org/test.txt',
      'filename' => 'test.txt',
      'status' => ExternalFileStatus::AVAILABLE,
      'download_start_date' => NULL,
      'download_try_count' => 0,
      'extension' => $extension,
      'identifier' => 'identifier',
      'custom_data' => [
        'entityName' => 'TestEntity',
        'entityId' => 4,
      ],
      'last_modified' => NULL,
    ]);
    $request = new Request();

    return new AuthorizeFileDownloadEvent($attachment, $externalFile, $request);
  }

}

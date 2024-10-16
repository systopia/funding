<?php
declare(strict_types = 1);

namespace Civi\Funding\Notification;

use Civi\Api4\Contact;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Notification\NotificationSendTemplateParamsFactory
 */
final class NotificationSendTemplateParamsFactoryTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private $api4Mock;

  /**
   * @var \Civi\Funding\Notification\NotificationSendTemplateParamsFactory
   */
  private NotificationSendTemplateParamsFactory $paramsFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->paramsFactory = new NotificationSendTemplateParamsFactory($this->api4Mock);
  }

  public function testCreateSendTemplateParams(): void {
    $tokenContext = ['foo' => 'bar'];

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(Contact::getEntityName(), 'get', [
        'select' => [
          'display_name',
          'email.email',
        ],
        'where' => [
          ['id', '=', 123],
          ['do_not_email', '=', FALSE],
        ],
        'join' => [
          ['Email AS email', 'INNER', ['email.contact_id', '=', 'id'], ['email.is_primary', '=', TRUE]],
        ],
      ])->willReturn(new Result([['display_name' => 'Display Name', 'email.email' => 'to@example.org']]));

    $params = $this->paramsFactory->createSendTemplateParams(
      123,
      'workflow',
      'From',
      'from@example.org',
      $tokenContext
    );

    $expectedParams = [
      'workflow' => 'workflow',
      'from' => 'From <from@example.org>',
      'toName' => 'Display Name',
      'toEmail' => 'to@example.org',
      'tokenContext' => [
        'foo' => 'bar',
        'contactId' => 123,
      ],
    ];

    static::assertEquals($expectedParams, $params);
  }

  public function testCreateSendTemplateParamsNoContact(): void {
    $tokenContext = ['foo' => 'bar'];

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(Contact::getEntityName(), 'get', [
        'select' => [
          'display_name',
          'email.email',
        ],
        'where' => [
          ['id', '=', 123],
          ['do_not_email', '=', FALSE],
        ],
        'join' => [
          ['Email AS email', 'INNER', ['email.contact_id', '=', 'id'], ['email.is_primary', '=', TRUE]],
        ],
      ])->willReturn(new Result([]));

    static::assertNull($this->paramsFactory->createSendTemplateParams(
      123,
      'workflow',
      'From',
      'from@example.org',
      $tokenContext
    ));
  }

}

<?php

declare(strict_types = 1);

namespace Civi\Funding\DocumentRender;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeRendererOptions;

/**
 * @covers \Civi\Funding\DocumentRender\SettingProvider
 * @group headless
 */
class SettingProviderTest extends AbstractFundingHeadlessTestCase {

  public function testGetSettingsValue(): void {
    $mockOptions = $this->createMock(CiviOfficeRendererOptions::class);
    $mockOptions->expects(static::never())
      ->method('fetchOptions');

    $provider = $this->getMockBuilder(SettingProvider::class)
      ->setConstructorArgs([$mockOptions])
      ->onlyMethods(['getSettingsValue'])
      ->getMock();

    $provider->expects(static::once())
      ->method('getSettingsValue')
      ->willReturn('custom-uri');

    static::assertSame('custom-uri', $provider->getCiviOfficeRendererUri());
  }

  public function testGetFirstOption(): void {
    $mockOptions = $this->createMock(CiviOfficeRendererOptions::class);
    $mockOptions->expects(static::once())
      ->method('fetchOptions')
      ->willReturn(['uri1' => 'Name 1', 'uri2' => 'Name 2']);

    $provider = $this->getMockBuilder(SettingProvider::class)
      ->setConstructorArgs([$mockOptions])
      ->onlyMethods(['getSettingsValue'])
      ->getMock();

    $provider->expects(static::once())
      ->method('getSettingsValue')
      ->willReturn(NULL);

    static::assertSame('Name 1', $provider->getCiviOfficeRendererUri());
  }

}

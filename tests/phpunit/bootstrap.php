<?php
declare(strict_types = 1);

use Civi\Funding\Contact\DummyRemoteContactIdResolver;
use Civi\Funding\Contact\FundingRemoteContactIdResolverInterface;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\DocumentRendererInterface;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Mock\Contact\PossibleRecipientsLoaderMock;
use Civi\Funding\Mock\DocumentRender\MockDocumentRenderer;
use Civi\Funding\Mock\FundingCaseType\Application\Actions\TestApplicationActionsDeterminer;
use Civi\Funding\Mock\FundingCaseType\Application\Actions\TestApplicationActionStatusInfo;
use Civi\Funding\Mock\FundingCaseType\Application\Actions\TestApplicationStatusDeterminer;
use Civi\Funding\Mock\FundingCaseType\Application\Actions\TestApplicationSubmitActionsContainer;
use Civi\Funding\Mock\FundingCaseType\Application\Data\TestApplicationFormFilesFactory;
use Civi\Funding\Mock\FundingCaseType\Application\Data\TestFormDataFactory;
use Civi\Funding\Mock\FundingCaseType\Application\JsonSchema\TestJsonSchemaFactory;
use Civi\Funding\Mock\FundingCaseType\Application\UiSchema\TestUiSchemaFactory;
use Civi\Funding\Mock\FundingCaseType\Clearing\TestReportFormFactory;
use Civi\Funding\Mock\FundingCaseType\FundingCase\Actions\TestCaseActionsDeterminer;
use Civi\Funding\Mock\FundingCaseType\FundingCase\Data\TestFundingCaseFormDataFactory;
use Civi\Funding\Mock\FundingCaseType\FundingCase\JsonSchema\TestFundingCaseJsonSchemaFactory;
use Civi\Funding\Mock\FundingCaseType\FundingCase\UiSchema\TestFundingCaseUiSchemaFactory;
use Civi\Funding\Mock\FundingCaseType\FundingCase\Validation\TestFundingCaseValidator;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryLocator;
use Civi\Funding\TestAttachmentManager;
use Civi\PHPUnit\Comparator\ApiActionComparator;
use Civi\RemoteTools\Contact\RemoteContactIdResolverInterface;
use Composer\Autoload\ClassLoader;
use SebastianBergmann\Comparator\Factory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

ini_set('memory_limit', '2G');

// phpcs:disable Drupal.Functions.DiscouragedFunctions.Discouraged
eval(cv('php:boot --level=classloader', 'phpcode'));
// phpcs.enable

// Make CRM_Funding_ExtensionUtil available.
require_once __DIR__ . '/../../funding.civix.php';

// phpcs:disable PSR1.Files.SideEffects

// Add test classes to class loader.
addExtensionDirToClassLoader(__DIR__);
addExtensionToClassLoader('funding');

// For tests without Civi environment.
addExtensionToClassLoader('external-file');
addExtensionToClassLoader('de.systopia.remotetools');

if (!function_exists('ts')) {
  // Ensure function ts() is available - it's declared in the same file as CRM_Core_I18n in CiviCRM < 5.74.
  // In later versions the function is registered following the composer conventions.
  \CRM_Core_I18n::singleton();
}

$comparatorFactory = Factory::getInstance();
$comparatorFactory->register(new ApiActionComparator());

/**
 * Modify DI container for tests.
 */
function _funding_test_civicrm_container(ContainerBuilder $container): void {
  $container->autowire(TestAttachmentManager::class)
    ->setDecoratedService(FundingAttachmentManagerInterface::class);

  // To clear cache
  $container->getDefinition(FundingCaseManager::class)->setPublic(TRUE);
  $container->getDefinition(FundingCaseTypeManager::class)->setPublic(TRUE);
  $container->getDefinition(FundingProgramManager::class)->setPublic(TRUE);

  // For FundingCaseContactRelationPropertiesFactoryTypeTest
  $container->getDefinition(RelationPropertiesFactoryLocator::class)->setPublic(TRUE);

  // For FundingCaseTest
  $container->autowire(DocumentRendererInterface::class, MockDocumentRenderer::class);

  // For CiviOfficeRendererTest
  $container->getDefinition(CiviOfficeContextDataHolder::class)->setPublic(TRUE);

  // overwrite remote contact ID resolver
  $container->autowire(FundingRemoteContactIdResolverInterface::class, DummyRemoteContactIdResolver::class);
  $container->setAlias(RemoteContactIdResolverInterface::class, FundingRemoteContactIdResolverInterface::class);

  $container->autowire(TestApplicationStatusDeterminer::class)
    ->addTag(TestApplicationStatusDeterminer::SERVICE_TAG);
  $container->autowire(TestApplicationActionStatusInfo::class)
    ->addTag(TestApplicationActionStatusInfo::SERVICE_TAG);

  $container->autowire(TestApplicationActionsDeterminer::class)
    ->addTag(TestApplicationActionsDeterminer::SERVICE_TAG);
  $container->autowire(TestApplicationSubmitActionsContainer::class)
    ->addTag(TestApplicationSubmitActionsContainer::SERVICE_TAG);

  $container->autowire(TestCaseActionsDeterminer::class)
    ->addTag(TestCaseActionsDeterminer::SERVICE_TAG);

  $container->autowire(TestJsonSchemaFactory::class)
    ->addTag(TestJsonSchemaFactory::SERVICE_TAG);
  $container->autowire(TestUiSchemaFactory::class)
    ->addTag(TestUiSchemaFactory::SERVICE_TAG);
  $container->autowire(TestFormDataFactory::class)
    ->addTag(TestFormDataFactory::SERVICE_TAG);
  $container->autowire(TestApplicationFormFilesFactory::class)
    ->addTag(TestApplicationFormFilesFactory::SERVICE_TAG);

  $container->autowire(TestFundingCaseFormDataFactory::class)
    ->addTag(TestFundingCaseFormDataFactory::SERVICE_TAG);
  $container->autowire(TestFundingCaseJsonSchemaFactory::class)
    ->addTag(TestFundingCaseJsonSchemaFactory::SERVICE_TAG);
  $container->autowire(TestFundingCaseUiSchemaFactory::class)
    ->addTag(TestFundingCaseUiSchemaFactory::SERVICE_TAG);
  $container->autowire(TestFundingCaseValidator::class)
    ->setArgument('$jsonSchemaFactory', new Reference(TestFundingCaseJsonSchemaFactory::class))
    ->addTag(TestFundingCaseValidator::SERVICE_TAG);

  $container->autowire(TestReportFormFactory::class)
    ->addTag(TestReportFormFactory::SERVICE_TAG);

  $container->autowire(PossibleRecipientsLoaderMock::class)
    ->addTag(PossibleRecipientsLoaderMock::SERVICE_TAG);
}

function addExtensionToClassLoader(string $extension): void {
  addExtensionDirToClassLoader(__DIR__ . '/../../../' . $extension);
}

function addExtensionDirToClassLoader(string $extensionDir): void {
  $loader = new ClassLoader();
  $loader->add('CRM_', [$extensionDir]);
  $loader->addPsr4('Civi\\', [$extensionDir . '/Civi']);
  $loader->add('api_', [$extensionDir]);
  $loader->addPsr4('api\\', [$extensionDir . '/api']);
  $loader->register();

  if (file_exists($extensionDir . '/autoload.php')) {
    require_once $extensionDir . '/autoload.php';
  }
}

/**
 * Call the "cv" command.
 *
 * @param string $cmd
 *   The rest of the command to send.
 * @param string $decode
 *   Ex: 'json' or 'phpcode'.
 * @return mixed
 *   Response output (if the command executed normally).
 *   For 'raw' or 'phpcode', this will be a string. For 'json', it could be any JSON value.
 * @throws \RuntimeException
 *   If the command terminates abnormally.
 */
function cv(string $cmd, string $decode = 'json') {
  $cmd = 'cv ' . $cmd;
  $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => STDERR];
  $oldOutput = getenv('CV_OUTPUT');
  putenv('CV_OUTPUT=json');

  // Execute `cv` in the original folder. This is a work-around for
  // phpunit/codeception, which seem to manipulate PWD.
  $cmd = sprintf('cd %s; %s', escapeshellarg(getenv('PWD')), $cmd);

  $process = proc_open($cmd, $descriptorSpec, $pipes, __DIR__);
  putenv("CV_OUTPUT=$oldOutput");
  fclose($pipes[0]);
  $result = stream_get_contents($pipes[1]);
  fclose($pipes[1]);
  if (proc_close($process) !== 0) {
    throw new RuntimeException("Command failed ($cmd):\n$result");
  }
  switch ($decode) {
    case 'raw':
      return $result;

    case 'phpcode':
      // If the last output is /*PHPCODE*/, then we managed to complete execution.
      if (substr(trim($result), 0, 12) !== '/*BEGINPHP*/' || substr(trim($result), -10) !== '/*ENDPHP*/') {
        throw new RuntimeException("Command failed ($cmd):\n$result");
      }
      return $result;

    case 'json':
      return json_decode($result, TRUE);

    default:
      throw new RuntimeException("Bad decoder format ($decode)");
  }
}

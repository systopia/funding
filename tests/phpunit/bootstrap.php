<?php
declare(strict_types = 1);

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ReworkPossibleApplicationProcessActionsDeterminer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ReworkPossibleApplicationProcessActionStatusInfo;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ReworkPossibleApplicationProcessStatusDeterminer;
use Civi\Funding\Contact\DummyRemoteContactIdResolver;
use Civi\Funding\Contact\FundingRemoteContactIdResolverInterface;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\DocumentRendererInterface;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\FundingCase\DefaultFundingCaseActionsDeterminer;
use Civi\Funding\Mock\DocumentRender\MockDocumentRenderer;
use Civi\Funding\Mock\Form\FundingCaseType\TestApplicationCostItemsFactory;
use Civi\Funding\Mock\Form\FundingCaseType\TestApplicationFormFilesFactory;
use Civi\Funding\Mock\Form\FundingCaseType\TestApplicationResourcesItemsFactory;
use Civi\Funding\Mock\Form\FundingCaseType\TestFormDataFactory;
use Civi\Funding\Mock\Form\FundingCaseType\TestJsonSchemaFactory;
use Civi\Funding\Mock\Form\FundingCaseType\TestUiSchemaFactory;
use Civi\Funding\Mock\Form\FundingCaseType\TestValidator;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryLocator;
use Civi\Funding\TestAttachmentManager;
use Civi\PHPUnit\Comparator\ApiActionComparator;
use Composer\Autoload\ClassLoader;
use SebastianBergmann\Comparator\Factory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

ini_set('memory_limit', '2G');

require_once __DIR__ . '/../../vendor/autoload.php';

// Make CRM_Funding_ExtensionUtil available.
require_once __DIR__ . '/../../funding.civix.php';

// phpcs:disable
eval(cv('php:boot --level=classloader', 'phpcode'));
// phpcs:enable

// phpcs:disable PSR1.Files.SideEffects

// Allow autoloading of PHPUnit helper classes in this extension.
$loader = new ClassLoader();
$loader->add('CRM_', [__DIR__ . '/../..', __DIR__]);
$loader->addPsr4('Civi\\', [__DIR__ . '/../../Civi', __DIR__ . '/Civi']);
$loader->add('api_', [__DIR__ . '/../..', __DIR__]);
$loader->addPsr4('api\\', [__DIR__ . '/../../api', __DIR__ . '/api']);
$loader->register();

// Ensure function ts() is available - it's declared in the same file as CRM_Core_I18n
\CRM_Core_I18n::singleton();

// For tests without Civi environment.
addExtensionToClassLoader('external-file');
addExtensionToClassLoader('de.systopia.remotetools');

$comparatorFactory = Factory::getInstance();
$comparatorFactory->register(new ApiActionComparator());

function _funding_test_civicrm_container(ContainerBuilder $container): void {
  $container->autowire(TestAttachmentManager::class)
    ->setDecoratedService(FundingAttachmentManagerInterface::class);

  // For FundingCaseContactRelationPropertiesFactoryTypeTest
  $container->getDefinition(RelationPropertiesFactoryLocator::class)->setPublic(TRUE);

  // For FundingCaseTest
  $container->autowire(DocumentRendererInterface::class, MockDocumentRenderer::class);

  // For CiviOfficeRendererTest
  $container->getDefinition(CiviOfficeContextDataHolder::class)->setPublic(TRUE);

  // overwrite remote contact ID resolver
  $container->autowire(FundingRemoteContactIdResolverInterface::class, DummyRemoteContactIdResolver::class);

  $container->getDefinition(ReworkPossibleApplicationProcessActionsDeterminer::class)
    ->addTag('funding.application.actions_determiner',
      ['funding_case_type' => TestJsonSchemaFactory::getSupportedFundingCaseTypes()[0]]);
  $container->getDefinition(ReworkPossibleApplicationProcessStatusDeterminer::class)
    ->addTag('funding.application.status_determiner',
      ['funding_case_type' => TestJsonSchemaFactory::getSupportedFundingCaseTypes()[0]]);
  $container->getDefinition(ReworkPossibleApplicationProcessActionStatusInfo::class)
    ->addTag('funding.application.action_status_info',
      ['funding_case_type' => TestJsonSchemaFactory::getSupportedFundingCaseTypes()[0]]);

  $container->getDefinition(DefaultFundingCaseActionsDeterminer::class)
    ->addTag(DefaultFundingCaseActionsDeterminer::TAG,
      ['funding_case_type' => TestJsonSchemaFactory::getSupportedFundingCaseTypes()[0]]);

  $container->autowire(TestJsonSchemaFactory::class)
    ->addTag('funding.application.json_schema_factory');
  $container->autowire(TestUiSchemaFactory::class)
    ->addTag('funding.application.ui_schema_factory');
  $container->autowire(TestValidator::class)
    ->setArgument('$jsonSchemaFactory', new Reference(TestJsonSchemaFactory::class))
    ->addTag(TestValidator::SERVICE_TAG);
  $container->autowire(TestFormDataFactory::class)
    ->addTag('funding.application.form_data_factory');
  $container->autowire(TestApplicationCostItemsFactory::class)
    ->addTag('funding.application.cost_items_factory');
  $container->autowire(TestApplicationResourcesItemsFactory::class)
    ->addTag('funding.application.resources_items_factory');
  $container->autowire(TestApplicationFormFilesFactory::class)
    ->addTag(TestApplicationFormFilesFactory::SERVICE_TAG);
}

function addExtensionToClassLoader(string $extension) {
  $extensionDir = __DIR__ . '/../../../' . $extension;
  $loader = new ClassLoader();
  $loader->add('CRM_', [$extensionDir]);
  $loader->addPsr4('Civi\\', [$extensionDir . '/Civi']);
  $loader->add('api_', [$extensionDir]);
  $loader->addPsr4('api\\', [$extensionDir . '/api']);
  $loader->register();
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

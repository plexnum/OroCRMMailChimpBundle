<?php
namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Async;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor;
use Oro\Bundle\MailChimpBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\Testing\ClassExtensionTrait;
use Psr\Log\LoggerInterface;

/**
 * @dbIsolationPerTest
 */
class ExportMailChimpProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        self::assertClassImplements(MessageProcessorInterface::class, ExportMailChimpProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        self::assertClassImplements(TopicSubscriberInterface::class, ExportMailChimpProcessor::class);
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new ExportMailChimpProcessor(
            $this->createDoctrineHelperStub(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $this->createLoggerMock()
        );
    }

    public function testShouldSubscribeOnExportMailChimpSegmentsTopic()
    {
        $this->assertEquals([Topics::EXPORT_MAILCHIMP_SEGMENTS], ExportMailChimpProcessor::getSubscribedTopics());
    }

    public function testShouldLogAndRejectIfMessageBodyMissIntegrationId()
    {
        $message = new NullMessage();
        $message->setBody('[]');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have integrationId set', ['message' => $message])
        ;

        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperStub(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldLogAndRejectIfMessageBodyMissSegmentsIds()
    {
        $message = new NullMessage();
        $message->setBody('{"integrationId":1}');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have segmentsIds set', ['message' => $message])
        ;

        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperStub(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }
    
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The malformed json given.
     */
    public function testThrowIfMessageBodyInvalidJson()
    {
        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperStub(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $this->createLoggerMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $processor->process($message, new NullSession());
    }

    public function testShouldLogAndRejectIfIntegrationNotFound()
    {
        $message = new NullMessage();
        $message->setBody('{"integrationId":"theIntegrationId", "segmentsIds": 1}');
        $message->setMessageId('theMessageId');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('The integration not found: theIntegrationId', ['message' => $message])
        ;

        $processor = new ExportMailChimpProcessor(
            $this->createDoctrineHelperStub(),
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldLogAndRejectIfIntegrationNotEnabled()
    {
        $integration = new Integration();
        $integration->setEnabled(false);

        $doctrineHelper = $this->createDoctrineHelperStub($integration);

        $message = new NullMessage();
        $message->setBody('{"integrationId":"theIntegrationId", "segmentsIds": 1}');
        $message->setMessageId('theMessageId');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('The integration is not enabled: theIntegrationId', ['message' => $message])
        ;

        $processor = new ExportMailChimpProcessor(
            $doctrineHelper,
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $this->createJobRunnerMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRunUniqueJobAndReturnAckIfClosureReturnTrue()
    {
        $jobRunner = $this->createJobRunnerMock();
        $jobRunner->expects($this->once())
            ->method('runUnique')
            ->willReturn(true)
        ;

        $integration = new Integration();
        $integration->setEnabled(true);

        $doctrineHelper = $this->createDoctrineHelperStub($integration);

        $processor = new ExportMailChimpProcessor(
            $doctrineHelper,
            $this->createReverseSyncProcessorMock(),
            $this->createStaticSegmentsMemberStateManagerMock(),
            $jobRunner,
            $this->createLoggerMock()
        );

        $message = new NullMessage();
        $message->setBody('{"integrationId":"theIntegrationId", "segmentsIds": 1}');
        $message->setMessageId('theMessageId');

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    private function createDoctrineHelperStub($integration = null)
    {
        $entityManagerMock = $this->createEntityManagerStub();
        $entityManagerMock
            ->expects($this->any())
            ->method('find')
            ->with(Integration::class)
            ->willReturn($integration);
        ;


        $helperMock = $this->getMock(DoctrineHelper::class, [], [], '', false);
        $helperMock
            ->expects($this->any())
            ->method('getEntityManagerForClass')
            ->with(Integration::class)
            ->willReturn($entityManagerMock)
        ;

        return $helperMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReverseSyncProcessor
     */
    private function createReverseSyncProcessorMock()
    {
        return $this->getMock(ReverseSyncProcessor::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StaticSegmentsMemberStateManager
     */
    private function createStaticSegmentsMemberStateManagerMock()
    {
        return $this->getMock(StaticSegmentsMemberStateManager::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JobRunner
     */
    private function createJobRunnerMock()
    {
        return $this->getMock(JobRunner::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private function createEntityManagerStub()
    {
        $configuration = new Configuration();

        $connectionMock = $this->getMock(Connection::class, [], [], '', false);
        $connectionMock
            ->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($configuration)
        ;

        $entityManagerMock = $this->getMock(EntityManagerInterface::class);
        $entityManagerMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock)
        ;

        return $entityManagerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->getMock(LoggerInterface::class);
    }
}
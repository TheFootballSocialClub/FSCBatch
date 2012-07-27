<?php

namespace FSC\Batch\Tests;

use FSC\Batch\JobProcessor;

class JobProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithWrongExecutor()
    {
        $this->createJobProcessor($this->createJobProviderInterfaceMock(), null);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWithJobProvider()
    {
        $jobProviderMock = $this->createJobProviderInterfaceMock();
        $jobProviderMock->expects($this->any())
            ->method('getJobsCount')
            ->will($this->returnValue(null));

        $jobProcessor = $this->createJobProcessor($jobProviderMock, function () {});

        $jobProcessor->run();
    }

    /**
     * @dataProvider getTestRunData
     */
    public function testRun($loops, $batchSize, $expectedGetJobsContextsCallsCount, $expectedExecutorCallsCount)
    {
        $executorCallsCount = 0;

        $jobProviderMock = $this->getMock('FSC\Batch\JobProvider\JobProviderInterface', array('getJobsCount', 'getJobsContexts'));
        $jobProviderMock->expects($this->exactly(1))
            ->method('getJobsCount')
            ->will($this->returnValue($loops));
        $jobProviderMock->expects($this->exactly($expectedGetJobsContextsCallsCount))
            ->method('getJobsContexts')
            ->will($this->returnCallback(function($offset, $limit) {
                return array_fill(0, $limit, array());
            }));

        $jobProcessor = $this->createJobProcessor($jobProviderMock, function () use (&$executorCallsCount) {
            $executorCallsCount++;
        });

        $jobProcessor->run($batchSize);

        $this->assertEquals($expectedExecutorCallsCount, $executorCallsCount);
    }

    public function getTestRunData()
    {
        return array(
            array(200, 50, 4, 200),
            array(1, 50, 1, 1),
            array(50, 50, 1, 50),
            array(51, 50, 2, 51),
            array(51, 25, 3, 51),
            array(50, 25, 2, 50),
        );
    }

    protected function createJobProviderInterfaceMock()
    {
        return $this->getMock('FSC\Batch\JobProvider\JobProviderInterface');
    }

    protected function createJobProcessor($jobProvider, $jobExecutor)
    {
        return new JobProcessor($jobProvider, $jobExecutor);
    }
}

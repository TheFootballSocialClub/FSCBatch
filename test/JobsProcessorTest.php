<?php

namespace FSC\Batch\Tests;

use FSC\Batch\JobsProcessor;

class JobsProcessorTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @expectedException RuntimeException
     */
    public function testWithJobProvider()
    {
        $jobProviderMock = $this->createJobProviderInterfaceMock();
        $jobProviderMock->expects($this->any())
            ->method('getJobsCount')
            ->will($this->returnValue(null));

        $jobProcessor = $this->createJobsProcessor($jobProviderMock, $this->createJobExecutorInterfaceMock());

        $jobProcessor->run();
    }

    /**
     * @dataProvider getTestRunData
     */
    public function testRun($loops, $batchSize, $expectedGetJobsContextsCallsCount, $expectedExecuteCallsCount)
    {
        $jobProviderMock = $this->getMock('FSC\Batch\JobProviderInterface', array('getJobsCount', 'getJobsContexts'));
        $jobProviderMock->expects($this->exactly(1))
            ->method('getJobsCount')
            ->will($this->returnValue($loops));
        $jobProviderMock->expects($this->exactly($expectedGetJobsContextsCallsCount))
            ->method('getJobsContexts')
            ->will($this->returnCallback(function($offset, $limit) {
                return array_fill(0, $limit, array());
            }));

        $jobExecutorMock = $this->getMock('FSC\Batch\JobExecutorInterface', array('execute'));
        $jobExecutorMock->expects($this->exactly($expectedExecuteCallsCount))
            ->method('execute')
            ->will($this->returnValue(null));

        $jobProcessor = $this->createJobsProcessor($jobProviderMock, $jobExecutorMock);

        $jobProcessor->run();
    }

    public function getTestRunData()
    {
        return array(
            array(200, 50, 4, 200),
            array(1, 50, 1, 1),
            array(50, 50, 1, 50),
            array(51, 50, 2, 51),
        );
    }

    protected function createJobProviderInterfaceMock()
    {
        return $this->getMock('FSC\Batch\JobProviderInterface');
    }

    protected function createJobExecutorInterfaceMock()
    {
        return $this->getMock('FSC\Batch\JobExecutorInterface');
    }

    protected function createJobsProcessor($jobProvider, $jobExecutor)
    {
        return new JobsProcessor($jobProvider, $jobExecutor);
    }
}

<?php

namespace FSC\Batch\Tests;

use FSC\Batch\Batch;

class BatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithWrongExecutor()
    {
        $this->createBatch($this->createAdapterInterfaceMock(), null);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWithJobProvider()
    {
        $adapterMock = $this->createAdapterInterfaceMock();
        $adapterMock->expects($this->any())
            ->method('getJobsCount')
            ->will($this->returnValue(null));

        $batch = $this->createBatch($adapterMock, function () {});

        $batch->run();
    }

    /**
     * @dataProvider getTestRunData
     */
    public function testRun($loops, $batchSize, $expectedGetJobsContextsCallsCount, $expectedExecutorCallsCount)
    {
        $executorCallsCount = 0;

        $adapterMock = $this->getMock('Pagerfanta\Adapter\AdapterInterface', array('getNbResults', 'getSlice'));
        $adapterMock->expects($this->exactly(1))
            ->method('getNbResults')
            ->will($this->returnValue($loops));
        $adapterMock->expects($this->exactly($expectedGetJobsContextsCallsCount))
            ->method('getSlice')
            ->will($this->returnCallback(function($offset, $limit) {
                return array_fill(0, $limit, array());
            }));

        $batch = $this->createBatch($adapterMock, function () use (&$executorCallsCount) {
            $executorCallsCount++;
        });

        $batch->run($batchSize);

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

    protected function createAdapterInterfaceMock()
    {
        return $this->getMock('Pagerfanta\Adapter\AdapterInterface');
    }

    protected function createBatch($adapter, $jobExecutor)
    {
        return new Batch($adapter, $jobExecutor);
    }
}

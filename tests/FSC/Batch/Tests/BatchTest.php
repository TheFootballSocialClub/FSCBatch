<?php

namespace FSC\Batch\Tests;

use FSC\Batch\Batch;

class BatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestRunData
     */
    public function testRun($loops, $batchSize, $expectedGetSliceCallsCount, $expectedCallbackCallsCount)
    {
        $callbackCallsCount = 0;

        $adapterMock = $this->getMock('Pagerfanta\Adapter\AdapterInterface', array('getNbResults', 'getSlice'));
        $adapterMock->expects($this->exactly(1))
            ->method('getNbResults')
            ->will($this->returnValue($loops));
        $adapterMock->expects($this->exactly($expectedGetSliceCallsCount))
            ->method('getSlice')
            ->will($this->returnCallback(function($offset, $limit) {
                return array_fill(0, $limit, array());
            }));

        $batch = $this->createBatch($adapterMock, function () use (&$callbackCallsCount) {
            $callbackCallsCount++;
        });

        $batch->run($batchSize);

        $this->assertEquals($expectedCallbackCallsCount, $callbackCallsCount);
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

    protected function createBatch($adapter, $callback)
    {
        return new Batch($adapter, $callback);
    }
}

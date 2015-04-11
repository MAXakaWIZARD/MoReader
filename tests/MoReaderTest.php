<?php
namespace MoReader\Tests;

use MoReader\Reader;

/**
 *
 */
class MoReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     *
     */
    public function setUp()
    {
        $this->reader = new Reader();
    }

    /**
     *
     */
    public function testGeneral()
    {
        $entries = $this->reader->load(BASE_PATH . '/tests/data/general.mo');
        //print_r($entries);
        //exit;

        $correctData = array(
            array('', ''),
            array('cat', 'gato'),
            array('dog', 'perro')
        );

        $this->assertEquals(count($correctData), count($entries));

        $idx = 0;
        foreach ($entries as $entryKey => $entryValue) {
            if ($correctData[$idx][0] !== '') {
                $this->assertEquals($correctData[$idx][0], $entryKey);
                $this->assertEquals($correctData[$idx][1], $entryValue);
            }
            $idx++;
        }
    }
}

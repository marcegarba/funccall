<?php

namespace Marcegarba\FuncColl;

use stdClass;
use LogicException;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-08-02 at 22:22:41.
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Collection
     */
    protected $object;

    private $source = [1, 2, 3, 4, 5, 6];

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = Collection::fromArray($this->source);
    }

    public function testFromArray()
    {
        $this->assertCount(count($this->source), $this->object);
        $this->assertEquals(4, $this->object[3]);
    }

    public function testGenerate()
    {
        $obj = new stdClass();
        $obj->arr = ['a', 'b', 'c', 'd'];
        $func = function () use ($obj) {
            $eachArr = each($obj->arr);
            if ($eachArr) {
                $result = $eachArr[1];
            } else {
                $result = false;
            }

            return $result;
        };
        reset($obj->arr);
        $col1 = Collection::generate($func);
        $this->assertCount(4, $col1);
        $this->assertEquals('b', $col1[1]);
        reset($obj->arr);
        $col2 = Collection::generate($func, 3);
        $this->assertCount(3, $col2);
        $this->assertEquals('c', $col2[2]);
    }

    public function testToArray()
    {
        $this->assertTrue(is_array($this->object->toArray()));
        $this->assertEquals($this->source, $this->object->toArray());
    }

    public function testFilter()
    {
        $col = $this->object->filter(function ($elem) { return $elem % 2 == 0;});
        $this->assertNotSame($this->object, $col);
        $this->assertTrue(count($col) == 3);
        $this->assertEquals(2, $col[1]);
        $this->assertEquals(4, $col[3]);
        $this->assertNull($col[0]);
    }

    public function testMap()
    {
        $col = $this->object->map(function ($elem) { return $elem * 3; });
        $this->assertNotNull($col);
        $this->assertNotSame($this->object, $col);
        $this->assertTrue($col instanceof Collection);
        $this->assertCount(6, $col);
        for ($i = 0; $i < count($this->source); $i++) {
            $this->assertEquals($this->source[$i] * 3, $col[$i]);
        }
    }

    public function testReduce()
    {
        $this->assertEquals(21, $this->object->reduce(
                function ($a, $b) {
                    return $a + $b;
                }));
        $this->assertEquals(720, $this->object->reduce(
                function ($a, $b) {
                    return $a * $b;
                }, 1));
    }

    public function testCount()
    {
        $arr = $this->object->toArray();
        $this->assertEquals(count($arr), count($this->object));
    }

    public function testTakeWithPositiveArgument()
    {
        $col = $this->object->take(3);
        $this->assertNotSame($this->object, $col);
        $this->assertCount(3, $col);
        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals($this->source[$i], $col[$i]);
        }
    }

    public function testTakeWithZeroArgument()
    {
        $col = $this->object->take(0);
        $this->assertNotSame($this->object, $col);
        $this->assertCount(0, $col);
    }

    public function testTakeWithNegativeArgument()
    {
        $col = $this->object->take(-2);
        $this->assertNotSame($this->object, $col);
        $newCount= count($this->source) - 2;
        $this->assertCount($newCount, $col);
        for ($i = 0; $i < $newCount; $i++) {
            $this->assertEquals($this->source[$i], $col[$i]);
        }
    }

    public function testFlatten()
    {
        $source = [
            ['a', 'b'],
            'c', 'd',
            ['e', ['f', 'g', ['h']]]
        ];
        $dest = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'
        ];
        $col1 = Collection::fromArray($source);
        $col2 = $col1->flatten();
        $this->assertNotSame($col1, $col2);
        $this->assertTrue($col2 instanceof Collection);
        $this->assertEquals($dest, $col2->toArray());
        // check that the array wasn't changed
        $this->assertEquals($source, $col1->toArray());
    }

    public function testSort()
    {
        $bigToSmall = function ($a, $b) {
            return ($a > $b) ? -1 : 1;
        };
        $col1 = $this->object->sort($bigToSmall);
        $this->assertNotNull($col1);
        $this->assertTrue($col1 instanceof Collection);
        $this->assertNotSame($this->object, $col1);
        $this->assertEquals($this->source, $this->object->toArray());
        $this->assertEquals([6, 5, 4, 3, 2, 1], $col1->toArray());
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->object[3]));
        $this->assertFalse(isset($this->object['x']));
    }

    public function testOffsetGet()
    {
        for ($i = 0; $i < count($this->source); $i++) {
            $this->assertEquals($this->source[$i], $this->object[$i]);
        }
    }

    /**
     * This is an immutable collection, so it should throw LogicException.
     */
    public function testOffsetSet()
    {
        try {
            $this->object[4] = 'x';
            $this->fail("Didn't throw LogicException");
        } catch (LogicException $ex) {
            // OK
        }
    }

    /**
     * This is an immutable collection, so it should throw LogicException.
     */
    public function testOffsetUnset()
    {
        try {
            unset($this->object[4]);
            $this->fail("Didn't throw LogicException");
        } catch (LogicException $ex) {
            // OK
        }
    }

    /**
     * Validates that method chaining works.
     */
    public function testMethodChains()
    {
        $sumDouble = Collection::fromArray($this->source)
                ->filter(function ($x) { return $x % 2 == 0; })
                ->map(function ($x) { return $x + 7; })
                ->reduce(function ($a, $b) { return $a + $b * 2; });
        $this->assertEquals(66, $sumDouble);
    }

}

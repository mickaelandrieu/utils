<?php

namespace BackBee\Utils\Test;

use BackBee\Utils\Arrays\Arrays;

/**
 * @author      c.rouillon <rouillon.charles@gmail.com>
 * @author      Mickaël Andrieu <mickael.andrieu@lp-digital.fr>
 */
class ArraysTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $_mock;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $this->mock = [
            'key' => [
                'subkey' => [
                    'subsubkey' => 'value',
                ],
            ],
        ];
    }

    public function testHas()
    {
        $this->assertTrue(Arrays::has($this->mock, 'key:subkey:subsubkey'));
        $this->assertFalse(Arrays::has($this->mock, 'key:subkey:unknown'));
        $this->assertFalse(Arrays::has($this->mock, 'key:subkey:subsubkey:unknown'));
        $this->assertTrue(Arrays::has($this->mock, 'key::subkey::subsubkey', '::'));
        $this->assertFalse(Arrays::has($this->mock, 'key:subkey:subsubkey', '::'));
    }

    /**
     * @expectedException \Exception
     */
    public function testHasWithInvalidKey()
    {
        $this->assertTrue(Arrays::has($this->mock, new \stdClass()));
    }

    /**
     * @expectedException \Exception
     */
    public function testHasWithInvalidSeparator()
    {
        $this->assertTrue(Arrays::has($this->mock, 'key', new \stdClass()));
    }

    public function testGet()
    {
        $this->assertEquals('value', Arrays::get($this->mock, 'key:subkey:subsubkey'));
        $this->assertNull(Arrays::get($this->mock, 'key:subkey:unknown'));
        $this->assertNull(Arrays::get($this->mock, 'key:subkey:subsubkey:unknown'));
        $this->assertEquals('default', Arrays::get($this->mock, 'key:subkey:subsubkey:unknown', 'default'));
        $this->assertEquals('value', Arrays::get($this->mock, 'key::subkey::subsubkey', null, '::'));
        $this->assertNull(Arrays::get($this->mock, 'key:subkey:subsubkey', null, '::'));

        $result = [
            'subkey' => [
                'subsubkey' => 'value',
            ],
        ];
        $this->assertEquals($result, Arrays::get($this->mock, 'key'));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetWithInvalidKey()
    {
        $this->assertTrue(Arrays::get($this->mock, new \stdClass()));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetWithInvalidSeparator()
    {
        $this->assertTrue(Arrays::get($this->mock, 'key', null, new \stdClass()));
    }

    public function testArray_column()
    {
        $mock = [
            'unused',
            [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ],
        ];

        $this->assertEquals([
            [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ],
        ], Arrays::array_column($mock));
        $this->assertEquals(['John', 'Sally', 'Jane', 'Peter'], Arrays::array_column($mock, 'first_name'));
        $this->assertEquals([2135 => 'John', 3245 => 'Sally', 5342 => 'Jane', 5623 => 'Peter'], Arrays::array_column($mock, 'first_name', 'id'));
        $this->assertEquals([
            2135 => [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            3245 => [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            5342 => [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            5623 => [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ],
        ], Arrays::array_column($mock, null, 'id'));
    }

    public function testToCsv()
    {
        $users = [0 => ['name' => 'Charles', 'role' => 'lead developper'],
            1 => ['name' => 'Eric', 'role' => 'developper'],
        ];

        $this->assertSame("Charles;lead developper\nEric;developper\n", Arrays::toCsv($users));
    }

    public function testToBasicXml()
    {
        $users = ['users' => [
            0 => ['name' => 'Charles', 'role' => 'lead developper'],
            1 => ['name' => 'Eric', 'role' => 'developper'],
            ],
        ];

        $this->assertSame('<users><0><name>Charles</name><role>lead developper</role></0><1><name>Eric</name><role>developper</role></1></users>', Arrays::toBasicXml($users));
    }

    public function testToXml()
    {
        $users = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper', 'drink' => 'milk & chocolate'],
            ],
        ];

        $xmlReturn = '<users><1><name>Charles</name><role>lead developper</role></1><2><name>Eric</name><role>developper</role><drink>milk &amp; chocolate</drink></2></users>';
        $this->assertSame($xmlReturn, Arrays::toXml($users));
    }

    public function testArrayDiffAssocRecursive()
    {
        $users = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            ],
        ];

        $users2 = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $diff1 = [ ];
        $diff2 = ['users' => [
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $this->assertSame(Arrays::array_diff_assoc_recursive($users, $users2), $diff1);
        $this->assertSame(Arrays::array_diff_assoc_recursive($users2, $users), $diff2);
    }

    public function testArrayMergeAssocRecursive()
    {
        $users = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            ],
        ];

        $users2 = ['users' => [
            2 => ['name' => 'Eric', 'role' => 'developper'],
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $mergedUsers = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $this->assertSame(Arrays::array_merge_assoc_recursive($users, $users2), $mergedUsers);
    }

    public function testArrayRemoveAssocRecursive()
    {
        $allUsers = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $nicolas = ['users' => [
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $expectedResult = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            ],
        ];

        Arrays::array_remove_assoc_recursive($allUsers, $nicolas);

        $this->assertSame($allUsers, $expectedResult);
    }

    public function tearDown()
    {
        $this->mock = null;
    }
}

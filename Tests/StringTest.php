<?php
/*
 * Copyright (c) 2011-2014 Lp digital system
 *
 * This file is part of BackBee.
 *
 * BackBee is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BackBee is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with BackBee. If not, see <http://www.gnu.org/licenses/>.
 */
namespace BackBee\Utils\Tests;

use BackBee\Utils\String;

/**
 * @author Flavia Fodor <flavia.flodor@lp-digital.fr>
 * @author Eric Chau <eric.chau@lp-digital.fr>
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
    public function testToASCII()
    {
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII('test')));
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII('te90-+st\\')));
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII('accentu�')));
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII("-100")));
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII("l’avocat", 'UTF-8')));
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII("1345623", 'ISO-8859-1')));
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII("é123", 'UTF-8')));
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII("-100", 'ISO-8859-1')));
        $this->assertEquals('ASCII', mb_detect_encoding(String::toASCII("©ÉÇáñ", 'UTF-8')));
    }

    public function testToUTF8()
    {
        $this->assertEquals('UTF-8', mb_detect_encoding(String::toUTF8('aaa'), 'UTF-8', true));
        $this->assertEquals('UTF-8', mb_detect_encoding(String::toUTF8('accentu�'), 'UTF-8', true));
        $this->assertEquals('UTF-8', mb_detect_encoding(String::toUTF8('reçoivent'), 'UTF-8', true));
        $this->assertEquals('UTF-8', mb_detect_encoding(String::toUTF8('©ÉÇáñ'), 'UTF-8', true));
    }

    public function testToBoolean()
    {
        $trueValues = ['1', 'on', 'true', 'yes'];
        foreach($trueValues as $value) {
            $this->assertTrue(String::toBoolean($value));
        }

        $falseValues = ['0', 'off', 'false', 'no'];
        foreach ($falseValues as $value) {
            $this->assertFalse(String::toBoolean($value));
        }
    }

    /**
     * @expectedException \BackBee\Utils\Exception\InvalidArgumentException
     */
    public function testToBooleanWithIntegerValues()
    {
        String::toBoolean(1);
    }

    public function testToPath()
    {
        $options1 = array(
            'extension' => '.txt',
            'spacereplace' => '_',
        );

        $this->assertEquals('test_path.txt', String::toPath('test path', $options1));

        $options2 = array(
            'extension' => '.txt',
            'spacereplace' => '_',
            'lengthlimit' => 5,
        );

        $this->assertEquals('test_.txt', String::toPath('test path', $options2));

        $options3 = array();

        $this->assertEquals('testpath', String::toPath('test path', $options3));

        $options4 = array(
            'extension' => '.jpg',
        );

        $this->assertEquals('testpath.jpg', String::toPath('test path', $options4));

        $options5 = array(
            'spacereplace' => '+',
        );

        $this->assertEquals('test+path', String::toPath('test path', $options5));

        $options6 = array(
            'lengthlimit' => 3,
        );

        $this->assertEquals('tes', String::toPath('test path', $options6));

        $options7 = array(
            'new' => 'aaa',
        );

        $this->assertEquals('testpath', String::toPath('test path', $options7));

        $this->assertEquals('foodefaut.yml', String::toPath('foo/défaut.yml'));
    }

    public function testUrlize()
    {
        $this->assertEquals('test-s-url', String::urlize('test’s url'));

        $this->assertEquals('test-s-url', String::urlize('test\'s url'));

        $this->assertEquals('test-euro-url', String::urlize('test € url'));

        $this->assertEquals('percent-euro', String::urlize('® % € “ ” …'));

        $this->assertEquals('', String::urlize('“ ” …'));

        $this->assertEquals('tests_url.com', String::urlize('test`s url', array(
                    'extension' => '.com',
                    'spacereplace' => '_',
        )));

        $this->assertEquals('tests_u.com', String::urlize('test`s url', array(
                    'extension' => '.com',
                    'spacereplace' => '_',
                    'lengthlimit' => 7,
        )));

        $this->assertEquals('tests#the#url#this#one.com', String::urlize('test`s the.url:this\'one', array(
                    'extension' => '.com',
                    'separators' => '/[.\'’:]+/',
                    'spacereplace' => '#',
        )));
    }

    public function testToXmlCompliant()
    {
        $this->assertEquals(' test line ', String::toXmlCompliant('<a> test line </a>'));
        $this->assertEquals('&amp;lt;a&amp;gt; test line &amp;lt;/a&amp;gt;', String::toXmlCompliant('<a> test line </a>', false));
    }

    public function testBr2nl()
    {
        $this->assertEquals("test aaa \r\ntest bbb \r\ntest ccc \r\n", String::br2nl("test aaa <br> test bbb <br> test ccc <br>"));
        $this->assertEquals("test aaa \r\ntest bbb \r\ntest ccc \r\n", String::br2nl("test aaa <br\> test bbb <br> test ccc <br\>"));
        $this->assertEquals("test aaa \r\ntest bbb \r\ntest ccc", String::br2nl("test aaa <br \> test bbb <br \> test ccc"));
    }

    public function testTruncateText()
    {
        $this->assertEquals('text +newstring', String::truncateText('text of test ', 2, '+newstring'));
        $this->assertEquals('text of test ', String::truncateText('text of test ', 30, '+newstring'));
        $this->assertEquals('text ', String::truncateText('text ', 10, '+newstring'));
        $this->assertEquals('text+newstring', String::truncateText('text of test', 5, '+newstring', true));
    }

    public function testFormatBytes()
    {
        $this->assertEquals('1.953 kb', String::formatBytes(2000, 3));
        $this->assertEquals('553.71094 kb', String::formatBytes(567000, 5));
        $this->assertEquals('553.71 kb', String::formatBytes(567000));
        $this->assertEquals('5.28 gb', String::formatBytes(5670008902));
    }
}

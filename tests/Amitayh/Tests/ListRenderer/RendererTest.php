<?php

namespace Amitayh\Tests\ListRenderer;

use Amitayh\ListRenderer\Renderer;

class RendererTest extends \PHPUnit_Framework_TestCase
{

    public function testEmptyList() {
        $renderer = new Renderer();
        $iterator = new \ArrayIterator();

        $expected = '<ul></ul>';
        $actual = $renderer->render($iterator);

        $this->assertEquals($expected, $actual);
    }

    public function testOneLevel() {
        $renderer = new Renderer();
        $iterator = new \ArrayIterator(array(
            new ItemStub(0, 'Item #1'),
            new ItemStub(0, 'Item #2'),
            new ItemStub(0, 'Item #3')
        ));

        $expected = <<<HTML
<ul>
    <li>Item #1</li>
    <li>Item #2</li>
    <li>Item #3</li>
</ul>
HTML;

        $expected = $this->clean($expected);
        $actual = $renderer->render($iterator);

        $this->assertEquals($expected, $actual);
    }

    public function testFirstChildHasChildren() {
        $renderer = new Renderer();
        $iterator = new \ArrayIterator(array(
            new ItemStub(0, 'Item #1'),
            new ItemStub(1, 'Item #1.1'),
            new ItemStub(0, 'Item #2'),
            new ItemStub(0, 'Item #3')
        ));

        $expected = <<<HTML
<ul>
    <li>
        Item #1
        <ul>
            <li>Item #1.1</li>
        </ul>
    </li>
    <li>Item #2</li>
    <li>Item #3</li>
</ul>
HTML;

        $expected = $this->clean($expected);
        $actual = $renderer->render($iterator);

        $this->assertEquals($expected, $actual);
    }

    public function testCloseMultipleLevels() {
        $renderer = new Renderer();
        $iterator = new \ArrayIterator(array(
            new ItemStub(0, 'Item #1'),
            new ItemStub(1, 'Item #1.1'),
            new ItemStub(2, 'Item #1.1.1'),
            new ItemStub(0, 'Item #2'),
            new ItemStub(0, 'Item #3')
        ));

        $expected = <<<HTML
<ul>
    <li>
        Item #1
        <ul>
            <li>
                Item #1.1
                <ul>
                    <li>Item #1.1.1</li>
                </ul>
            </li>
        </ul>
    </li>
    <li>Item #2</li>
    <li>Item #3</li>
</ul>
HTML;

        $expected = $this->clean($expected);
        $actual = $renderer->render($iterator);

        $this->assertEquals($expected, $actual);
    }

    public function testCloseLastChild() {
        $renderer = new Renderer();
        $iterator = new \ArrayIterator(array(
            new ItemStub(0, 'Item #1'),
            new ItemStub(0, 'Item #2'),
            new ItemStub(0, 'Item #3'),
            new ItemStub(1, 'Item #3.1')
        ));

        $expected = <<<HTML
<ul>
    <li>Item #1</li>
    <li>Item #2</li>
    <li>
        Item #3
        <ul>
            <li>Item #3.1</li>
        </ul>
    </li>
</ul>
HTML;

        $expected = $this->clean($expected);
        $actual = $renderer->render($iterator);

        $this->assertEquals($expected, $actual);
    }

    public function testCrazyNesting() {
        $renderer = new Renderer();
        $iterator = new \ArrayIterator(array(
            new ItemStub(0, 'Item #1'),
            new ItemStub(1, 'Item #1.1'),
            new ItemStub(2, 'Item #1.1.1'),
            new ItemStub(2, 'Item #1.1.2'),
            new ItemStub(0, 'Item #2'),
        ));

        $expected = <<<HTML
<ul>
    <li>
        Item #1
        <ul>
            <li>
                Item #1.1
                <ul>
                    <li>Item #1.1.1</li>
                    <li>Item #1.1.2</li>
                </ul>
            </li>
        </ul>
    </li>
    <li>Item #2</li>
</ul>
HTML;

        $expected = $this->clean($expected);
        $actual = $renderer->render($iterator);

        $this->assertEquals($expected, $actual);
    }

    public function testMainListAttributes() {
        $renderer = new Renderer();
        $renderer->setMainListAttributes(array('id' => 'foo', 'class' => 'bar'));
        $iterator = new \ArrayIterator(array(
            new ItemStub(0, 'Item #1'),
            new ItemStub(0, 'Item #2'),
            new ItemStub(0, 'Item #3')
        ));

        $expected = <<<HTML
<ul id="foo" class="bar">
    <li>Item #1</li>
    <li>Item #2</li>
    <li>Item #3</li>
</ul>
HTML;

        $expected = $this->clean($expected);
        $actual = $renderer->render($iterator);

        $this->assertEquals($expected, $actual);
    }

    public function testSubListAttributes() {
        $renderer = new Renderer();
        $renderer->setSubListAttributes(array('class' => 'sub-list'));
        $iterator = new \ArrayIterator(array(
            new ItemStub(0, 'Item #1'),
            new ItemStub(1, 'Item #1.1'),
            new ItemStub(1, 'Item #1.2'),
            new ItemStub(0, 'Item #2'),
            new ItemStub(1, 'Item #2.1'),
            new ItemStub(1, 'Item #2.2')
        ));

        $expected = <<<HTML
<ul>
    <li>
        Item #1
        <ul class="sub-list">
            <li>Item #1.1</li>
            <li>Item #1.2</li>
        </ul>
    </li>
    <li>
        Item #2
        <ul class="sub-list">
            <li>Item #2.1</li>
            <li>Item #2.2</li>
        </ul>
    </li>
</ul>
HTML;

        $expected = $this->clean($expected);
        $actual = $renderer->render($iterator);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Amitayh\ListRenderer\InvalidLevelException
     */
    public function testInvalidLevelException() {
        $renderer = new Renderer();
        $iterator = new \ArrayIterator(array(
            new ItemStub(0, 'Item #1'),
            new ItemStub(2, 'Item #2')
        ));
        $renderer->render($iterator);
    }

    /**
     * @expectedException \Amitayh\ListRenderer\InvalidLevelException
     */
    public function testInvalidLevelExceptionOnFirstChild() {
        $renderer = new Renderer();
        $iterator = new \ArrayIterator(array(
            new ItemStub(1, 'Item #1')
        ));
        $renderer->render($iterator);
    }

    private function clean($str) {
        $str = str_replace("\n", '', $str);
        $str = preg_replace('/\s{2,}/', '', $str);

        return $str;
    }

}
 
<?php

use Cti\Tools\Locator;
use Cti\Tools\String;
use Cti\Tools\View;
use Cti\Tools\Web;
use Cti\Di\Manager;

class ToolsTests extends PHPUnit_Framework_TestCase
{
    function testLocator()
    {
        $l = new Locator(__DIR__);

        // file that exists in this project
        $this->assertSame($l->path('ToolsTests.php'), __FILE__);
        $this->assertSame(
            $l->base('ToolsTests.php'),
            implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'ToolsTests.php'))
        );

        // file that exists in the base
        $reflection = new ReflectionClass('Cti\Tools\Locator');
        $this->assertSame($l->path('src Tools Locator.php'), $reflection->getFileName());

        // project location 
        $this->assertSame(
            $l->project('src Tools Locator.php'), 
            implode(DIRECTORY_SEPARATOR, array(__DIR__, 'src', 'Tools', 'Locator.php'))
        );

        // ignore duplicate spaced 
        $this->assertSame(
            $l->project('a b'), 
            $l->project('a  b')
        );

        // working with array 
        $this->assertSame(
            $l->project('a', 'b'), 
            $l->project('a  b')
        );

        // not exists file - project location
        $this->assertSame($l->path('no-file'), __DIR__ . DIRECTORY_SEPARATOR . 'no-file');
    }

    function testView()
    {
        $view = new View(new Locator(__DIR__));
        ob_start();
        $view->show('test');
        $this->assertSame($view->render('test'), ob_get_clean());

        $this->assertSame('exception', $view->render('exception', array('test' => true)));
    }

    function testWebBaseStartException()
    {
        $this->setExpectedException('Exception');
        
        $web = new Web;
        $web->base = 'test';
        $web->init();
    }

    function testWebBaseEndException()
    {
        $this->setExpectedException('Exception');

        $web = new Web;
        $web->base = '/test';
        $web->init();
    }

    function testBasics()
    {
        $m = new Manager;
        $web = $m->get('Cti\Tools\Web');

        $this->assertTrue($m->contains('Symfony\Component\HttpFoundation\Request'));

        $this->assertSame($web->getUrl('test'), '/test');
    }

    function testChainCalculation()
    {
        $manager = new Manager();
        $manager->get('Cti\Di\Configuration')->set('Cti\Tools\Web', 'base', '/application/');


        $mock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->setMethods(array('getPathInfo', 'getMethod'))
            ->getMock();

        $manager->register($mock, 'Symfony\Component\HttpFoundation\Request');

        $mock->method('getMethod')->will($this->returnValue('POST'));
        $mock->method('getPathInfo')->will($this->returnValue('/application//hello'));

        $this->assertSame($manager->get('Cti\Tools\Web')->base, '/application/');
        $this->assertSame($manager->get('Cti\Tools\Web')->chain, array('hello'));
    }

    function testChainProcessing()
    {
        $manager = new Manager();
        $web = $manager->get('Cti\Tools\Web');

        ob_start();
        $web->method = 'get';
        $web->chain = array();
        $web->process('Common\Controller');
        $this->assertSame(ob_get_clean(), 'index page');

        ob_start();
        $web->chain = array('upload');
        $web->method = 'post';
        $web->process('Common\Controller');
        $this->assertSame(ob_get_clean(), 'uploading');

        ob_start();
        $web->chain = array('something');
        $web->method = 'get';
        $web->process('Common\Controller');
        $this->assertSame(ob_get_clean(), json_encode(array('something')));

        ob_start();
        $web->method = 'get';
        $web->chain = array();
        $web->process('Common\ExceptionHandlingController');
        $this->assertSame(ob_get_clean(), 'Not found');

        $this->setExpectedException('Exception');
        ob_start();
        $web->process('Common\Application');
        $this->assertSame(ob_get_clean(), 'Not found');
    }

    public function testConvertToCamelCase()
    {
        $this->assertSame(String::convertToCamelCase('hello_world'), 'HelloWorld');
    }

    public function testCamelCaseToUnderScore()
    {
        $this->assertSame(String::camelCaseToUnderScore('ThisIsTest'), 'this_is_test');
    }

    public function testPluralize()
    {
        $this->assertSame(String::pluralize('cat'), 'cats');
        $this->assertSame(String::pluralize('pony'), 'ponies');
        $this->assertSame(String::pluralize('bass'), 'basses');
        $this->assertSame(String::pluralize('case'), 'cases');
    }

    public function testFormatBytes()
    {
        $this->assertSame(String::formatBytes(1024), '1k');
        $this->assertSame(String::formatBytes(1024*1024*2), '2M');
        $this->assertSame(String::formatBytes(1024*1024*1024*4), '4G');
        $this->assertSame(String::formatBytes(1024*1024*1024*1024*5), '5T');
    }
}

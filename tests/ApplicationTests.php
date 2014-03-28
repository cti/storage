<?php

use Application\Locator;
use Application\View;
use Application\Web;
use Di\Manager;

class ApplicationTests extends PHPUnit_Framework_TestCase
{
    function testLocator()
    {
        $l = new Locator(__DIR__);

        // file that exists in this project
        $this->assertSame($l->path('ApplicationTests.php'), __FILE__);
        $this->assertSame(
            $l->base('ApplicationTests.php'),
            implode(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'ApplicationTests.php'))
        );

        // file that exists in the base
        $reflection = new ReflectionClass('Application\Locator');
        $this->assertSame($l->path('src php Application Locator.php'), $reflection->getFileName());

        // project location 
        $this->assertSame(
            $l->project('src php Application Locator.php'), 
            implode(DIRECTORY_SEPARATOR, array(__DIR__, 'src', 'php', 'Application', 'Locator.php'))
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
        $web = $m->get('Application\Web');

        $this->assertTrue($m->contains('Symfony\Component\HttpFoundation\Request'));

        $this->assertSame($web->getUrl('test'), '/test');
    }

    function testChainCalculation()
    {
        $manager = new Manager();
        $manager->get('Di\Configuration')->set('Application\Web', 'base', '/application/');


        $mock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->setMethods(array('getPathInfo', 'getMethod'))
            ->getMock();

        $manager->register($mock, 'Symfony\Component\HttpFoundation\Request');

        $mock->method('getMethod')->will($this->returnValue('POST'));
        $mock->method('getPathInfo')->will($this->returnValue('/application//hello'));

        $this->assertSame($manager->get('Application\Web')->chain, array('hello'));
    }

    function testChainProcessing()
    {
        $manager = new Manager();
        $web = $manager->get('Application\Web');

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
}

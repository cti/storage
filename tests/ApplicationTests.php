<?php

use Application\Locator;

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
}

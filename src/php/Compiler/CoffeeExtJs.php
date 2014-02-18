<?php

namespace Compiler;

use Application\Locator;

class CoffeeExtJs
{
    protected $locator;

    protected $scriptList = array();

    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
        $this->output = $locator->project('out js');
    }

    public function compile($path)
    {
        $coffee = $this->locator->path('resources coffee ' . $path . '.coffee');
        $js = $this->locator->path('out js '. $path .'.js');

        $content = '';
        foreach($this->manageDependencies($coffee, $js) as $script) {
            $content .= file_get_contents($script);
        }

        $pack = $this->locator->path('public js '. $path .'.js');

        if(!file_exists($pack) || md5(file_get_contents($pack)) != md5($content)) {
            $this->locator->createDirectory(dirname($pack));
            file_put_contents($pack, $content);
        }

        return 'public/js/'.$path.'.js';
    }

    protected function manageDependencies($coffee, $js)
    {
        if (in_array($js, $this->scriptList)) {
            return;
        }

        if (!file_exists($js) || filemtime($js) < filemtime($coffee)) {

            $out = dirname($js);

            $this->locator->createDirectory($out);

            $command = "coffee -bo $out -c $coffee";

            $data = array();
            exec($command, $data, $result);
            if ($result) {
                throw new \Exception('Execution failed: ' . $command);
            }
        }

        $dependencies = array();

        foreach($this->getScriptDependencies(file_get_contents($js)) as $dependency) {
            if (strpos($dependency, 'Ext.') === 0) {
                continue;
            }
            // if($dependency != $class) {
            $path = implode(' ', explode('.', $dependency));
            $dependency_coffee = $this->locator->path('src coffee '.$path.'.coffee');
            $dependency_js = $this->locator->path('out js '.$path.'.js');
            foreach($this->manageDependencies($dependency_coffee, $dependency_js) as $child_dependency_script) {
                $scriptList[] = $child_dependency_script;
            }

            $dependencies[] = $dependency;
        }

        $scriptList[] = $js;
        return $scriptList;
    }

    public function getScriptDependencies($text)
    {
        return array_merge(
            $this->getRequires($text),
            $this->getMixins($text),
            $this->getCreate($text),
            $this->getExtend($text)
        );


        $dependencies = array();
        $dependencies = array_merge($dependencies, $this->getRequires($text));
        $dependencies = array_merge($dependencies, $this->getMixins($text));
        $dependencies = array_merge($dependencies, $this->getCreate($text));
        $dependencies = array_merge($dependencies, $this->getExtend($text));

        return $dependencies;
    }

    public function getRequires($text)
    {
        $requires = array();
        $pregs = array(
            "/Ext.require\(['\"]([a-zA-Z0-9.]+)['\"]/",
            "/Ext.syncRequire\(['\"]([a-zA-Z0-9.]+)['\"]/",
        );
        foreach ($pregs as $p) {
            preg_match_all($p, $text, $answer);
            $requires = array_merge($requires,$answer[1]);
        }

        $p = "/requires\s*:\s*\[['\"a-zA-Z0-9.,\s]+\]/";
        preg_match_all($p, $text, $output);
        $p = "/['\"]([a-zA-Z0-9.]*)['\"]/";
        $required_classes = array();
        foreach ($output[0] as $require) {
            preg_match_all($p, $require, $match);
            $required_classes = array_merge($required_classes, $match[1]);
        }

        return array_merge($requires,$required_classes);

    }

    public function getMixins($text)
    {
        $mix = array();
        $p = "/mixins\s*:\s*[\[{][^\[\]}{]+[\]}]/";
        preg_match_all($p, $text, $match);
        $p = "/['\"]([a-zA-Z0-9._]+)['\"]/";
        foreach ($match[0] as $mixin) {
            preg_match_all($p, $mixin, $classes);
            if ($classes[1]) {
                $mix = array_merge($mix, $classes[1]);
            }
        }

        return $mix;
    }

    public function getExtend($text)
    {
        $p = "/extend\s*:\s*['\"]([a-zA-Z0-9._]+)['\"]/";
        preg_match_all($p, $text, $answer);

        return $answer[1];
    }

    public function getCreate($text)
    {
        $p = "/Ext.create\(['\"]([a-zA-Z0-9.]+)['\"]/";
        preg_match_all($p, $text, $answer);

        return $answer[1];
    }
}
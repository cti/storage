<?php

namespace Nekufa\Direct;

use Nekufa\Di\Manager;
use Nekufa\Di\Reflection;

class Service
{
    /**
     * action map to class
     * @var array
     */
    protected $classes = array();

    /**
     * action description
     * @var array
     */
    protected $actions = array();

    /**
     * response hash
     * @var array[Response]
     */
    protected $responses = array();

    /**
     * @param $config
     */
    function __construct($config)
    {
        foreach ($config as $class) {
            $alias = basename(str_replace('\\', DIRECTORY_SEPARATOR, $class));
            $this->classes[$alias] = $class;

            $this->actions[$alias] = array();
            foreach (Reflection::getReflectionClass($class)->getMethods() as $method) {
                if (!$method->isConstructor()) {
                    $len = 0;
                    foreach ($method->getParameters() as $parameter) {
                        if (is_null($parameter->getClass())) {
                            $len++;
                        }
                    }
                    $this->actions[$alias][] = (object)array(
                        'name' => $method->getName(),
                        'len' => $len,
                    );
                }
            }
        }
    }

    /**
     * @param Manager $manager
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    function handle(Manager $manager, Request $request)
    {
        if (!isset($this->classes[$request->getAction()])) {
            throw new Exception(sprintf("Action %s not found", $request->getAction()));
        }

        if (isset($this->responses[$request->getTid()])) {
            return $this->responses[$request->getTid()];
        }

        foreach ($this->actions[$request->getAction()] as $info) {
            if ($request->getMethod() == $info->name) {
                $response = $request->generateResponse();
                try {
                    $response->setType($request->getType());
                    $response->setResult($manager->call(
                        $this->classes[$request->getAction()],
                        $request->getMethod(),
                        $request->getData()
                    ));

                } catch (\Exception $e) {
                    $response->setType('exception');
                    $response->setFile($e->getFile());
                    $response->setLine($e->getLine());
                }
                return $this->responses[$response->getId()] = $response;
            }
        }
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

}
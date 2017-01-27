<?php
use Cake\Controller\Controller;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use BRFilter\Controller\Component\FilterComponent;
use Cake\ORM\Entity;

class Test extends TestCase
{

    public $theController;

    public $plugin;

    public function testPluginComponent()
    {
        // ...
        $request = new Request();
        $response = new Response();
        
        $this->theController = new Controller($request, $response);
        $this->plugin = new FilterComponent($this->theController->components());
        
    }
}

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
        
        $this->theController->loadComponent('BRFilter.Filter');
        
        $this->theController->Filter->addFilter([
            'filter_id' => [
                'field' => 'Posts.id',
                'operator' => '='
            ],
            'filter_title' => [
                'field' => 'Posts.title',
                'operator' => 'LIKE',
                'explode' => 'true'
            ],
            'filter_category_id' => [
                'field' => 'Posts.category_id',
                'operator' => 'IN'
            ]
        ]);
        
        $this->theController->request->data = [
            'filter_id' => 1,
            'filter_title' => 'test',
            'filter_category_id' => [
                1,
                2
            ]
        ];
        
        // get conditions
        $conditions = $this->theController->Filter->getConditions([
            'session' => 'filter'
        ]);
        $url = $this->theController->Filter->getUrl();
        
        $this->assertNotEmpty($conditions);
        
        $this->assertNotEmpty($url);
    }
}

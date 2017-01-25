# Filter plugin for CakePHP 3.x

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require brenoroosevelt/cakephp-filter
```
### Load the plugin

Add following to your `config/bootstrap.php`

```php
Plugin::load('BRFilter');
```

## Usage

### Controller class

```php
public function index()
{

		$this->loadComponent('BRFilter.Filter');
		
		// add filter and options
		$this->Filter->addFilter([
					'filter_id' => ['field' => 'Post.id', 'operator'=>'='],
					'filter_title' => ['field' => 'Post.title', 'operator' => 'LIKE', 'explode' => 'true'],
					'filter_category_id' => ['field'=> 'Post.category_id', 'operator' => 'IN' ] 
		]);
		
		// get conditions
		$conditions = $this->Filter->getConditions(['session'=>'filter']);
		
		// set url for pagination
    	$this->set('url', $this->Filter->getUrl());
    	
    	// apply conditions to pagination
    	$this->paginate['conditions']	= $conditions;
    	
    	// get pagination 
    	$this->set('posts', $this->paginate($this->Post));
    	
    	// ...
    	
}
```

### Template views 
You have to add a form to your index.ctp, corresponding with the alias of your filter configuration.

```php
    
    // set url to paginator (it keeps filter applied when navigate)
	$this->Paginator->options(['url' => $url]);
    
	echo $this->Form->create();
    
   	// Match with the filter configuration in your controller 
    echo $this->Form->input('filter_id', ['type' => 'text']);
    echo $this->Form->input('filter_title', ['type' => 'text']);
    echo $this->Form->input('filter_category_id', ['options'=>[ /* ... */ ], 'multiple'=>'multiple' ]);
    
	echo $this->Form->button('Filter', ['type' => 'submit']);
	echo $this->Form->end();
```

### Filter options

The following options are supported:

- `field` (`string`) The name of the field to use for searching.
- `operator` (`string`) The operator used for searching.
- `explode` (`boolean`) Used only with operator `LIKE` and `ILIKE` to explode the string query.


### Operators

The following options are supported:

- `=`
- `>`
- `<`
- `>=`
- `<=`
- `LIKE`
- `ILIKE`
- `IN`
 
### Persisting query (session)

All query strings are persisted using sessions. Make sure to load the Session component.

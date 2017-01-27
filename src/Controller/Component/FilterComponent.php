<?php
namespace BRFilter\Controller\Component;

use Cake\Controller\Component;

class FilterComponent extends Component
{

    public $the_controller = null;

    public $filters, $conditions, $query = [];

    public $pagination_key = [
        '_',
        'sort',
        'direction',
        'page',
        'lang',
        'limit'
    ];

    public $operators = [
        'LIKE',
        'NOT LIKE',
        '=',
        '>',
        '>=',
        '<',
        '<=',
        '<>',
        'IN',
        'NOT IN'
    ];

    public $_url = [];

    public function initialize(array $config = [])
    {
        parent::initialize($config);
        $this->the_controller = $this->_registry->getController();
    }

    public function addFilter($filter)
    {
        $this->filters = $filter;
    }

    private function getFilter($field, $value, $filter)
    {
        $operator = isset($this->filters[$filter]['operator']) ? $this->filters[$filter]['operator'] : '=';
        $before = isset($this->filters[$filter]['before']) ? $this->filters[$filter]['before'] : '';
        $after = isset($this->filters[$filter]['after']) ? $this->filters[$filter]['after'] : '';
        $explode = isset($this->filters[$filter]['explode']) ? $this->filters[$filter]['explode'] : '';
        
        if (! $before && ! $after)
            $before = $after = "%";
            
            // se for LIKE, NOT LIKE, colocar %%
        if (in_array($operator, [
            'LIKE',
            'NOT LIKE',
            'ILIKE'
        ])) {
            
            if ($explode) {
                $value_exploded = explode(' ', $value);
                $OR_AND = [
                    "$explode" => []
                ];
                foreach ($value_exploded as $vl) {
                    $OR_AND[$explode][] = array(
                        "$field $operator" => "$before$vl$after"
                    );
                }
                return $OR_AND;
            }
            return array(
                "$field $operator" => "$before$value$after"
            );
        }
        // se for =...
        if (in_array($operator, [
            '='
        ]))
            return array(
                "$field" => $value
            );
            // CASO IN
        
        if (in_array($operator, [
            'IN',
            'NOT IN'
        ])) {
            if (! is_array($value)) {
                $value = explode(',', $value);
            }
            return array(
                "$field $operator" => $value
            );
        }
        
        // outros:
        return array(
            "$field $operator" => $value
        );
    }

    public function hasValue($value)
    {
        if (is_null($value))
            return false;
        
        if (is_string($value) && $value === "")
            return false;
        
        return true;
    }

    public function getConditions($save = [])
    {
        $conditions = [];
        
        // se for post, pega os dados para montar conditions
        foreach ($this->the_controller->request->data as $filter => $value) {
            
            if (array_key_exists($filter, $this->filters)) { // se o filtro existe
                                                             
                if ($this->hasValue($value)) { // se o valor foi preenchido
                    $field = $this->filters[$filter]['field'];
                    // prepara a url
                    $operator = isset($this->filters[$filter]['operator']) ? $this->filters[$filter]['operator'] : '=';
                    $this->_url[$filter] = in_array($operator, [
                        'IN',
                        'NOT IN'
                    ]) ? implode(',', $value) : $value;
                    
                    $conditions = array_merge($conditions, $this->getFilter($field, $value, $filter));
                }
            }
        }
        
        $exclude = $this->pagination_key;
        
        foreach ($this->the_controller->request->query as $qr_filter => $value) {
            
            if (in_array($qr_filter, $this->pagination_key)) {
                continue;
            }
            // $conditions[$qr] = $value;
            
            if (array_key_exists($qr_filter, $this->filters)) { // se o filtro existe
                
                if ($this->hasValue($value)) { // se o valor foi preenchido
                    
                    $field = $this->filters[$qr_filter]['field'];
                    $operator = isset($this->filters[$qr_filter]['operator']) ? $this->filters[$qr_filter]['operator'] : '=';
                    
                    $this->_url[$qr_filter] = $value;
                    $this->the_controller->request->data[$qr_filter] = in_array($operator, [
                        'IN',
                        'NOT IN'
                    ]) ? explode(',', $value) : $value; // mantem data preenchido...
                    $this->the_controller->request->query[$qr_filter] = in_array($operator, [
                        'IN',
                        'NOT IN'
                    ]) ? explode(',', $value) : $value;
                    
                    $conditions = array_merge($conditions, $this->getFilter($field, $value, $qr_filter));
                }
            }
        }
        
        $session = $this->the_controller->request->session();
        
        // tenta restaurar o filtro salvo na sessao
        if (! $this->the_controller->request->is('post') && empty($conditions) && isset($save['session'])) {
            $session_id = $save['session'];
            $saved_query = $session->read($session_id);
            
            IF ($saved_query) {
                foreach ($saved_query as $qr_filter => $value) {
                    
                    if (in_array($qr_filter, $this->pagination_key)) {
                        continue;
                    }
                    // $conditions[$qr] = $value;
                    
                    if (array_key_exists($qr_filter, $this->filters)) { // se o filtro existe
                        if ($this->hasValue($value)) { // se o valor foi preenchido
                            
                            $field = $this->filters[$qr_filter]['field'];
                            $operator = isset($this->filters[$qr_filter]['operator']) ? $this->filters[$qr_filter]['operator'] : '=';
                            
                            $this->_url[$qr_filter] = $value;
                            $this->the_controller->request->data[$qr_filter] = in_array($operator, [
                                'IN',
                                'NOT IN'
                            ]) ? explode(',', $value) : $value; // mantem data preenchido...
                            
                            $conditions = array_merge($conditions, $this->getFilter($field, $value, $qr_filter));
                        }
                    }
                }
            }
        }
        if (isset($save['session'])) {
            $session_id = $save['session'];
            $session->write($session_id, $this->_url);
        }
        
        return $conditions;
    }

    public function getUrl()
    {
        return $this->_url;
    }
}
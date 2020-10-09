<?php

namespace App\Filters;

use Illuminate\Http\Request;

abstract class Filters
{

    protected $request, $builder;
    protected $filters = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply($builder)
    {
        // We apply our filters to the builder

        $this->builder = $builder;

        foreach($this->getFilters() as $filter => $value){

            // per ognuno di questi parametri verifico se esiste il metodo specifico, es: by($username) e lo lancio per filtrare la query (builder)
            if(method_exists($this, $filter)){
                $this->$filter($value);
            }
        }

        return $this->builder;
    }

    protected function getFilters() // mi faccio tornare dalla request tutti i parametri contenuti nell'array $filters
    {
        return array_filter($this->request->only($this->filters)); //array_filter senza callback rimuove dall'array tutti gli elementi vuoti
    }
}
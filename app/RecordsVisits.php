<?php


namespace App;


use Illuminate\Support\Facades\Redis;

trait RecordsVisits
{
    public function visits()
    {
        return new Visits;
    }
}
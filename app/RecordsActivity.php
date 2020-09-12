<?php


namespace App;


use Illuminate\Support\Facades\Auth;

trait RecordsActivity
{

    // questo metodo statico viene lanciato da laravel quando viene invocato il metodo boot del modello stesso.,mklhllnòhlkklh
    // deve essere rispettata la name convention bootNomeDelTrait
    protected static function bootRecordsActivity()
    {

        if(Auth::guest()) return;

        foreach (static::getActivitiesRecord() as $event){
            static::$event(function($model) use ($event){
                $model->recordActivity($event);
            });
        }

        static::deleting(function($model){
            $model->activity()->delete();
        });
    }

    /**
     * @param $thread
     * @throws \ReflectionException
     */

    // dichiarare questo metodo nel modello per sovrascrivere le azioni
    protected static function getActivitiesRecord()
    {
        return ['created'];
    }

    protected function recordActivity($event)
    {
        $this->activity()->create([
            'user_id' => Auth::id(),
            'type' => $this->getActivityType($event)
        ]);
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    protected function getActivityType($event)
    {
        $type = strtolower((new \ReflectionClass($this))->getShortName());
        return "{$event}_{$type}";
    }
}
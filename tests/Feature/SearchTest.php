<?php

namespace Tests\Feature;

use App\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testAUserCanSearchThreads()
    {

        // Abilito l'utilizzo di scout solo per questo test.
        // Disabilitato per i test nel file phpunit.xml
        config(['scout.driver' => 'algolia']);

        $search = 'foobar';

        create(Thread::class, [], 2);

        create(Thread::class, ['body' => "A thread with the {$search} term."], 2);

        // le richieste ad algolia hanno un piccola latenza quindi si può creare la situazione in cui il modello è stato creato
        // ma l'indice su algolia non è stato ancora creato quindi continuo a riprovare fino a che non arriva il risultato
        // imposto anche un piccolo timeout per non fare troppe richieste consecutivamente
        do {
            sleep(.25);

            $results = $this->getJson("/threads/search?q={$search}")->json();
        } while(empty($results['data']));

        $this->assertCount(2, $results['data']);

        // Elimino tutti i record indicizzati (ad ogni creazione del modello viene sparato in automatico il modello ad algolia)
        // Il trait searchable registra delle macro searchable/unsearchable da lanciare su una collection che permette di aggiungere/rimuovere
        // gli indici per un intera collection
        Thread::all()->unsearchable();

    }
}

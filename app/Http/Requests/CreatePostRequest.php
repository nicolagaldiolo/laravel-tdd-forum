<?php

namespace App\Http\Requests;

use App\Exceptions\ThrottleException;
use App\Reply;
use App\Rules\SpamFree;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', Reply::class);
    }

    // Dato che ho bisogno di maggiore controllo sulla gestione di questa autorizzazione
    // in quanto voglio un errore 429 (troppe richieste effettuate) e non un semplice 403
    // riscrivo il metodo failedAuthorization e lancio una mia eccezzione custom
    protected function failedAuthorization()
    {
        throw new ThrottleException('You are posting too frequently. Please take a break. :)');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'body' => ['required', new SpamFree]
        ];
    }
}

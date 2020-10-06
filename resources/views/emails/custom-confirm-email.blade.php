@component('mail::message')
# One last Step

We just need you to confirm your email address to prove that you're a human. You get it, right? Coo.

@component('mail::button', ['url' => url('/register/custom_confirm?token=' . $user->confirmation_token)])
Confirm Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

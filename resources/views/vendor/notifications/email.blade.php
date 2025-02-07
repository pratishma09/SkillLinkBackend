<x-mail::message>
# Verify Email Address

Please click the button below to verify your email address.

<x-mail::button :url="$actionUrl">
Verify Email Address
</x-mail::button>

If you did not create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser: {{ $actionUrl }}
</x-mail::subcopy>
</x-mail::message> 
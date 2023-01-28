<x-mail::message>
{{ $author }} / {{ $category }} / {{ $publish_date }}

<x-mail::button :url="$url">
{{ $title }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

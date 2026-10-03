<x-mail::message>
# Nova solicitação para você

Olá, {{ $assigneeName }}!

**{{ $requesterName }}** abriu a solicitação **#{{ $ticketNumber }}** ({{ $typeLabel }}) e
ela foi encaminhada para você no Flowkly.

**{{ $title }}**

{{ $description }}

<x-mail::button :url="$kanbanUrl">
Ver no Kanban
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>

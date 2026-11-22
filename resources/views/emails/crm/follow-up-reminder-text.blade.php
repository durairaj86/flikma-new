Hello {{ $name }},

These follow-ups are due today or overdue:

@foreach($rows as $r)
- {{ $r['when'] }}{{ $r['overdue'] ? ' (overdue)' : '' }} · {{ $r['kind'] }} {{ $r['no'] }} {{ $r['name'] }}: {{ $r['todo'] }}
@endforeach

Open my follow-ups: {{ $url }}

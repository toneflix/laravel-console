Hi {{ $name ?? 'there' }}, {!! strip_tags($message_line1 ?? '') !!} [{{ $cta['code'] ?? ($cta['link'] ?? '') }}]
{!! strip_tags($message_line2 ?? '') !!}
{!! strip_tags($message_line3 ?? '') !!} {!! strip_tags($close_greeting ?? 'Good luck! Hope it works.') !!} {!! strip_tags($message_help ?? '') !!}

<h1>Good morning {{ $user->name }}</h1>

<p>Here is your Planora agenda for {{ \Carbon\Carbon::parse($date, $user->timezone)->toFormattedDayDateString() }}:</p>

@if(count($tasks) === 0)
    <p>You have no upcoming tasks scheduled today. Enjoy your day!</p>
@else
    <ul>
        @foreach($tasks as $task)
            <li>
                <strong>{{ $task['title'] }}</strong>
                @if(isset($task['time']))
                    at {{ $task['time'] }}
                @endif
                @if(isset($task['goal']))
                    (Goal: {{ $task['goal'] }})
                @endif
            </li>
        @endforeach
    </ul>
@endif

<p>Keep making progress!<br>{{ config('app.name') }}</p>

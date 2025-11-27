<h1>Task Reminder</h1>

<p>{{ $body }}</p>

@if($task)
    <p><strong>Task:</strong> {{ $task->title }}</p>
    @if($task->due_at)
        <p><strong>Due at:</strong> {{ $task->due_at->setTimezone($user->timezone)->toDayDateTimeString() }}</p>
    @elseif($task->due_date)
        <p><strong>Due on:</strong> {{ $task->due_date->format('Y-m-d') }}</p>
    @endif
@endif

<p>Stay productive,<br>{{ config('app.name') }}</p>

@component('mail::message')
# Employee Time Off Calendars Updated
<p>{{ $bodyMessage }}</p>
# New Events
@component('mail::table')
|            Employee           |        Start DateTime       |        End DateTime       |
|:-----------------------------:|:---------------------------:|:-------------------------:|
@foreach($newEvents  as $newEvent)
| {{ $newEvent['EMP_COMMON_FULL_NAME'] }} | {{ $newEvent['SCH_PCE_START_DATETIME'] }} | {{ $newEvent['SCH_PCE_END_DATETIME'] }} |
@endforeach
@endcomponent
<br>
<hr>
<br>
# Existing  Events
@component('mail::table')
|            Employee           |        Start DateTime       |        End DateTime       |
|:-----------------------------:|:---------------------------:|:-------------------------:|
@foreach($existingEvents as $existingEvent)
| {{ $existingEvent['EMP_COMMON_FULL_NAME'] }} | {{ $existingEvent['SCH_PCE_START_DATETIME'] }} | {{ $existingEvent['SCH_PCE_END_DATETIME'] }} |
@endforeach
@endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent



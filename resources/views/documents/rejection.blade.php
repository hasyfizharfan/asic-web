@extends('documents.layout')
@section('content')
<h1>Rejection Letter</h1>
<div class="ref">Ref. No: {{ $nomor }}</div>

<p>Dear {{ $name }},<br>{{ $institution }}@if(!empty($country)), {{ $country }}@endif</p>

<p>Following the review process conducted by our review team, we regret to inform you that your abstract, entitled:</p>
<div class="title">“{{ $abstract_title }}”</div>
<p>has not been accepted for either an oral or a poster presentation at the {{ $event_name }}.</p>
<p>The review team determined that the focus of your abstract falls outside the thematic scope and subject areas of the
    conference. Therefore, we are unable to include your submission in the scientific program.</p>
<p>We sincerely appreciate your interest in participating in the {{ $event_name }} and the time and effort you devoted to
    preparing your submission.</p>
@endsection

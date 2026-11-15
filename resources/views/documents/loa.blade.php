@extends('documents.layout')
@php
    $mode = ($attendance_mode ?? '') === 'Daring' ? 'online' : 'offline';
    $venue = trim(($event_location ?? '').($event_dates ? ', '.$event_dates : ''), ', ');
    $theme = !empty($event_theme) ? ' with the theme: “'.$event_theme.'”' : '';
@endphp
@section('content')
<h1>Letter of Acceptance</h1>
<div class="ref">Ref. No: {{ $nomor }}</div>

<p>Dear {{ $name }},<br>{{ $institution }}@if(!empty($country)), {{ $country }}@endif</p>

@if($is_presenter)
    <p>Based on the review process performed by our review team, we are delighted to inform you that your abstract, entitled:</p>
    <div class="title">“{{ $abstract_title }}”</div>
    <p>is accepted to be presented {{ ($presentation_method ?? '') === 'Poster' ? 'as a poster' : 'orally' }} – {{ $mode }}
        at the {{ $event_name }}{{ $theme }}.@if($venue) The conference will be held at {{ $venue }} (Hybrid).@endif</p>
    @if(!empty($publication_option) && $publication_option !== 'Tidak')
        <p>We would like to convey the following additional information for you who will publish the full paper in the
            proceeding/journal proposed by the committee: your full paper will be considered to be published in the
            {{ $publication_option === 'Prosiding' ? 'proceeding' : 'journal' }} (reviewer decision). The final decision will be based on
            the recommendation from the editors. The full paper should be formatted according to the guidelines provided by the
            journal/proceeding. We recommend that your article include the institutional email and ORCID ID number.</p>
    @endif
@else
    <p>We are delighted to inform you that your registration is accepted to be a Listener – {{ $mode }}
        at the {{ $event_name }}{{ $theme }}.@if($venue) The conference will be held at {{ $venue }} (Hybrid).@endif</p>
@endif

@if(!empty($invoice_nomor))
    <p>Please make a payment according to the attached invoice (No. {{ $invoice_nomor }})
        @if(!empty($invoice_due)) before {{ \Carbon\Carbon::parse($invoice_due)->format('d F Y') }} (23.59 WIB)@endif through:</p>
    @include('documents._payment')
    <p>Please upload the proof of payment through the conference website. The receipt will be issued after being verified by the treasurer.</p>
@endif

<p>Your interest in {{ $event_name }} is very much appreciated. We look forward to meeting you at the hybrid conference.</p>
@endsection

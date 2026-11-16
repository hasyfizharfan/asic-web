@extends('layouts.portal', ['role' => 'admin', 'roleName' => __('asic-nav.public_content'), 'nav' => $adminNav ?? null, 'title' => __('asic-nav.public_content')])

@section('content')
@include('content.manager')
@endsection

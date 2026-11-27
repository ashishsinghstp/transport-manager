@extends('layouts.app')

@section('title', 'Add Driver — Transport Manager')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-6 text-2xl font-semibold">Add Driver</h1>
        <div class="rounded border border-gray-200 bg-white p-6 shadow">
            @include('drivers._form', ['action' => route('drivers.store'), 'method' => 'POST', 'driver' => null])
        </div>
    </div>
@endsection

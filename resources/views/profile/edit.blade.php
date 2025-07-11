@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h2>Perfil</h2>

        @include('profile.partials.update-profile-information-form')
        @include('profile.partials.update-password-form')
        @include('profile.partials.delete-user-form')
    </div>
@endsection
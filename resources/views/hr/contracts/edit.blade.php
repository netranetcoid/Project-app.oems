@extends('layouts.contentNavbarLayout')

@section('title','Edit Kontrak Pegawai')

@section('content')

<div class="container-fluid">

    <form
        method="POST"
        action="{{ route('hr.contracts.update',$contract) }}">

        @method('PUT')

        <div class="card">

            <div class="card-header">

                <h4>

                    Edit Kontrak

                </h4>

            </div>

            <div class="card-body">

                @include('hr.contracts._form')

            </div>

        </div>

    </form>

</div>

@endsection
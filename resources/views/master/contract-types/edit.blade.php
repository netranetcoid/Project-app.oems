@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Jenis Kontrak')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <h4 class="mb-0">

                <i class="ti ti-edit me-2"></i>

                Edit Jenis Kontrak

            </h4>

        </div>

        <div class="card-body">

            <form
                method="POST"
                action="{{ route('master.contract-types.update',$contractType) }}">

                @csrf
                @method('PUT')

                @include('master.contract-types._form')

            </form>

        </div>

    </div>

</div>

@endsection
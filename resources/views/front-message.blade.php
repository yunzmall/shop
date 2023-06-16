@extends('layouts.base')

@section('content')
    <script>
        $(function () {
            alert('{!! $message !!}')
        })
    </script>
@endsection

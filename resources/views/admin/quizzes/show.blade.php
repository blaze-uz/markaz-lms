@extends('layouts.master')
@section('title')
@lang("translation.quizzes.show")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:quiz.quiz-show :item="$item" />
@endsection
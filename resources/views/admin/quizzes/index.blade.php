@extends('layouts.master')
@section('title')
@lang('translation.quizzes.quizzes')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:quiz.quiz-table />
@endsection
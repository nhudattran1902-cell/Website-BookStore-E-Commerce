@extends('layouts.app')

@section('title', 'Bookworm - E-commerce')

@section('content')
    @include('home.hero')
    @include('home.categories')
    @include('home.bestselling-books')
    @include('home.featured-books')
    @include('home.deals')
    @include('home.authors')
    @include('home.newsletter')
@endsection
@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    <style>
        /* Global SaaS Overrides for AdminLTE Sidebar & Navbar */
        .main-sidebar {
            box-shadow: 4px 0 10px rgba(0,0,0,0.02) !important;
            border-right: 1px solid #f3f4f6 !important;
        }
        .main-sidebar, .main-sidebar::before { background-color: #ffffff !important; }
        .nav-sidebar .nav-item > .nav-link {
            border-radius: 8px !important;
            margin: 0 0.8rem 0.2rem 0.8rem !important;
            color: #4b5563 !important;
            font-weight: 500 !important;
            transition: all 0.2s ease;
        }
        .nav-sidebar .nav-item > .nav-link:hover {
            background-color: #f8fafc !important;
            color: #111827 !important;
        }
        .nav-sidebar .nav-item > .nav-link.active {
            background-color: #111827 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important;
        }
        .brand-link {
            border-bottom: 1px solid #f3f4f6 !important;
            color: #111827 !important;
            background-color: #ffffff !important;
        }
        .brand-text { font-weight: 800 !important; letter-spacing: -0.02em; }
        
        .main-header.navbar {
            border-bottom: 1px solid #f3f4f6 !important;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.02) !important;
        }
    </style>
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop

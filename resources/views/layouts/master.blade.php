<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    {!! csrf_field() !!}
    <title>@yield('title') - Workflow Automation</title>

    <!-- CSS -->
    <link rel="stylesheet"
          href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet"
          href="//cdnjs.cloudflare.com/ajax/libs/authy-forms.css/2.2/form.authy.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/scaffolds.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vacation_properties.css') }}">
    <link rel="stylesheet" href="{{ asset('css/application.css') }}">

    @yield('css')
  </head>
  <body class=@yield('body_class')>
    @yield('nav_bg')
    <!-- Nav Bar -->
    <nav class="navbar navbar-transparent">
      <a class="navbar-brand" href="/">airtng</a>
      <ul class="navbar-nav navbar-right pull-right">
        @if (Auth::check())
          <li><image src="{{ asset('images/spock.png') }}"></li>
          <li><a href="{{ route('property-new') }}">New Vacation property</a></li>
          <li><a href="{{ route('logout') }}">Log Out</a></li>
        @else
          <li><a href="{{ route('user-new') }}">Sign Up</a></li>
          <li><a href="{{ route('login-index') }}">Log In</a></li>
        @endif

      </ul>
    </nav>

    @yield('hero')
    <section id="main" class="push-nav">
        @include('_messages')
        @yield('content')
    </section>

    <footer>
        Made with <i class="fa fa-heart"></i> by your pals
        <a href="http://www.twilio.com">@twilio</a>
    </footer>
    <!-- JavaScript -->
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/authy-forms.js/2.2/form.authy.min.js"></script>

    @yield('javascript')
  </body>
</html>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      {{-- Meta, title, CSS, favicons, etc. --}}
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="{{ csrf_token() }}" />

      <title>
          @section('title')
          @show
      </title>

      {{--}}<link rel="stylesheet" href="{{ asset('css/app.css') }}"/>{{--}}
      <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.css') }}"/>
      <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-grid.css') }}"/>
      <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-reboot.css') }}"/>
      <link rel="stylesheet" type="text/css" href="{{ asset('datatables/css/datatables.css') }}"/>
      <link rel="stylesheet" type="text/css" href="{{ asset('datatables/css/dataTables.bootstrap4.css') }}"/>
      <link rel="stylesheet" type="text/css" href="{{ asset('datatables/css/colReorder.bootstrap4.css') }}"/>
      <link rel="stylesheet" type="text/css" href="{{ asset('datatables/css/buttons.bootstrap4.css') }}"/>
      <link rel="stylesheet" type="text/css" href="{{ asset('fontawesome-free-5.12.0-web/css/fontawesome.css') }}"/>
      <link rel="stylesheet" type="text/css" href="{{ asset('fontawesome-free-5.12.0-web/css/solid.css') }}"/>

      <style>
          body {
              padding-top: 65px;
          }
          @media (max-width: 980px) {
              body {
                  padding-top: 0;
              }
          }
          .dataTables_wrapper .dataTables_processing {
              width: 70px !important;
              padding: 10px !important;
              background: #F5F8FA !important;
              border: 1px solid black;
              border-radius: 3px !important;
              font-size: xx-large !important;
              opacity : 1 !important;
              text-decoration: none;
          }
      </style>

    </head>

    <body>

    {{-- top navigation done with bootstrap --}}
    <nav class="navbar navbar-expand-md fixed-top navbar-dark bg-dark">
        {{--}}<a class="navbar-brand" href="#"><img src="{{ asset('images/wcf1.jpg') }}" class="rounded" width="30" height="30" alt=""></a>{{--}}
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav mr-auto">

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/') }}">Home</a>
                </li>
                {{--}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('accruals') }}">Accruals</a>
                </li>
                {{--}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('overtimelist') }}">Overtime List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('overtimelistdetails') }}">Overtime List Details</a>
                </li>
                {{-- add dropdown to links
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="locationsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Location</a>
                    <div class="dropdown-menu text-light bg-dark" aria-labelledby="locationsDropdown">
                        <a class="dropdown-item text-light bg-dark" href="{{ url('locations') }}">Lookup</a>
                        <a class="dropdown-item text-light bg-dark" href="#">Open</a>
                        <a class="dropdown-item text-light bg-dark" href="#">Open</a>
                    </div>
                </li>

                --}}
            </ul>

            {{-- No auth needed at this time
            <ul class="nav navbar-nav justify-content-end">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
            --}}
        </div>
    </nav>{{-- /top navigation --}}


            {{-- page content --}}
        <main>
             @yield('content')
        </main>
            {{-- /page content --}}

            {{-- footer content --}}
          <footer>
              <div class="clearfix"></div>
          </footer>
            {{-- /footer content --}}


    {{-- scripts loaded at end so page loads faster most not needed --}}


   {{--}}<script src="{{ asset('js/app.js') }}"></script>{{--}}
    <script src="{{ asset('js/jquery-3.4.1.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.js') }}"></script>
    <script src="{{ asset('js/bootstrap.js') }}"></script>
    <script src="{{ asset('datatables/js/datatables.js') }}"></script>
    {{--}}<script src="{{ asset('datatables3/js/dataTables.buttons.js') }}"></script>{{--}}
    {{--}}<script src="{{ asset('datatables3/js/dataTables.bootstrap4.js') }}"></script>{{--}}
    <script src="{{ asset('datatables/js/buttons.print.js') }}"></script>
    <script src="{{ asset('datatables/js/buttons.html5.js') }}"></script>
    <script src="{{ asset('datatables/js/buttons.colVis.js') }}"></script>
    <script src="{{ asset('fontawesome-free-5.12.0-web/js/fontawesome.js') }}"></script>
    <script src="{{ asset('fontawesome-free-5.12.0-web/js/solid.js') }}"></script>


    <script>
        window.FontAwesomeConfig = { autoReplaceSvg: false }
        $.fn.dataTable.ext.buttons.reload = {
            text: 'Reload',
            action: function ( e, dt, node, config ) {
                dt.ajax.reload();
            }
        };
    </script>
    {{-- recieving page specific scripts --}}
    @stack('scripts')

  </body>
</html>

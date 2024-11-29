<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title')</title>
    <meta name="_token" content="{{csrf_token()}}">
    <link rel="shortcut icon" href="{{asset('storage/app/public/favicon')}}/{{Helpers::get_business_settings('favicon') ?? null}}"/>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/theme.minc619.css?v=1.0')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/style.css')}}">

    <link rel="stylesheet" href="{{asset('public/assets/admin/css/custom-helper.css')}}">
    <script src="{{asset('public/assets/admin/js/fontawesome.js')}}"></script>


    @stack('css_or_js')
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1/dist/cleave.min.js"></script>


    <script src="{{asset('public/assets/admin')}}/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js"></script>
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/toastr.css')}}">
</head>

<body class="footer-offset">

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="loading" class="d-none">
                <div class="loader-css">
                    <img width="200" src="{{asset('public/assets/admin/img/loader.gif')}}" alt="{{ translate('loader') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client Search Modal -->
<div class="modal fade" id="clientSearchModal" tabindex="-1" role="dialog" aria-labelledby="clientSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ translate('Search Clients') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" id="clientSearchInput" class="form-control" placeholder="{{ translate('Type to search clients...') }}" autocomplete="off">
                <div id="clientSearchResults" class="list-group mt-3"></div>
            </div>
        </div>
    </div>
</div>



@include('layouts.admin.partials._front-settings')

@include('layouts.admin.partials._header')
@include('layouts.admin.partials._sidebar')

<main id="content" role="main" class="main pointer-event">
@yield('content')

@include('layouts.admin.partials._footer')

</main>

<script src="{{asset('public/assets/admin/js/custom.js')}}"></script>

@stack('script')
<script src="{{asset('public/assets/admin/js/vendor.min.js')}}"></script>
<script src="{{asset('public/assets/admin/js/theme.min.js')}}"></script>
<script src="{{asset('public/assets/admin/js/sweet_alert.js')}}"></script>
<script src="{{asset('public/assets/admin/js/toastr.js')}}"></script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
    let searchTimeout;

    // Shortcut Listener
    document.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.key.toLowerCase() === 'k') {
            event.preventDefault(); // Prevent default browser behavior
            $('#clientSearchModal').modal('show');
            setTimeout(() => document.getElementById('clientSearchInput').focus(), 500); // Focus on input after modal opens
        }
    });

    // Debounced Search Function
    function performSearch(query) {
        $.ajax({
            url: '{{ route("admin.clients.search") }}',
            method: 'GET',
            data: { q: query },
            success: function (data) {
                let resultsDiv = document.getElementById('clientSearchResults');
                resultsDiv.innerHTML = ''; // Clear previous results

                if (data.clients.length) {
                    data.clients.forEach(client => {
                        let link = document.createElement('a');
                        link.href = `/admin/clients/${client.id}`;
                        link.className = 'list-group-item list-group-item-action';

                        // Format the credit balance
                        let formattedBalance = new Intl.NumberFormat().format(client.credit_balance);

                        // Set the display text
                        link.textContent = `${client.name} - {{ translate('Credit Balance') }}: ${formattedBalance}`;

                        resultsDiv.appendChild(link);
                    });
                } else {
                    resultsDiv.innerHTML = `<p class="text-muted">{{ translate('No clients found') }}</p>`;
                }
            },
            error: function () {
                document.getElementById('clientSearchResults').innerHTML = `<p class="text-danger">{{ translate('An error occurred while searching.') }}</p>`;
            }
        });
    }

    // Input Event with Debouncing
    document.getElementById('clientSearchInput').addEventListener('input', function (event) {
        let query = event.target.value.trim();

        clearTimeout(searchTimeout); // Clear previous timeout

        if (query.length > 2) { // Start searching after 3 characters
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300); // Wait for 300ms after user stops typing
        } else {
            // Clear results if query is too short
            document.getElementById('clientSearchResults').innerHTML = '';
        }
    });

    // Clear search input and results when modal is closed
    $('#clientSearchModal').on('hidden.bs.modal', function () {
        document.getElementById('clientSearchInput').value = '';
        document.getElementById('clientSearchResults').innerHTML = '';
    });
});

    </script>
    
<script>
    $(window).on('load', function() {
        if ($(".navbar-vertical-content li.active").length) {
            $('.navbar-vertical-content').animate({
                scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
            }, 10);
        }
    });

   // Scroll to active menu item
            if ($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 300);
            }


    var $rows = $('.navbar-vertical-content .navbar-nav > li');
    $('#search-sidebar-menu').keyup(function() {
        var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

        $rows.show().filter(function() {
            var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
            return !~text.indexOf(val);
        }).hide();
    });
</script>

<script
{{-- <script>
        $(document).ready(function() {
            // Scroll to active menu item
            if ($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 300);
            }

            // Sidebar Search Functionality
            var $rows = $('.navbar-vertical-content .navbar-nav > li');
            $('#search-sidebar-menu').keyup(function() {
                var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

                $rows.show().filter(function() {
                    var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                    return !~text.indexOf(val);
                }).hide();
            });

            // Initialize Bootstrap's Collapse for Submenus
            $('.nav-link[data-toggle="collapse"]').on('click', function() {
                var target = $(this).attr('href');

                // Close other open submenus
                $('.collapse.show').not(target).collapse('hide');
            });
        });
    </script> --}}
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif
<script>
    $(document).on('ready', function () {

        $('.admin-logout-btn').on('click', function (e) {
            e.preventDefault();
            logOut();
        });

        function logOut(){
            Swal.fire({
                title: '{{translate('Do you want to logout?')}}',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonColor: '#014F5B',
                cancelButtonColor: '#363636',
                confirmButtonText: `Yes`,
                denyButtonText: `Don't Logout`,
            }).then((result) => {
                if (result.value) {
                    location.href='{{route('admin.auth.logout')}}';
                } else{
                    Swal.fire('Canceled', '', 'info')
                }
            })
        }

        if (window.localStorage.getItem('hs-builder-popover') === null) {
            $('#builderPopover').popover('show')
                .on('shown.bs.popover', function () {
                    $('.popover').last().addClass('popover-dark')
                });

            $(document).on('click', '#closeBuilderPopover', function () {
                window.localStorage.setItem('hs-builder-popover', true);
                $('#builderPopover').popover('dispose');
            });
        } else {
            $('#builderPopover').on('show.bs.popover', function () {
                return false
            });
        }

        $('.js-navbar-vertical-aside-toggle-invoker').click(function () {
            $('.js-navbar-vertical-aside-toggle-invoker i').tooltip('hide');
        });

        var megaMenu = new HSMegaMenu($('.js-mega-menu'), {
            desktop: {
                position: 'left'
            }
        }).init();

        var sidebar = $('.js-navbar-vertical-aside').hsSideNav();


        $('.js-nav-tooltip-link').tooltip({boundary: 'window'})

        $(".js-nav-tooltip-link").on("show.bs.tooltip", function (e) {
            if (!$("body").hasClass("navbar-vertical-aside-mini-mode")) {
                return false;
            }
        });

        $('.js-hs-unfold-invoker').each(function () {
            var unfold = new HSUnfold($(this)).init();
        });

        $('.js-form-search').each(function () {
            new HSFormSearch($(this)).init()
        });


        $('.js-select2-custom').each(function () {
            var select2 = $.HSCore.components.HSSelect2.init($(this));
        });


        $('.js-daterangepicker').daterangepicker();

        $('.js-daterangepicker-times').daterangepicker({
            timePicker: true,
            startDate: moment().startOf('hour'),
            endDate: moment().startOf('hour').add(32, 'hour'),
            locale: {
                format: 'M/DD hh:mm A'
            }
        });

        var start = moment();
        var end = moment();

        function cb(start, end) {
            $('#js-daterangepicker-predefined .js-daterangepicker-predefined-preview').html(start.format('MMM D') + ' - ' + end.format('MMM D, YYYY'));
        }

        $('#js-daterangepicker-predefined').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);

        $('.js-clipboard').each(function () {
            var clipboard = $.HSCore.components.HSClipboard.init(this);
        });
    });

    $('.form-alert').on('click', function (){
        let id = $(this).data('id');
        let message = $(this).data('message');
        form_alert(id, message)
    });

    function form_alert(id, message) {
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#014F5B',
            cancelButtonText: 'No',
            confirmButtonText: 'Yes',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $('#'+id).submit()
            }
        })
    }

    $('.change-status').on('click', function (){
        location.href = $(this).data('route');
    });

    $(document).on('ready', function () {
        $('.js-toggle-password').each(function () {
            new HSTogglePassword(this).init()
        });

        $('.js-validate').each(function () {
            $.HSCore.components.HSValidation.init($(this));
        });
    });
</script>

@stack('script_2')
<audio id="myAudio">
    <source src="{{asset('public/assets/admin/sound/notification.mp3')}}" type="audio/mpeg">
</audio>

<script>
    var audio = document.getElementById("myAudio");

    function playAudio() {
        audio.play();
    }

    function pauseAudio() {
        audio.pause();
    }

    function call_demo() {
        toastr.info('This option is disabled for demo!', {
            CloseButton: true,
            ProgressBar: true
        });
    }
    $('.demo-form-submit').click(function() {
        if ('{{ env('APP_MODE') }}' == 'demo') {
            call_demo();
        }
    });
</script>

<script>
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('public/assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
</script>

<script>

    var initialImages = [];
    $(window).on('load', function() {
        $("form").find('img').each(function (index, value) {
            initialImages.push(value.src);
        })
    })

    $(document).ready(function() {
        $('form').on('reset', function(e) {
        $("form").find('img').each(function (index, value) {
            $(value).attr('src', initialImages[index]);
        })
        })
    });
</script>
</body>
</html>

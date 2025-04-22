<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title')</title>
    <meta name="_token" content="{{csrf_token()}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{asset('storage/app/public/favicon')}}/{{Helpers::get_business_settings('favicon') ?? null}}"/>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/theme.minc619.css?v=1.0')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/style.css')}}">

    <link rel="stylesheet" href="{{asset('public/assets/admin/css/custom-helper.css')}}">
    <script src="{{asset('public/assets/admin/js/fontawesome.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    @stack('css_or_js')
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1/dist/cleave.min.js"></script>


    <script src="{{asset('public/assets/admin')}}/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js"></script>
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/toastr.css')}}">

    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}"></script>

<!-- Custom CSS for hover effects -->
<style>
    .navbar-vertical-footer .js-hs-unfold-invoker {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .navbar-vertical-footer .js-hs-unfold-invoker:hover,
    .navbar-vertical-footer .js-hs-unfold-invoker.active {
        background-color: rgba(255, 255, 255, 0.1);
        border-left: 3px solidrgb(0, 88, 52) !important;
    }
    
    .dropdown-item {
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        background-color: rgba(52, 152, 219, 0.1);
    }
    
    /* Animation for the chevron icon */
    .user-dropdown-indicator {
        transition: transform 0.3s ease;
    }
    
    .hs-unfold.show .user-dropdown-indicator {
        transform: rotate(90deg);
    }
    
    /* Ensure dropdown stays open when hovering */
    #accountDropdown {
        padding: 0;
        margin-top: 0;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    /* Adding delay to prevent accidentally closing */
    .hs-unfold-content.fadeOut {
        animation-delay: 0.2s;
    }
</style>



<style>
/* System Status Indicator Styling */
.system-status-indicator {
    display: inline-block;
}

/* .sy  */
.system-status-btn:hover {
    opacity: 0.9;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 50px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.status-badge.online {
    background-color: rgba(46, 204, 113, 0.2);
    color: #fff;
    border: 1px solid rgba(46, 204, 113, 0.3);
}

.status-badge.offline {
    background-color: rgba(231, 76, 60, 0.2);
    color: #fff;
    border: 1px solid rgba(231, 76, 60, 0.3);
    animation: pulse-red 1.5s infinite;
}

@keyframes pulse-red {
    0% {
        box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(231, 76, 60, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(231, 76, 60, 0);
    }
}

/* Hover effects */
.status-badge:hover {
    transform: translateY(-2px);
}

.status-badge.online:hover {
    background-color: rgba(46, 204, 113, 0.3);
}

.status-badge.offline:hover {
    background-color: rgba(231, 76, 60, 0.3);
}
</style>

<style>
.legend-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.legend-indicator.bg-primary {
    background-color: rgba(52, 152, 219, 0.7) !important;
}

.legend-indicator.bg-success {
    background-color: rgba(46, 204, 113, 0.7) !important;
}

.legend-indicator.bg-warning {
    background-color: rgba(255, 159, 64, 0.7) !important;
}

.chart-legend {
    font-size: 0.775rem;
}

.dropdown-item.active {
    background-color: rgba(1, 79, 91, 0.1);
    color: #014F5B;
}

.form-switch .form-check-input:checked {
    background-color: #014F5B;
    border-color: #014F5B;
}

.form-switch .form-check-input:focus {
    box-shadow: 0 0 0 0.25rem rgba(1, 79, 91, 0.25);
}
</style>
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
                        <div id="clientSearchResults" class="list-group mt-3">
                            
                        </div>
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
 <!-- Fullscreen Script -->
 <script>
    document.addEventListener('DOMContentLoaded', function() {
      const fsButton = document.getElementById('fullscreen-btn');
      if (fsButton) {
        fsButton.addEventListener('click', function(e) {
          e.preventDefault();
          const docEl = document.documentElement;
          if (docEl.requestFullscreen) {
            docEl.requestFullscreen();
          } else if (docEl.mozRequestFullScreen) {
            docEl.mozRequestFullScreen();
          } else if (docEl.webkitRequestFullscreen) {
            docEl.webkitRequestFullscreen();
          } else if (docEl.msRequestFullscreen) {
            docEl.msRequestFullscreen();
          }
        });
      }
    });
  </script>

<script src="{{asset('public/assets/admin/js/custom.js')}}"></script>

@stack('script')
<script src="{{asset('public/assets/admin/js/vendor.min.js')}}"></script>
<script src="{{asset('public/assets/admin/js/theme.min.js')}}"></script>
<script src="{{asset('public/assets/admin/js/sweet_alert.js')}}"></script>
<script src="{{asset('public/assets/admin/js/toastr.js')}}"></script>


<!-- jQuery and Bootstrap JS (Example) -->
    {{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> --}}



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

<script>
// System Status Detection and Update
document.addEventListener('DOMContentLoaded', function() {
    const statusIndicator = document.getElementById('systemStatusIndicator');
    const onlineBadge = statusIndicator.querySelector('.status-badge.online');
    const offlineBadge = statusIndicator.querySelector('.status-badge.offline');
    
    // Function to update status display
    function updateConnectionStatus() {
        if (navigator.onLine) {
            onlineBadge.style.display = 'inline-flex';
            offlineBadge.style.display = 'none';
        } else {
            onlineBadge.style.display = 'none';
            offlineBadge.style.display = 'inline-flex';
        }
    }
    
    // Initial status check
    updateConnectionStatus();
    
    // Listen for connection changes
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    
    // Additional connection testing (for more accurate detection)
    setInterval(function() {
        // Simple ping test to check real connectivity
        fetch('/ping', { 
            method: 'GET',
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => {
            // If we get a response, we're definitely online
            if (response.ok && !navigator.onLine) {
                // Navigator might be wrong, force update
                window.dispatchEvent(new Event('online'));
            }
        })
        .catch(error => {
            // If fetch fails and navigator says we're online, we might be offline
            if (navigator.onLine) {
                // Test failed but browser thinks we're online
                // Let's do a secondary check before showing offline
                const timestamp = new Date().getTime();
                const testImg = new Image();
                testImg.onload = function() {
                    // Image loaded, we're online
                };
                testImg.onerror = function() {
                    // Image failed to load, we're offline
                    window.dispatchEvent(new Event('offline'));
                };
                // Try to load a tiny image with cache busting
                testImg.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=?' + timestamp;
            }
        });
    }, 30000); // Check every 30 seconds
    
    // Optional: Add tooltip with more info
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        new bootstrap.Tooltip(statusIndicator, {
            title: 'Shows the current system connection status',
            placement: 'bottom'
        });
    }
});
</script>

<!-- Add this to existing script_2 (don't replace current content) -->
<script>
$(document).ready(function() {
    // Chart period selector
    $('#chartPeriodDropdown').next('.dropdown-menu').find('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        const period = $(this).data('period');
        const periodText = $(this).text();
        
        // Update dropdown button text
        $('#chartPeriodDropdown').text(periodText);
        
        // Set all items as not active
        $(this).parent().find('.dropdown-item').removeClass('active');
        
        // Set clicked item as active
        $(this).addClass('active');
        
        // You would typically load new data here based on the selected period
        // For demonstration, we'll just show a loading state and reuse the same chart
        
        const $chartArea = $('#monthlyLoanChart').parent();
        $chartArea.addClass('opacity-50');
        
        setTimeout(() => {
            // In a real application, you would fetch new data and update the chart
            $chartArea.removeClass('opacity-50');
            
            // Toast notification
            const toast = `<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 5">
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <strong class="me-auto">Chart Updated</strong>
                        <small>just now</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Chart data updated to show ${periodText.toLowerCase()} performance.
                    </div>
                </div>
            </div>`;
            
            $(toast).appendTo('body');
            
            // Auto-hide toast after 3 seconds
            setTimeout(() => {
                $('.toast').toast('hide');
                setTimeout(() => {
                    $('.position-fixed.bottom-0.end-0.p-3').remove();
                }, 500);
            }, 3000);
            
        }, 800);
    });
});
</script>

<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/11.4.0/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.4.0/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyB69FGalbnIiQhtTsuaGPkCODLcKut2xAY",
    authDomain: "sanaaos.firebaseapp.com",
    projectId: "sanaaos",
    storageBucket: "sanaaos.firebasestorage.app",
    messagingSenderId: "957614256068",
    appId: "1:957614256068:web:0f801e646284f29a707e91",
    measurementId: "G-GRDX5S3V1B"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
</script>
</body>
</html>

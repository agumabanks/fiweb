<!-- Polished Header with Clock, Fullscreen Prompt, and Calculator -->
<div id="headerMain" class="d-none">
  <header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered  shadow" style="min-height:60px; background-color: black;">
    <div class="navbar-nav-wrap d-flex justify-content-between align-items-center px-3">

      <!-- Left Section (Toggler + Clock) -->
      <div class="d-flex align-items-center">
        <!-- Toggler Button (for small devices) -->
        <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3 d-xl-none" style="font-size:1.25rem;">
          <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
             data-placement="right" title="Collapse"></i>
          <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
             data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
             data-toggle="tooltip" data-placement="right" title="Expand"></i>
        </button>
        <!-- Clock -->
        <div id="live-clock" class="font-weight-bold text-white" style="font-size:1rem;"></div>
        <div class="text-white" style="opacity: 0.5;">@yield('title')</div>
      </div>

      <!-- Right Section -->
      <div class="navbar-nav-wrap-content-right">
        <ul class="navbar-nav align-items-center flex-row">
          <!-- Calculator Button -->
          <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">
            <a class="topbar-link d-flex align-items-center text-white" href="#" id="calculator-toggle">
              <!-- Calculator Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calculator" viewBox="0 0 16 16">
                <path d="M12 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h8zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4z"/>
                <path d="M4 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-2zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-4z"/>
              </svg>
              <span class="ml-2">Calculator</span>
              <div class="calculator-shortcut-tooltip">Alt+C</div>
            </a>
          </li>

          <!-- Clear Cache Example -->
          <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">
            <a class="topbar-link d-flex align-items-center lang-country-flag text-white" href="{{route('admin.cache.clear')}}">
              <!-- Sample SVG Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                <path d="M7.719,8.911H8.9V10.1H7.719v1.185H6.539V10.1H5.36V8.911h1.18V7.726h1.18ZM5.36,13.652h1.18v1.185H5.36v1.185H4.18V14.837H3V13.652H4.18V12.467H5.36Zm13.626-2.763H10.138V10.3a1.182,1.182,0,0,1,1.18-1.185h2.36V2h1.77V9.111h2.36a1.182,1.182,0,0,1,1.18,1.185ZM18.4,18H16.044a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,14.755,18H12.5a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,11.216,18H8.958a22.825,22.825,0,0,0,1.163-5.926H18.99A19.124,19.124,0,0,1,18.4,18Z" transform="translate(-3 -2)" fill="#717580"/>
              </svg>
            </a>
          </li>

          <!-- Fullscreen Trigger -->
          <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">
            <a class="topbar-link d-flex align-items-center text-white"
                href="#"
                id="fullscreen-btn">
                Enter Fullscreen
            </a>
          </li>

          <!-- All Clients Example -->
          <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">
            <a class="topbar-link d-flex align-items-center lang-country-flag text-white" href="{{route('admin.allclients')}}">
              All Clients
            </a>
          </li>

          <!-- Teller -->
          <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">
            <a class="topbar-link d-flex align-items-center lang-country-flag text-white" href="{{route('admin.teller.index')}}">
              Teller Pay 
            </a>
          </li>

          
          <!-- Profile / Settings -->
          <!-- <li class="nav-item">
            <div class="hs-unfold">
              <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper media align-items-center right-dropdown-icon text-white" href="{{route('admin.settings')}}">
                <div class="media-body pl-0 pr-2 text-right">
                  <span class="card-title h5 d-block mb-0 text-white">{{translate('admin panel')}}</span>
                  <span class="card-text text-white">{{auth('user')->user()->f_name??''}} {{auth('user')->user()->l_name??''}}</span>
                </div>
                <div class="avatar avatar-sm avatar-circle">
                  <img class="avatar-img" src="{{auth('user')->user()->image_fullpath}}" alt="Image Description">
                  <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                </div>
              </a>
            </div>
          </li> -->
        </ul>
      </div>
    </div>
  </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>

<!-- Calculator Modal (Add this right before the closing </body> tag) -->
<div class="modal fade" id="calculatorModal" tabindex="-1" role="dialog" aria-labelledby="calculatorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 300px;">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="calculatorModalLabel">Calculator</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0">
        <div class="calculator">
          <div class="calculator-display bg-light p-3 mb-2">
            <input type="text" id="calcInput" class="form-control form-control-lg text-right" readonly>
          </div>
          <div class="calculator-keys">
            <div class="row mx-0">
              <button class="col-3 btn btn-danger p-3" onclick="clearDisplay()">AC</button>
              <button class="col-3 btn btn-warning p-3" onclick="clearEntry()">CE</button>
              <button class="col-3 btn btn-light p-3" onclick="appendToDisplay('%')">%</button>
              <button class="col-3 btn btn-light p-3" onclick="appendToDisplay('/')">÷</button>
            </div>
            <div class="row mx-0">
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('7')">7</button>
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('8')">8</button>
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('9')">9</button>
              <button class="col-3 btn btn-light p-3" onclick="appendToDisplay('*')">×</button>
            </div>
            <div class="row mx-0">
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('4')">4</button>
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('5')">5</button>
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('6')">6</button>
              <button class="col-3 btn btn-light p-3" onclick="appendToDisplay('-')">-</button>
            </div>
            <div class="row mx-0">
              <button class="col-3 btn btn-light p-3 text-danger" onclick="appendToDisplay('1')">1</button>
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('2')">2</button>
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('3')">3</button>
              <button class="col-3 btn btn-light p-3" onclick="appendToDisplay('+')">+</button>
            </div>
            <div class="row mx-0">
              <button class="col-3 btn btn-light p-3 text-primary" onclick="appendToDisplay('0')">0</button>
              <button class="col-3 btn btn-light p-3" onclick="appendToDisplay('.')">.</button>
              <button class="col-6 btn btn-success p-3" onclick="calculate()">=</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Calculator Floating Button for Mobile -->
<div class="calculator-float-btn d-xl-none">
  <button class="btn btn-primary rounded-circle" id="calculatorFloatBtn">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calculator" viewBox="0 0 16 16">
      <path d="M12 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h8zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4z"/>
      <path d="M4 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-2zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-4z"/>
    </svg>
  </button>
</div>

<!-- Combined Scripts Section (Clock, Fullscreen, and Calculator) -->
<script>
  // CLOCK
  function updateClock() {
    const now = new Date();
    // Format time as HH:MM:SS
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('live-clock').textContent = `${hours}:${minutes}:${seconds}`;
  }

  setInterval(updateClock, 1000);
  window.addEventListener('load', updateClock);

  // FULLSCREEN TRIGGER
  document.addEventListener('DOMContentLoaded', function() {
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    if (fullscreenBtn) {
      fullscreenBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (!document.fullscreenElement) {
          document.documentElement.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable fullscreen: ${err.message}`);
          });
          this.textContent = 'Exit Fullscreen';
        } else {
          if (document.exitFullscreen) {
            document.exitFullscreen();
            this.textContent = 'Enter Fullscreen';
          }
        }
      });
    }
  });
  
  // CALCULATOR
  document.addEventListener('DOMContentLoaded', function() {
    // Toggle calculator with navbar button
    document.getElementById('calculator-toggle').addEventListener('click', function(e) {
      e.preventDefault();
      $('#calculatorModal').modal('show');
    });
    
    // Toggle calculator with floating button
    document.getElementById('calculatorFloatBtn').addEventListener('click', function() {
      $('#calculatorModal').modal('show');
    });
    
    // Set focus to calculator input when modal opens
    $('#calculatorModal').on('shown.bs.modal', function() {
      document.getElementById('calcInput').focus();
    });
    
    // Add keyboard shortcut (Alt+C) to open calculator
    document.addEventListener('keydown', function(e) {
      if (e.altKey && e.key === 'c') {
        $('#calculatorModal').modal('show');
        e.preventDefault();
      }
    });
    
    // Add keyboard support for calculator when open
    document.addEventListener('keydown', function(e) {
      if ($('#calculatorModal').hasClass('show')) {
        const key = e.key;
        
        // Numbers and operators
        if (/[\d.+\-*/%]/.test(key)) {
          appendToDisplay(key);
          e.preventDefault();
        } 
        // Enter key for equals
        else if (key === 'Enter') {
          calculate();
          e.preventDefault();
        }
        // Escape key to close
        else if (key === 'Escape') {
          $('#calculatorModal').modal('hide');
        }
        // Backspace for CE
        else if (key === 'Backspace') {
          clearEntry();
          e.preventDefault();
        }
        // Delete for AC
        else if (key === 'Delete') {
          clearDisplay();
          e.preventDefault();
        }
      }
    });
  });
  
  // Calculator functions
  function appendToDisplay(value) {
    document.getElementById('calcInput').value += value;
  }
  
  function clearDisplay() {
    document.getElementById('calcInput').value = '';
  }
  
  function clearEntry() {
    const currentValue = document.getElementById('calcInput').value;
    document.getElementById('calcInput').value = currentValue.slice(0, -1);
  }
  
  function calculate() {
    try {
      const currentValue = document.getElementById('calcInput').value;
      
      // Handle percentage calculations
      let processedValue = currentValue.replace(/(\d+)%/g, function(match, number) {
        return number / 100;
      });
      
      const result = eval(processedValue);
      document.getElementById('calcInput').value = result;
    } catch (error) {
      document.getElementById('calcInput').value = 'Error';
    }
  }
</script>

<!-- Additional CSS -->
<style>
  /* Navbar Styles */
  .navbar-fixed {
    top: 0;
    left: 0;
    right: 0;
    z-index: 1040;
  }

  .navbar-nav-wrap {
    width: 100%;
  }

  .navbar-nav-wrap-content-right {
    display: flex;
    align-items: center;
  }

  .topbar-link {
    text-decoration: none;
    padding: 0.5rem 0.75rem;
    transition: background-color 0.2s ease;
  }

  .topbar-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
  }

  #live-clock {
    min-width: 80px;
  }
  
  /* Calculator Styles */
  .calculator-keys button {
    border-radius: 0;
    font-weight: bold;
    font-size: 1.2rem;
  }
  
  .calculator-float-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1050;
  }
  
  .calculator-float-btn button {
    width: 50px;
    height: 50px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  }
  
  /* Add keyboard shortcut tooltip */
  .calculator-shortcut-tooltip {
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    background-color: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 10px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.3s;
  }
  
  .topbar-link:hover .calculator-shortcut-tooltip {
    opacity: 1;
  }
</style>
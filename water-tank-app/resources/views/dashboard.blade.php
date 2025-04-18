<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Water Tank Dashboard</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    :root {
      --bg-color: #f4f4f4;
      --text-color: #333;
      --card-bg: #fff;
      --primary: #4CAF50;
      --secondary: #e0e0e0;
    }

    [data-theme="dark"] {
      --bg-color: #121212;
      --text-color: #e0e0e0;
      --card-bg: #1e1e1e;
      --secondary: #2c2c2c;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg-color);
      color: var(--text-color);
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      background: var(--primary);
      color: white;
      flex-wrap: wrap;
    }

    .theme-toggle {
      cursor: pointer;
      font-size: 1.2rem;
      margin-right: 1rem;
    }

    .user-dropdown {
      position: relative;
      display: inline-block;
    }

    .user-dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background: var(--card-bg);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      z-index: 1;
    }

    .user-dropdown:hover .user-dropdown-content {
      display: block;
    }

    .user-dropdown-content a {
      display: block;
      padding: 0.5rem 1rem;
      color: var(--text-color);
      text-decoration: none;
    }

    .container {
      padding: 1rem;
      display: grid;
      gap: 1rem;
      max-width: 1200px;
      margin: auto;
    }

    .status-bar,
    .readings,
    .sensors,
    .toggles,
    .wifi-settings,
    .mode-toggle {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1rem;
    }

    .card {
      background: var(--card-bg);
      padding: 1rem;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 1rem;
      min-height: 70px;
      gap: 10px;
    }

    .card i {
      font-size: 1.3rem;
      color: var(--primary);
      margin-right: 10px;
    }

    .card strong {
      font-weight: 600;
    }

    .toggle {
      appearance: none;
      width: 40px;
      height: 20px;
      border-radius: 10px;
      background: #ccc;
      position: relative;
      cursor: pointer;
      outline: none;
    }

    .toggle::before {
      content: "";
      position: absolute;
      top: 2px;
      left: 2px;
      width: 16px;
      height: 16px;
      background: white;
      border-radius: 50%;
      transition: transform 0.3s;
    }

    .toggle:checked {
      background: var(--primary);
    }

    .toggle:checked::before {
      transform: translateX(20px);
    }

    .wifi-settings input[type="text"],
    .wifi-settings input[type="password"] {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .wifi-settings button,
    .mode-toggle button {
      padding: 0.5rem 1rem;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
      margin-top: 0.5rem;
    }

    .toast {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: var(--primary);
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      opacity: 0;
      transition: opacity 0.4s ease;
      z-index: 1000;
    }

    .toast.show {
      opacity: 1;
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        text-align: center;
      }
    }
  </style>
</head>

<body data-theme="light">
  <header>
    <div>💧 <strong>Water Tank Dashboard</strong></div>
    <div style="display: flex; align-items: center;">
      <div class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-adjust"></i></div>
      <div class="user-dropdown">
        <span id="user-name">{{ Auth::user()->name }}</span>
        <div class="user-dropdown-content">
          <a href="{{ route('profile.edit') }}">Profile</a>
          <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
      </div>
    </div>
  </header>

  <div class="container">
    <!-- Status Bar -->
    <div class="status-bar card">
      <div><i class="fas fa-wifi"></i> <strong id="wifi_ssid">--</strong></div>
      <div><i class="fas fa-sync-alt"></i> Sync: <strong id="last_sync">--</strong></div>
      <div><i class="fas fa-clock"></i> Online: <strong id="last_online">--</strong></div>
      <div><i class="fas fa-network-wired"></i> IP: <strong id="ip_address">--</strong></div>
      <div><i class="fas fa-microchip"></i> MAC: <strong id="mac_address">--</strong></div>
      <div><i class="fas fa-calendar-alt"></i> <strong id="date_time">--</strong></div>
    </div>

    <!-- Readings -->
    <div class="readings">
      <div class="card"><i class="fas fa-water"></i> Water Level: <strong id="water_level">--</strong>%</div>
      <div class="card"><i class="fas fa-ruler"></i> Distance: <strong id="distance_cm">--</strong> cm</div>
      <div class="card"><i class="fas fa-thermometer-half"></i> Temp: <strong id="temp_c">--</strong> °C</div>
      <div class="card"><i class="fas fa-vial"></i> TDS: <strong id="tds_ppm">--</strong> ppm</div>
    </div>

    <!-- Sensor Status -->
    <div class="sensors">
      <div class="card"><i class="fas fa-bell"></i> Buzzer: <strong id="buzzer_status">--</strong></div>
      <div class="card"><i class="fas fa-microchip"></i> Ultrasonic: <strong id="ultrasonic_status">--</strong></div>
      <div class="card"><i class="fas fa-tint"></i> Pump: <strong id="pump_status">--</strong></div>
      <div class="card"><i class="fas fa-thermometer-three-quarters"></i> Temp: <strong id="temp_status">--</strong></div>
      <div class="card"><i class="fas fa-vial"></i> TDS: <strong id="tds_status">--</strong></div>
      <div class="card"><i class="fas fa-tv"></i> OLED: <strong id="oled_status">--</strong></div>
    </div>

    <!-- Toggles -->
    <div class="toggles">
      <label class="card">Pump <input type="checkbox" class="toggle" id="toggle_pump"></label>
      <label class="card">Buzzer <input type="checkbox" class="toggle" id="toggle_buzzer"></label>
      <label class="card">TDS <input type="checkbox" class="toggle" id="toggle_tds"></label>
      <label class="card">Temp <input type="checkbox" class="toggle" id="toggle_temp"></label>
      <label class="card">Ultrasonic <input type="checkbox" class="toggle" id="toggle_ultrasonic"></label>
      <label class="card">OLED <input type="checkbox" class="toggle" id="toggle_oled"></label>
    </div>

    <!-- Mode Toggle -->
    <div class="mode-toggle">
      <div class="card">
        <i class="fas fa-robot"></i> Mode: <strong id="mode_status">--</strong>
        <button onclick="toggleMode()">Switch Mode</button>
      </div>
    </div>

    <!-- Wi-Fi Settings -->
    <div class="wifi-settings">
      <div class="card">
        <label>Wi-Fi SSID:</label>
        <input type="text" id="wifi_ssid_input" placeholder="Enter SSID">
      </div>
      <div class="card">
        <label>Wi-Fi Password:</label>
        <input type="password" id="wifi_pass_input" placeholder="Enter Password">
      </div>
      <div class="card">
        <button onclick="submitWiFi()">Save Wi-Fi Settings</button>
      </div>
    </div>
  </div>

  <!-- Toast Message -->
  <div id="toast" class="toast"></div>

  <script>
    const firebaseUrl = 'https://sabin-water-tank-management-default-rtdb.asia-southeast1.firebasedatabase.app/';

    function toggleTheme() {
      const current = document.body.dataset.theme;
      document.body.dataset.theme = current === 'dark' ? 'light' : 'dark';
    }

    function showToast(message) {
      const toast = document.getElementById('toast');
      toast.innerText = message;
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 5000);
    }

    function updateData() {
      fetch(firebaseUrl + '.json')
        .then(res => res.json())
        .then(data => {
          if (!data) return;
          const { status, readings, control } = data;

          document.getElementById('wifi_ssid').innerText = status?.wifi_ssid || '--';
          document.getElementById('last_online').innerText = status?.last_online || '--';
          document.getElementById('last_sync').innerText = status?.last_sync || '--';
          document.getElementById('ip_address').innerText = status?.ip || '--';
          document.getElementById('mac_address').innerText = status?.mac || '--';
          document.getElementById('date_time').innerText = status?.date_time || '--';

          document.getElementById('water_level').innerText = readings?.water_level ?? '--';
          document.getElementById('distance_cm').innerText = readings?.distance_cm?.toFixed(2) ?? '--';
          document.getElementById('temp_c').innerText = readings?.temp_c?.toFixed(2) ?? '--';
          document.getElementById('tds_ppm').innerText = readings?.tds_ppm?.toFixed(2) ?? '--';

          document.getElementById('buzzer_status').innerText = status?.buzzer || '--';
          document.getElementById('ultrasonic_status').innerText = status?.ultrasonic || '--';
          document.getElementById('pump_status').innerText = status?.pump || '--';
          document.getElementById('temp_status').innerText = status?.temp || '--';
          document.getElementById('tds_status').innerText = status?.tds || '--';
          document.getElementById('oled_status').innerText = status?.oled || '--';

          document.getElementById('toggle_pump').checked = control?.pump === true;
          document.getElementById('toggle_buzzer').checked = control?.buzzer === true;
          document.getElementById('toggle_tds').checked = control?.tds === true;
          document.getElementById('toggle_temp').checked = control?.temp === true;
          document.getElementById('toggle_ultrasonic').checked = control?.ultrasonic === true;
          document.getElementById('toggle_oled').checked = control?.oled === true;

          document.getElementById('mode_status').innerText = control?.mode || '--';
        });
    }

    function toggleMode() {
      const currentMode = document.getElementById('mode_status').innerText.trim();
      const newMode = currentMode === 'AUTO' ? 'MANUAL' : 'AUTO';

      fetch('/switch-mode', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ mode: newMode })
      })
      .then(res => res.json())
      .then(data => {
        if (data.mode) {
          document.getElementById('mode_status').innerText = data.mode;
          showToast(`Mode switched to ${data.mode}`);
        }
      });
    }

    function submitWiFi() {
      const ssid = document.getElementById('wifi_ssid_input').value;
      const password = document.getElementById('wifi_pass_input').value;

      fetch('/update-wifi', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ ssid, password })
      }).then(() => showToast('Wi-Fi credentials updated'));
    }

    document.querySelectorAll('.toggle').forEach(toggle => {
      toggle.addEventListener('change', () => {
        const key = toggle.id.replace('toggle_', '');
        const value = toggle.checked;

        fetch('/toggle', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ key, value })
        }).then(() => {
          showToast(`${key.toUpperCase()} turned ${value ? 'ON' : 'OFF'}`);
        });
      });
    });

    setInterval(updateData, 1000);
    updateData();
  </script>
</body>
</html>

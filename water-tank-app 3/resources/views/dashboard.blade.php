<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Dashboard</title>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <style>
            :root {
                --bg-color: #f4f4f4;
                --text-color: #333;
                --card-bg: #fff;
                --primary: #4CAF50;
                --secondary: #e0e0e0;
                --input-bg: #fafafa;
                --border-color: #ccc;
                --hover-bg: #45a049;
            }

            [data-theme="dark"] {
                --bg-color: #121212;
                --text-color: #e0e0e0;
                --card-bg: #1e1e1e;
                --secondary: #2c2c2c;
                --input-bg: #2a2a2a;
                --border-color: #444;
                --hover-bg: #3d7a3d;
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
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                z-index: 1;
            }

            .user-dropdown:hover .user-dropdown-content {
                display: block;
            }

            .user-dropdown-content a {
                display: block;
                padding: .5rem 1rem;
                color: var(--text-color);
                text-decoration: none;
            }

            .container {
                padding: 1rem;
                max-width: 1200px;
                margin: auto;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            h3 {
                margin-top: 1.5rem;
                font-size: 1.2rem;
                color: var(--text-color);
                border-left: 4px solid var(--primary);
                padding-left: .5rem;
            }

            .card {
                background: var(--card-bg);
                padding: 1rem;
                border-radius: 10px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                display: flex;
                align-items: center;
                justify-content: flex-start;
                /* ← changed */
                font-size: 1rem;
                gap: 10px;
            }

            .card i {
                font-size: 1.3rem;
                color: var(--primary);
            }

            .card strong {
                font-weight: 600;
                margin-left: auto;
                /* ← pushes value to the right */
            }

            /* Override for <label class="card">…<input> so the switch/input sits right */
            label.card {
                justify-content: space-between;
            }

            .toggle {
                appearance: none;
                width: 40px;
                height: 20px;
                border-radius: 10px;
                background: var(--secondary);
                position: relative;
                cursor: pointer;
                transition: background .3s;
            }

            .toggle::before {
                content: "";
                position: absolute;
                top: 2px;
                left: 2px;
                width: 16px;
                height: 16px;
                background: #fff;
                border-radius: 50%;
                transition: transform .3s;
            }

            .toggle:checked {
                background: var(--primary);
            }

            .toggle:checked::before {
                transform: translateX(20px);
            }

            /* Grids for most card sections */
            .esp-status,
            .readings,
            .sensors,
            .toggles {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            @media (min-width: 768px) {

                .esp-status,
                .readings,
                .sensors,
                .toggles {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            /* Control & Wi‑Fi row */
            .mode-wifi {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                align-items: stretch;
            }

            @media (max-width: 767px) {
                .mode-wifi {
                    grid-template-columns: 1fr;
                }
            }

            /* Mode panel */
            .mode-toggle .card {
                flex-direction: column;
                gap: .5rem;
                height: 100%;
                align-content: center;
            }

            .mode-toggle .card button {
                padding: .6rem;
                background: var(--primary);
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background .3s;
            }

            .mode-toggle .card button:hover {
                background: var(--hover-bg);
            }

            /* Wi‑Fi panel */
            .wifi-settings .card {
                flex-direction: column;
                gap: .75rem;
                height: 100%;
            }

            .wifi-settings .card label {
                font-weight: 500;
            }

            .wifi-settings .card input {
                width: 100%;
                background: var(--input-bg);
                border: 1px solid var(--border-color);
                border-radius: 5px;
                padding: .6rem;
                font-size: 1rem;
                transition: border-color .3s, box-shadow .3s;
            }

            .wifi-settings .card input:focus {
                border-color: var(--primary);
                box-shadow: 0 0 3px var(--primary);
                outline: none;
            }

            .wifi-settings .card button {
                padding: .6rem;
                background: var(--primary);
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background .3s;
            }

            .wifi-settings .card button:hover {
                background: var(--hover-bg);
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
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
                opacity: 0;
                transition: opacity .4s ease;
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
            <div><strong>Water Tank Management System Dashboard</strong></div>
            <div style="display: flex; align-items: center;">
                <div class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-adjust"></i></div>
                <div class="user-dropdown">
                    <span>{{ Auth::user()->name }}</span>
                    <div class="user-dropdown-content">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <a href="{{ route('profile.edit') }}">Profile</a>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
                <div class="user-dropdown" style="margin-left: 10px;">
                    <span>Reading History</span>
                    <div class="user-dropdown-content">
                        <a href="{{ route('temperature.history') }}">Temperature History</a>
                        <a href="{{ route('water.history') }}"> Water Level History </a>
                        <a href="{{ route('tds.history') }}">TDS History </a>
                        <a href="{{ route('sensor.history') }}">Sensor History </a>
                        <a href="{{ route('device.history') }}">Device History</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="container">
            <h3>ESP8266 Status</h3>
            <div class="esp-status">
                <div class="card"><i class="fas fa-wifi"></i> Wi‑Fi: <strong id="wifi_ssid">--</strong></div>
                <div class="card"><i class="fas fa-sync-alt"></i> Sync: <strong id="last_sync">--</strong></div>
                <div class="card"><i class="fas fa-clock"></i> Online: <strong id="last_online">--</strong></div>
                <div class="card"><i class="fas fa-network-wired"></i> IP: <strong id="ip_address">--</strong></div>
                <div class="card"><i class="fas fa-microchip"></i> MAC: <strong id="mac_address">--</strong></div>
                <div class="card"><i class="fas fa-calendar-alt"></i> Date: <strong id="date_time">--</strong></div>
            </div>

            <h3>Sensor Readings</h3>
            <div class="readings">
                <div class="card"><i class="fas fa-water"></i> Water Level: <strong id="water_level">--</strong>%
                </div>
                <div class="card"><i class="fas fa-ruler"></i> Distance: <strong id="distance_cm">--</strong> cm</div>
                <div class="card"><i class="fas fa-thermometer-half"></i> Temp: <strong id="temp_c">--</strong>°C
                </div>
                <div class="card"><i class="fas fa-vial"></i> TDS: <strong id="tds_ppm">--</strong> ppm</div>
            </div>

            <h3>Sensor Status</h3>
            <div class="sensors">
                <div class="card"><i class="fas fa-bell"></i> Buzzer: <strong id="buzzer_status">--</strong></div>
                <div class="card"><i class="fas fa-microchip"></i> Ultrasonic: <strong
                        id="ultrasonic_status">--</strong></div>
                <div class="card"><i class="fas fa-tint"></i> Pump: <strong id="pump_status">--</strong></div>
                <div class="card"><i class="fas fa-thermometer-three-quarters"></i> Temp Sensor: <strong
                        id="temp_status">--</strong></div>
                <div class="card"><i class="fas fa-vial"></i> TDS Sensor: <strong id="tds_status">--</strong></div>
                <div class="card"><i class="fas fa-tv"></i> OLED: <strong id="oled_status">--</strong></div>
            </div>

            <h3>Sensor Switches</h3>
            <div class="toggles">
                <label class="card">Pump<input type="checkbox" class="toggle" id="toggle_pump"></label>
                <label class="card">Buzzer<input type="checkbox" class="toggle" id="toggle_buzzer"></label>
                <label class="card">TDS<input type="checkbox" class="toggle" id="toggle_tds"></label>
                <label class="card">Temp<input type="checkbox" class="toggle" id="toggle_temp"></label>
                <label class="card">Ultrasonic<input type="checkbox" class="toggle"
                        id="toggle_ultrasonic"></label>
                <label class="card">OLED<input type="checkbox" class="toggle" id="toggle_oled"></label>
            </div>

            <h3>Control &amp; Wi‑Fi</h3>
            <div class="mode-wifi">
                <div class="mode-toggle">
                    <div class="card">
                        <i class="fas fa-robot" style="margin-top: 10%;"></i>
                        <div>Mode: <strong id="mode_status">--</strong></div>
                        <button onclick="toggleMode()">Switch Mode</button>
                    </div>
                </div>
                <div class="wifi-settings">
                    <div class="card">
                        <label for="wifi_ssid_input">Wi‑Fi SSID:</label>
                        <input type="text" id="wifi_ssid_input" placeholder="Enter SSID">
                        <label for="wifi_pass_input">Wi‑Fi Password:</label>
                        <input type="password" id="wifi_pass_input" placeholder="Enter Password">
                        <button onclick="submitWiFi()">Save Wi‑Fi Settings</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="toast" class="toast"></div>

        <script>
            const firebaseUrl = 'https://sabin-water-tank-management-default-rtdb.asia-southeast1.firebasedatabase.app/';

            function toggleTheme() {
                document.body.dataset.theme =
                    document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            }

            function showToast(msg) {
                const t = document.getElementById('toast');
                t.textContent = msg;
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 4000);
            }

            function updateData() {
                fetch(firebaseUrl + '.json')
                    .then(res => res.json())
                    .then(data => {
                        if (!data) return;
                        const {
                            status,
                            readings,
                            control
                        } = data;

                        // ─── UI UPDATES ────────────────────────────────────────────────────────
                        // Status
                        document.getElementById('wifi_ssid').innerText = status?.wifi_ssid || '--';
                        document.getElementById('last_sync').innerText = status?.last_sync || '--';
                        document.getElementById('last_online').innerText = status?.last_online || '--';
                        document.getElementById('ip_address').innerText = status?.ip || '--';
                        document.getElementById('mac_address').innerText = status?.mac || '--';
                        document.getElementById('date_time').innerText = status?.date_time || '--';

                        // Readings
                        document.getElementById('water_level').innerText = readings?.water_level ?? '--';
                        document.getElementById('distance_cm').innerText = readings?.distance_cm?.toFixed(2) ?? '--';
                        document.getElementById('temp_c').innerText = readings?.temp_c?.toFixed(2) ?? '--';
                        document.getElementById('tds_ppm').innerText = readings?.tds_ppm?.toFixed(2) ?? '--';

                        // Sensor status
                        document.getElementById('buzzer_status').innerText = status?.buzzer || '--';
                        document.getElementById('ultrasonic_status').innerText = status?.ultrasonic || '--';
                        document.getElementById('pump_status').innerText = status?.pump || '--';
                        document.getElementById('temp_status').innerText = status?.temp || '--';
                        document.getElementById('tds_status').innerText = status?.tds || '--';
                        document.getElementById('oled_status').innerText = status?.oled || '--';

                        // Switches
                        document.getElementById('toggle_pump').checked = status?.pump === "ON";
                        document.getElementById('toggle_buzzer').checked = control?.buzzer === true;
                        document.getElementById('toggle_tds').checked = control?.tds === true;
                        document.getElementById('toggle_temp').checked = control?.temp === true;
                        document.getElementById('toggle_ultrasonic').checked = control?.ultrasonic === true;
                        document.getElementById('toggle_oled').checked = control?.oled === true;

                        // Mode
                        document.getElementById('mode_status').innerText = control?.mode === true 
  ? 'AUTO' 
  : control?.mode === false 
    ? 'MANUAL' 
    : '--';

                        // ─── PUSH TO LARAVEL ────────────────────────────────────────────────────
                        fetch('/sensor-readings', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    // readings
                                    distance_cm: readings.distance_cm ?? 0,
                                    tds_ppm: readings.tds_ppm ?? 0,
                                    temp_c: readings.temp_c ?? 0,
                                    water_level: readings.water_level ?? 0,

                                    // timestamps & network
                                    reading_at: status.last_online, // "2025-04-18 13:37:34"
                                    ip: status.ip, // "192.168.1.12"
                                    last_online: status.last_online, // "23:44:06"
                                    last_sync: status.last_sync, // "13:37:34"
                                    mac: status.mac, // "50:02:91:E0:3A:13"

                                    // control flags
                                    mode: status.mode, // "AUTO"
                                    oled_control: control.oled, // true/false
                                    pump_control: control.pump, // true/false
                                    tds_control: control.tds, // true/false
                                    temp_control: control.temp, // true/false
                                    ultrasonic_control: control.ultrasonic, // true/false

                                    // status metadata
                                    status_buzzer: status.buzzer, // "ON"/"OFF"
                                    status_oled: status.oled,
                                    status_pump: status.pump,
                                    status_tds: status.tds,
                                    status_temp: status.temp,
                                    status_ultrasonic: status.ultrasonic,
                                    wifi_ssid: status.wifi_ssid // “HelloWorld”
                                })
                            })
                            .then(res => {
                                if (!res.ok) console.error('Error saving reading:', res.statusText);
                            })
                            .catch(console.error);
                    });
            }


            function toggleMode() {
  const statusEl = document.getElementById('mode_status');
  // current text: "AUTO" means mode=true, "MANUAL" means mode=false
  const isAuto   = statusEl.innerText.trim() === 'AUTO';
  const nextBool = !isAuto;

  fetch('/switch-mode', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ mode: nextBool })
  })
    .then(res => res.json())
    .then(data => {
      if (typeof data.mode === 'boolean') {
        // map boolean back to text
        const newText = data.mode ? 'AUTO' : 'MANUAL';
        statusEl.innerText = newText;
        showToast(`Mode switched to ${newText}`);
      }
    })
    .catch(err => {
      console.error('Failed to switch mode:', err);
      showToast('Error switching mode', 'error');
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
                    body: JSON.stringify({
                        ssid,
                        password
                    })
                }).then(() => showToast('Wi‑Fi settings saved'));
            }

            document.querySelectorAll('.toggle').forEach(toggle => {
                toggle.addEventListener('change', () => {
                    const key = toggle.id.replace('toggle_', '');
                    const value = toggle.checked;
                    // If pump toggle, check current mode first
        if (key === 'pump') {
            const currentMode = document.getElementById('mode_status').innerText.trim();
            
            if (currentMode === 'AUTO') {
                showToast('Cannot toggle pump in AUTO mode');
                // Revert toggle back immediately
                toggle.checked = !value;
                return;
            }
        }
                    fetch('/toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            key,
                            value
                        })
                    }).then(() => showToast(`${key.toUpperCase()} turned ${value ? 'ON' : 'OFF'}`));
                });
            });

            setInterval(updateData, 1000);
            updateData();
        </script>
    </body>

</html>

function toggleTheme() {
    const current = document.body.dataset.theme;
    document.body.dataset.theme = current === 'dark' ? 'light' : 'dark';
  }
  
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
  }
  
  function showToast(message) {
    const toast = document.getElementById('toast');
    toast.innerText = message;
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 5000);
  }
  
  function updateData() {
    fetch('https://sabin-water-tank-management-default-rtdb.asia-southeast1.firebasedatabase.app/.json')
      .then(res => res.json())
      .then(data => {
        if (!data) return;
        const status = data.status || {};
        const readings = data.readings || {};
        const control = data.control || {};
  
        document.getElementById('water_level').innerText = readings.water_level ?? '--';
        document.getElementById('distance_cm').innerText = readings.distance_cm ?? '--';
        document.getElementById('temp_c').innerText = readings.temp_c ?? '--';
        document.getElementById('tds_ppm').innerText = readings.tds_ppm ?? '--';
  
        document.getElementById('buzzer_status').innerText = status.buzzer || '--';
        document.getElementById('ultrasonic_status').innerText = status.ultrasonic || '--';
        document.getElementById('pump_status').innerText = status.pump || '--';
        document.getElementById('temp_status').innerText = status.temp || '--';
        document.getElementById('tds_status').innerText = status.tds || '--';
        document.getElementById('oled_status').innerText = status.oled || '--';
  
        document.getElementById('toggle_pump').checked = control.pump;
        document.getElementById('toggle_buzzer').checked = control.buzzer;
        document.getElementById('toggle_tds').checked = control.tds;
        document.getElementById('toggle_temp').checked = control.temp;
        document.getElementById('toggle_ultrasonic').checked = control.ultrasonic;
        document.getElementById('toggle_oled').checked = control.oled;
  
        document.getElementById('mode_status').innerText = control.mode || '--';
  
        document.getElementById('wifi_ssid').innerText = status.wifi_ssid || '--';
        document.getElementById('last_online').innerText = status.last_online || '--';
        document.getElementById('last_sync').innerText = status.last_sync || '--';
        document.getElementById('ip_address').innerText = status.ip || '--';
        document.getElementById('mac_address').innerText = status.mac || '--';
        document.getElementById('date_time').innerText = status.date_time || '--';
      });
  }
  
  function toggleMode() {
    const mode = document.getElementById('mode_status').innerText.trim() === 'AUTO' ? 'MANUAL' : 'AUTO';
    fetch('/switch-mode', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ mode })
    }).then(res => res.json()).then(data => {
      if (data.mode) {
        document.getElementById('mode_status').innerText = data.mode;
        showToast(`Switched to ${data.mode} mode`);
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
    }).then(() => showToast("Wi-Fi settings updated"));
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
      }).then(() => showToast(`${key.toUpperCase()} is now ${value ? 'ON' : 'OFF'}`));
    });
  });
  
  setInterval(updateData, 1000);
  updateData();
  
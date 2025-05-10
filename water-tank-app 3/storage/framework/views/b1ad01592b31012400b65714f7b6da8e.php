
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>TDS History</title>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>"/>

  <!-- FontAwesome -->
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <!-- DataTables CSS -->
  <link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
  <link rel="stylesheet"
    href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"/>
  <link rel="stylesheet"
    href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css"/>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- jQuery + DataTables + Buttons + Responsive -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

  <style>
    /* Theme variables */
    :root {
      --bg-color: #f4f4f4; --text-color: #333; --card-bg: #fff;
      --primary: #4CAF50; --secondary: #e0e0e0; --input-bg: #fafafa;
      --border-color: #ccc; --hover-bg: #45a049;
    }
    [data-theme="dark"] {
      --bg-color: #121212; --text-color: #e0e0e0; --card-bg: #1e1e1e;
      --secondary: #2c2c2c; --input-bg: #2a2a2a; --border-color: #444;
      --hover-bg: #3d7a3d;
    }
    body {
      margin: 0; font-family: 'Segoe UI', sans-serif;
      background: var(--bg-color); color: var(--text-color);
    }

    /* Navbar */
    header {
      display: flex; justify-content: space-between; align-items: center;
      padding: 1rem 2rem; background: var(--primary); color: #fff; flex-wrap: wrap;
    }
    header strong { font-size: 1.25rem; }
    header > div { display: flex; align-items: center; }
    .theme-toggle { cursor: pointer; margin-right: 1rem; font-size: 1.2rem; }
    .user-dropdown { position: relative; margin-right: 1rem; }
    .user-dropdown-content {
      display: none; position: absolute; right: 0; top: 100%;
      background: var(--card-bg); box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .user-dropdown:hover .user-dropdown-content { display: block; }
    .user-dropdown-content a {
      display: block; padding: .5rem 1rem; color: var(--text-color);
      text-decoration: none;
    }

    /* Container & Title */
    .container { padding: 2rem; max-width: 1100px; margin: auto; }
    h3 {
      margin: 2rem 0 1rem; font-size: 1.3rem;
      border-left: 4px solid var(--primary); padding-left: .5rem;
    }

    /* History card */
    .history-section {
      background: var(--card-bg); padding: 1.5rem;
      border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    /* Filters row */
    .filters {
      display: flex; justify-content: space-between; align-items: center;
      flex-wrap: wrap; margin-bottom: 1rem;
    }
    .filters-left {
      display: flex; align-items: center; gap: 1rem;
    }

    /* Chart */
    .chart-container {
      display: flex; justify-content: center; margin-bottom: 1.5rem;
    }
    .chart-box {
      background: var(--secondary); padding: 1rem; border-radius: 8px;
      width: 100%;
    }

    /* Table margins */
    .history-section .dataTables_wrapper {
      margin-top: 1rem; margin-bottom: 1.5rem;
      margin-top: 20px;
      margin-bottom: 20px;
    }

    /* DataTable styling */
    table.dataTable thead th {
      background: var(--primary) !important; color: #fff !important;
      padding: .75rem .5rem !important; font-weight: 600;
      margin-top: 20px;
      margin-bottom: 20px;
    }
    table.dataTable tbody td {
      padding: .5rem .5rem !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      background: var(--primary); color: #fff; border-radius: 4px;
      padding: .25rem .5rem; margin: 0 2px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
      background: var(--secondary); color: var(--text-color);
    }

    /* Mobile: stack right filters in two rows, centered */
    @media (max-width: 768px) {
      .filters {
        flex-direction: column; align-items: center; gap: .75rem;

      }
      .filters-left,
      .filters-right {
        justify-content: center; width: 100%;
      }
      .filters-right {
        flex-direction: column;
      }
      .filters-right .dataTables_length,
      .filters-right .dataTables_filter {
        width: auto !important;
        display: flex; justify-content: center;
        margin-top: 20px;
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body data-theme="light">
  <header>
    <div><strong>Water Tank Management System</strong></div>
    <div>
      <div class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-adjust"></i></div>
      <div class="user-dropdown">
        <span><?php echo e(Auth::user()->name); ?></span>
        <div class="user-dropdown-content">
            <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
          <a href="<?php echo e(route('profile.edit')); ?>">Profile</a>
          <a href="<?php echo e(route('logout')); ?>"
             onclick="event.preventDefault();
                      document.getElementById('logout-form').submit();">
            Logout
          </a>
          <form id="logout-form" action="<?php echo e(route('logout')); ?>"
                method="POST" style="display:none;"><?php echo csrf_field(); ?></form>
        </div>
      </div>
      <div class="user-dropdown">
        <span>Reading History</span>
        <div class="user-dropdown-content">
          <a href="<?php echo e(route('temperature.history')); ?>">Temperature History</a>
          <a href="<?php echo e(route('water.history')); ?>">Water Level History</a>
          <a href="<?php echo e(route('tds.history')); ?>">TDS History</a>
          <a href="<?php echo e(route('sensor.history')); ?>">Sensor History</a>
          <a href="<?php echo e(route('device.history')); ?>">Device History</a>
        </div>
      </div>
    </div>
  </header>

  <div class="container">
    <h3>TDS History</h3>
    <div class="history-section">
      <div class="filters">
        <div class="filters-left">
          <label>Range:
            <select id="rangeSelect">
              <option value="1">Last 24 Hrs</option>
              <option value="7">Last 7 Days</option>
              <option value="30">Last 30 Days</option>
            </select>
          </label>
        </div>
      </div>

      <div class="chart-container">
        <div class="chart-box">
          <canvas id="tempChart"></canvas>
        </div>
      </div>

      <table id="historyTable" class="display responsive nowrap" style="width:100%">
        <thead style="margin-top:20px;">
          <tr><th>Timestamp</th><th>TDS (ppm)</th></tr>
        </thead>
      </table>
    </div>
  </div>

  <script>
    function toggleTheme(){
      document.body.dataset.theme =
        document.body.dataset.theme==='dark'?'light':'dark';
    }

    const ctx = document.getElementById('tempChart').getContext('2d');
    let chart, table;

    function initChart(data){
      const labels = data.map(d=>d.reading_at);
      const temps  = data.map(d=>d.tds_ppm);
      if(chart) chart.destroy();
      chart = new Chart(ctx,{
        type:'line',
        data:{ labels, datasets:[{
          label:'TDS (ppm)',
          data:temps,
          fill:false,
          pointRadius:2,
          borderWidth:2
        }]},
        options:{
          responsive:true,
          layout:{padding:10},
          scales:{
            x:{ display:true, title:{display:true,text:'Time'} },
            y:{ display:true, title:{display:true,text:'ppm'} }
          },
          plugins:{ legend:{display:false} }
        }
      });
    }

    function initTable(){
      table = $('#historyTable').DataTable({
        responsive:true,
        processing:true,
        serverSide:false,
        ajax:{
          url: '<?php echo e(route("tds.history.data")); ?>',
          dataSrc:'data',
          data: d=>{ d.days = $('#rangeSelect').val(); }
        },
        columns:[
          { data:'reading_at' },
          { data:'tds_ppm' }
        ],
        dom: '<"filters-left"B><"filters-right"lf>rtip',
        buttons:[{
          extend:'excelHtml5',
          title:'TDS History'
        }],
        lengthMenu:[[10,25,50,-1],[10,25,50,'All']],
        pageLength:10,
        order:[[0,'desc']],
        language:{ search:'🔍', searchPlaceholder:'Search...' }
      });

      // Move built‑in length & filter into .filters-right
      $('.filters-right').append($('#historyTable_length'), $('#historyTable_filter'));

      // Wire export button
      $('#exportExcel').on('click', ()=> table.button(0).trigger());

      // Update chart on data load
      table.on('xhr', ()=> initChart(table.ajax.json().data));
    }

    $(function(){
      initTable();
      $('#rangeSelect').on('change', ()=> table.ajax.reload());
    });
  </script>
</body>
</html><?php /**PATH /Users/sameer/Desktop/water-tank-app/resources/views/tds_history.blade.php ENDPATH**/ ?>
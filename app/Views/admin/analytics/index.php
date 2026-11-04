<?php
$pageTitle = 'Analytics';
$GLOBALS['pageTitle'] = $pageTitle;
?>
<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

<div class="flex items-center justify-between mb-8">
  <div><h1 class="text-2xl font-bold text-white">Analytics Dashboard</h1><p class="text-slate-400 text-sm mt-1">Local visitor tracking — privacy-first (IP hashed, no cookies)</p></div>
  <div class="flex gap-2">
    <?php foreach ([7,30,90] as $d): ?>
    <a href="?days=<?= $d ?>" class="px-4 py-1.5 rounded-full text-sm transition <?= $days===$d?'bg-crimson text-white':'bg-slate-700/50 text-slate-300 hover:bg-slate-700' ?>"><?= $d ?>d</a>
    <?php endforeach; ?>
  </div>
</div>

<!-- KPI cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <?php
  $kpis = [
    ['Page Views', number_format($totals['page_views']), 'Last '.$days.' days', '👁'],
    ['Visitors',   number_format($totals['visitors']),   'Unique sessions',     '👤'],
    ['All-time Views', number_format($allTime['page_views']), 'Total recorded', '📊'],
    ['All-time Visitors', number_format($allTime['visitors']), 'Unique sessions', '🌍'],
  ];
  foreach ($kpis as [$label,$val,$sub,$icon]):
  ?>
  <div class="admin-card p-5">
    <div class="text-2xl mb-2"><?= $icon ?></div>
    <div class="text-2xl font-bold text-white"><?= $val ?></div>
    <div class="text-sm text-slate-400 mt-0.5"><?= $label ?></div>
    <div class="text-xs text-slate-600 mt-0.5"><?= $sub ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Page views chart -->
<div class="admin-card p-6 mb-8">
  <div class="flex items-center justify-between mb-5">
    <h2 class="font-semibold text-white">Page Views — Last <?= $days ?> Days</h2>
    <div class="flex gap-2">
      <button onclick="setMode('daily')" id="btn-daily" class="px-3 py-1 text-xs rounded bg-slate-600 text-white">Daily</button>
      <button onclick="setMode('weekly')" id="btn-weekly" class="px-3 py-1 text-xs rounded bg-slate-700 text-slate-400">Weekly</button>
    </div>
  </div>
  <canvas id="views-chart" height="80"></canvas>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
  <!-- Top pages -->
  <div class="lg:col-span-2 admin-card p-6">
    <h2 class="font-semibold text-white mb-5">Top Pages</h2>
    <div class="space-y-3">
      <?php
      $maxViews = max(array_column($topPages,'views') ?: [1]);
      foreach ($topPages as $pg):
        $pct = round($pg['views'] / $maxViews * 100);
      ?>
      <div>
        <div class="flex items-center justify-between text-sm mb-1">
          <span class="text-slate-300 truncate max-w-xs"><?= htmlspecialchars($pg['page_path']) ?></span>
          <span class="text-white font-medium ml-4"><?= number_format($pg['views']) ?></span>
        </div>
        <div class="h-1.5 bg-slate-700 rounded-full"><div class="h-full bg-crimson rounded-full transition-all" style="width:<?= $pct ?>%"></div></div>
      </div>
      <?php endforeach; ?>
      <?php if (!$topPages): ?><p class="text-slate-500 text-sm">No data yet.</p><?php endif; ?>
    </div>
  </div>

  <!-- Devices + referrers -->
  <div class="space-y-6">
    <div class="admin-card p-6">
      <h2 class="font-semibold text-white mb-4">Devices</h2>
      <canvas id="device-chart" height="160"></canvas>
    </div>
    <div class="admin-card p-6">
      <h2 class="font-semibold text-white mb-4">Top Referrers</h2>
      <div class="space-y-2">
        <?php foreach ($referrers as $r): ?>
        <div class="flex items-center justify-between text-sm">
          <span class="text-slate-300 truncate max-w-[180px]"><?= htmlspecialchars(parse_url($r['referrer'],PHP_URL_HOST) ?: $r['referrer']) ?></span>
          <span class="text-white font-medium"><?= $r['cnt'] ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (!$referrers): ?><p class="text-slate-500 text-sm">No referrer data.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
const days = <?= $days ?>;
const chartDefaults = { color:'#94a3b8', borderColor:'#1e293b' };

// Page views line chart
let viewsChart;
function buildViewsChart(data) {
  const ctx = document.getElementById('views-chart').getContext('2d');
  if (viewsChart) viewsChart.destroy();
  viewsChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(d => d.day || d.week),
      datasets: [{
        label: 'Page Views',
        data: data.map(d => d.views),
        borderColor: '#c0392b',
        backgroundColor: 'rgba(192,57,43,0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 3,
        pointBackgroundColor: '#c0392b',
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display:false } },
      scales: {
        x: { grid:{ color:'#1e293b' }, ticks:{ color:'#64748b' } },
        y: { grid:{ color:'#1e293b' }, ticks:{ color:'#64748b' }, beginAtZero:true },
      }
    }
  });
}

// Load daily data on init
fetch(`/admin/analytics/data?days=${days}&mode=daily`)
  .then(r=>r.json()).then(buildViewsChart);

function setMode(mode) {
  document.getElementById('btn-daily').className  = 'px-3 py-1 text-xs rounded ' + (mode==='daily'  ?'bg-slate-600 text-white':'bg-slate-700 text-slate-400');
  document.getElementById('btn-weekly').className = 'px-3 py-1 text-xs rounded ' + (mode==='weekly' ?'bg-slate-600 text-white':'bg-slate-700 text-slate-400');
  fetch(`/admin/analytics/data?days=${days}&mode=${mode}`).then(r=>r.json()).then(buildViewsChart);
}

// Devices donut
<?php
$devLabels = array_column($devices,'device_type');
$devData   = array_column($devices,'cnt');
?>
new Chart(document.getElementById('device-chart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($devLabels) ?>,
    datasets: [{ data: <?= json_encode($devData) ?>, backgroundColor:['#c0392b','#6ee7b7','#7dd3fc'], borderWidth:0 }]
  },
  options: { responsive:true, plugins:{ legend:{ position:'bottom', labels:{ color:'#94a3b8', boxWidth:12 } } } }
});
</script>

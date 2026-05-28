<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Webhook Dashboard - Enhanced</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', sans-serif;
            transition: background 0.3s ease;
        }
        
        .navbar-custom {
            background: rgba(0, 0, 0, 0.8) !important;
            backdrop-filter: blur(10px);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, background 0.3s, color 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: #667eea;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: background 0.3s, color 0.3s;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-processed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn-action {
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 8px;
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: background 0.3s, color 0.3s;
        }
        
        pre {
            max-height: 100px;
            overflow: auto;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            transition: background 0.3s, color 0.3s;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            height: 300px;
            transition: background 0.3s, color 0.3s;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .real-time-badge {
            animation: pulse 2s infinite;
            background: #28a745;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        html.dark body {
            background: #1a202c !important;
            color: #e2e8f0;
        }
        
        html.dark .stat-card, 
        html.dark .table-container, 
        html.dark .filter-section, 
        html.dark .chart-container {
            background: #2d3748;
            color: #e2e8f0;
        }
        
        html.dark .stat-label, 
        html.dark .text-muted {
            color: #a0aec0 !important;
        }
        
        html.dark .table {
            color: #e2e8f0;
        }
        
        html.dark .table-hover tbody tr:hover {
            color: #e2e8f0;
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        html.dark pre {
            background: #1a202c;
            color: #a0aec0;
            border: 1px solid #4a5568;
        }
        
        html.dark .form-control, html.dark .form-select {
            background-color: #1a202c;
            color: #e2e8f0;
            border-color: #4a5568;
        }
    </style>

    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body>
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="bi bi-webhook"></i> Webhook Dashboard
            </span>
            <div class="d-flex">
                <span class="badge bg-info me-2 real-time-badge d-flex align-items-center">
                    <i class="bi bi-broadcast me-1"></i> Real-time Active
                </span>
                <button class="btn btn-outline-light me-2" onclick="toggleDarkMode()">
                    <i class="bi bi-moon-stars"></i> Dark Mode
                </button>
                <button class="btn btn-outline-light" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div class="stat-icon">
                            <i class="bi bi-envelope-paper"></i>
                        </div>
                        <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                    </div>
                    <div class="stat-label">Total Webhooks</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div class="stat-icon">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                        <div class="stat-value">{{ number_format($stats['today'] ?? 0) }}</div>
                    </div>
                    <div class="stat-label">Today's Webhooks</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-value">{{ number_format($stats['failed'] ?? 0) }}</div>
                    </div>
                    <div class="stat-label">Failed Webhooks</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div class="stat-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="stat-value">{{ $stats['success_rate'] ?? 100 }}%</div>
                    </div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="eventsChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="filter-section">
            <h5><i class="bi bi-funnel"></i> Filter Webhooks</h5>
            <form method="GET" class="row g-3 mt-2">
                <div class="col-md-3">
                    <label class="form-label">Event Type</label>
                    <input type="text" name="event" class="form-control" placeholder="e.g., order.created" value="{{ request('event') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Order ID</label>
                    <input type="number" name="order_id" class="form-control" placeholder="Order ID" value="{{ request('order_id') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control" placeholder="Amount" value="{{ request('amount') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('webhooks.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-repeat"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        @if(isset($stats['recent_failures']) && $stats['recent_failures']->count() > 0)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-octagon"></i> Recent Failures:</strong>
            <ul class="mb-0 mt-2">
                @foreach($stats['recent_failures'] as $failure)
                <li>Webhook #{{ $failure->webhook_call_id }}: {{ $failure->error_message }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="bi bi-table"></i> Webhook Logs</h5>
                <div>
                    <a href="{{ route('webhooks.export') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                    <a href="{{ route('webhooks.test.form') }}" class="btn btn-info btn-sm text-white">
                        <i class="bi bi-bug"></i> Test Webhook
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Event</th>
                            <th>Payload</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th>Memory</th>
                            <th>Response Code</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($webhooks as $webhook)
                        <tr>
                            <td>#{{ $webhook->id }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $webhook->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <pre>{{ json_encode($webhook->payload ?? [], JSON_PRETTY_PRINT) }}</pre>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $webhook->status ?? 'pending' }}">
                                    {{ ucfirst($webhook->status ?? 'Pending') }}
                                </span>
                            </td>
                            <td>{{ $webhook->execution_time ?? '-' }}</td>
                            <td>{{ $webhook->memory_usage ?? '-' }}</td>
                            <td>
                                @if(isset($webhook->response_code) && $webhook->response_code)
                                    <span class="badge {{ $webhook->response_code == 200 ? 'bg-success' : 'bg-warning' }}">
                                        {{ $webhook->response_code }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ isset($webhook->created_at) ? $webhook->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('webhooks.show', $webhook->id) }}" class="btn btn-sm btn-info btn-action text-white" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(isset($webhook->status) && $webhook->status == 'failed' && isset($webhook->retry_count) && $webhook->retry_count < 3)
                                <form action="{{ route('webhooks.retry', $webhook->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning btn-action text-dark" title="Retry">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No webhook logs found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 d-flex justify-content-center">
                {{ $webhooks->links() }}
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
        }

       const eventsLabels = {!! json_encode(array_keys($stats['events_by_type'] ?? [])) !!};
const eventsData = {!! json_encode(array_values($stats['events_by_type'] ?? [])) !!};
        
        const eventsCtx = document.getElementById('eventsChart').getContext('2d');
        const eventsChart = new Chart(eventsCtx, {
            type: 'doughnut',
            data: {
                labels: eventsLabels,
                datasets: [{
                    data: eventsData,
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#43e97b', '#fa709a'],
                    borderColor: 'transparent'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#666'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Webhooks by Event Type',
                        color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#666'
                    }
                }
            }
        });
        
        fetch('{{ route("webhooks.stats") }}')
            .then(response => response.json())
            .then(data => {
                const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
                new Chart(hourlyCtx, {
                    type: 'line',
                    data: {
                        labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                        datasets: [{
                            label: 'Webhooks Received',
                            data: data.total_by_hour || Array(24).fill(0),
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#666'
                                }
                            },
                            title: {
                                display: true,
                                text: 'Webhooks by Hour',
                                color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#666'
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: document.documentElement.classList.contains('dark') ? '#a0aec0' : '#666' },
                                grid: { color: document.documentElement.classList.contains('dark') ? '#4a5568' : '#e5e7eb' }
                            },
                            y: {
                                ticks: { color: document.documentElement.classList.contains('dark') ? '#a0aec0' : '#666' },
                                grid: { color: document.documentElement.classList.contains('dark') ? '#4a5568' : '#e5e7eb' }
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error loading chart data:', error);
                document.getElementById('hourlyChart').parentElement.innerHTML = 
                    '<div class="no-data"><i class="bi bi-bar-chart"></i><p>No data available for hourly chart</p></div>';
            });
        
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>

</html>
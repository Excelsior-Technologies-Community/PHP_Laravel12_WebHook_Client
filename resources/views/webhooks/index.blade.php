<!DOCTYPE html>
<html>

<head>
    <title>Webhook Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #eef2f7, #f8fafc);
            font-family: 'Segoe UI', sans-serif;
        }

        .container-box {
            max-width: 1200px;
            margin: 40px auto;
        }

        .header {
            background: #111827;
            color: white;
            padding: 20px;
            border-radius: 12px;
        }

        .card-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .table thead {
            background: #111827;
            color: white;
        }

        .badge-success {
            background: #16a34a;
        }

        pre {
            max-height: 120px;
            overflow: auto;
            background: #f3f4f6;
            padding: 10px;
            border-radius: 8px;
        }

        .filter-box input {
            border-radius: 10px;
            padding: 8px 12px;
            border: 1px solid #ddd;
        }

        .btn-filter {
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <div class="container container-box">

        <!-- HEADER -->
        <div class="header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">🔥 Webhook Dashboard</h3>
                <small>Track all incoming webhook events in real-time</small>
            </div>
        </div>

        <!-- FILTER -->
        <div class="card-box filter-box">
            <form method="GET" class="row g-2">

                <div class="col-md-4">
                    <input type="text"
                        name="event"
                        class="form-control"
                        placeholder="Event (e.g. order.created)"
                        value="{{ request('event') }}">
                </div>

                <div class="col-md-3">
                    <input type="number"
                        name="order_id"
                        class="form-control"
                        placeholder="Order ID"
                        value="{{ request('order_id') }}">
                </div>

                <div class="col-md-3">
                    <input type="number"
                        name="amount"
                        class="form-control"
                        placeholder="Amount"
                        value="{{ request('amount') }}">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary w-100">Filter</button>
                    <a href="/webhooks" class="btn btn-secondary w-100">Reset</a>
                </div>

            </form>
        </div>

        <!-- TABLE -->
        <div class="card-box">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event</th>
                            <th>Payload</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($webhooks as $webhook)
                        <tr>
                            <td>#{{ $webhook->id }}</td>

                            <td>
                                <span class="badge bg-dark">
                                    {{ $webhook->name }}
                                </span>
                            </td>

                            <td>
                                <pre>{{ json_encode($webhook->payload, JSON_PRETTY_PRINT) }}</pre>
                            </td>

                            <td>
                                <span class="badge badge-success">Processed</span>
                            </td>

                            <td>
                                {{ $webhook->created_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No webhook logs found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $webhooks->links() }}
            </div>
        </div>

    </div>

</body>

</html>
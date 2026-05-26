{{-- resources/views/webhooks/show.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <title>Webhook Details #{{ $webhook->id ?? 'N/A' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-info-circle"></i> Webhook Details #{{ $webhook->id ?? 'N/A' }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Basic Information</h5>
                        <table class="table">
                            <tr>
                                <th>ID:</th>
                                <td>{{ $webhook->id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Event:</th>
                                <td><span class="badge bg-secondary">{{ $webhook->name ?? 'N/A' }}</span></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ isset($webhook->status) && $webhook->status == 'processed' ? 'success' : (isset($webhook->status) && $webhook->status == 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($webhook->status ?? 'Pending') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Retry Count:</th>
                                <td>{{ $webhook->retry_count ?? 0 }} / 3</td>
                            </tr>
                            <tr>
                                <th>Response Code:</th>
                                <td>{{ $webhook->response_code ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ isset($webhook->created_at) ? $webhook->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Processed At:</th>
                                <td>{{ isset($webhook->processed_at) && $webhook->processed_at ? $webhook->processed_at->format('d M Y, h:i A') : 'Not processed' }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Payload</h5>
                        <pre class="bg-light p-3 rounded">{{ isset($webhook->payload) ? json_encode($webhook->payload, JSON_PRETTY_PRINT) : 'No payload' }}</pre>
                        
                        @if(isset($webhook->response_body) && $webhook->response_body)
                        <h5 class="mt-3">Response Body</h5>
                        <pre class="bg-light p-3 rounded">{{ json_encode($webhook->response_body, JSON_PRETTY_PRINT) }}</pre>
                        @endif
                    </div>
                </div>
                
                @if(isset($webhook->failures) && $webhook->failures->count() > 0)
                <div class="mt-4">
                    <h5><i class="bi bi-exclamation-triangle text-danger"></i> Failure Details</h5>
                    <div class="alert alert-danger">
                        @foreach($webhook->failures as $failure)
                        <div class="mb-3">
                            <strong>Error:</strong> {{ $failure->error_message ?? 'Unknown error' }}<br>
                            <strong>Time:</strong> {{ isset($failure->created_at) ? $failure->created_at->format('d M Y, h:i A') : 'N/A' }}<br>
                            @if(isset($failure->error_context) && $failure->error_context)
                            <details>
                                <summary>Stack Trace</summary>
                                <pre class="mt-2">{{ is_array($failure->error_context) ? json_encode($failure->error_context, JSON_PRETTY_PRINT) : $failure->error_context }}</pre>
                            </details>
                            @endif
                        </div>
                        @if(!$loop->last)<hr>@endif
                        @endforeach
                    </div>
                </div>
                @endif
                
                <div class="mt-4">
                    <a href="{{ route('webhooks.dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    
                    @if(isset($webhook->status) && $webhook->status == 'failed' && isset($webhook->retry_count) && $webhook->retry_count < 3)
                    <form action="{{ route('webhooks.retry', $webhook->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-repeat"></i> Retry Webhook
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>

</html>
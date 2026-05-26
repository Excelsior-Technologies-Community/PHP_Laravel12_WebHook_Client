{{-- resources/views/webhooks/test.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <title>Test Webhook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4><i class="bi bi-bug"></i> Webhook Testing Tool</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Send test webhook payloads to your local webhook endpoint
                </div>
                
                <form id="testForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Webhook URL</label>
                        <input type="url" class="form-control" id="webhook_url" value="{{ url('/webhook-client') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Event Type</label>
                        <select class="form-select" id="event_type">
                            <option value="order.created">Order Created</option>
                            <option value="order.updated">Order Updated</option>
                            <option value="payment.received">Payment Received</option>
                            <option value="custom">Custom Payload</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Custom Payload (JSON)</label>
                        <textarea class="form-control" id="custom_payload" rows="8" placeholder='{"key": "value"}'></textarea>
                        <small class="text-muted">Valid JSON format required for custom payload</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Test Webhook
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="loadTemplate()">
                        <i class="bi bi-file-text"></i> Load Template
                    </button>
                </form>
                
                <div id="result" class="mt-4" style="display: none;">
                    <h5>Response:</h5>
                    <pre id="response" class="bg-light p-3 rounded"></pre>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-lightning"></i> Quick Test Buttons</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-outline-primary m-1" onclick="quickTest('order.created')">
                    Test Order Created
                </button>
                <button class="btn btn-outline-primary m-1" onclick="quickTest('order.updated')">
                    Test Order Updated
                </button>
                <button class="btn btn-outline-primary m-1" onclick="quickTest('payment.received')">
                    Test Payment Received
                </button>
            </div>
        </div>
    </div>
    
    <script>
        const templates = {
            'order.created': {
                event: "order.created",
                order_id: Math.floor(Math.random() * 9000) + 1000,
                amount: Math.floor(Math.random() * 10000) + 100,
                customer: {
                    name: "Test Customer",
                    email: "test@example.com",
                    phone: "+1234567890"
                },
                items: [
                    {id: 1, name: "Product 1", quantity: 2, price: 50},
                    {id: 2, name: "Product 2", quantity: 1, price: 150}
                ],
                shipping_address: {
                    street: "123 Test St",
                    city: "Test City",
                    country: "Test Country"
                }
            },
            'order.updated': {
                event: "order.updated",
                order_id: Math.floor(Math.random() * 9000) + 1000,
                status: "shipped",
                tracking_number: "TRK" + Math.floor(Math.random() * 900000) + 100000,
                updated_at: new Date().toISOString()
            },
            'payment.received': {
                event: "payment.received",
                order_id: Math.floor(Math.random() * 9000) + 1000,
                amount: Math.floor(Math.random() * 10000) + 100,
                payment_method: "credit_card",
                transaction_id: "TXN" + Math.floor(Math.random() * 900000) + 100000,
                status: "completed"
            }
        };
        
        document.getElementById('testForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const webhookUrl = document.getElementById('webhook_url').value;
            const eventType = document.getElementById('event_type').value;
            let payload = {};
            
            if (eventType === 'custom') {
                try {
                    payload = JSON.parse(document.getElementById('custom_payload').value);
                } catch (e) {
                    alert('Invalid JSON payload!');
                    return;
                }
            } else {
                payload = templates[eventType];
            }
            
            // Send webhook
            try {
                const response = await fetch('/webhooks/test/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        webhook_url: webhookUrl,
                        event: payload.event,
                        payload: payload
                    })
                });
                
                const result = await response.json();
                
                document.getElementById('result').style.display = 'block';
                document.getElementById('response').textContent = JSON.stringify(result, null, 2);
                
                if (result.success) {
                    alert('Webhook sent successfully!');
                } else {
                    alert('Failed to send webhook: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
        
        function loadTemplate() {
            const eventType = document.getElementById('event_type').value;
            if (eventType !== 'custom' && templates[eventType]) {
                document.getElementById('custom_payload').value = JSON.stringify(templates[eventType], null, 2);
            }
        }
        
        async function quickTest(eventType) {
            if (!templates[eventType]) return;
            
            try {
                const response = await fetch('/webhooks/test/simulate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ event_type: eventType })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Test webhook sent successfully!\nCheck the dashboard for new entry.');
                    setTimeout(() => {
                        window.location.href = '/webhooks';
                    }, 1000);
                } else {
                    alert('Failed: ' + result.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stripe Payment Test - FlowTrip</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        #card-element {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
        }
        
        #card-errors {
            color: #fa755a;
            margin-top: 10px;
            font-size: 14px;
        }
        
        #submit-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        #submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        #submit-button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            text-align: center;
            margin-top: 10px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 FlowTrip Payment Test</h1>
        
        <form id="payment-form">
            <div class="form-group">
                <label for="amount">Amount (USD)</label>
                <input type="number" id="amount" name="amount" min="1" step="0.01" value="10.00" required>
            </div>
            
            <div class="form-group">
                <label for="card-element">Credit or Debit Card</label>
                <div id="card-element">
                    <!-- Stripe Elements will create form elements here -->
                </div>
                <div id="card-errors" role="alert"></div>
            </div>
            
            <button type="submit" id="submit-button">
                Pay Now
            </button>
            
            <div class="loading" id="loading" style="display: none;">
                <div class="spinner"></div>
                <p>Processing payment...</p>
            </div>
        </form>
        
        <div id="result" class="result"></div>
    </div>

    <script>
        // Initialize Stripe
        const stripe = Stripe('{{ env("STRIPE_KEY") }}'); // You need to add STRIPE_KEY to your .env file
        const elements = stripe.elements();

        // Create card element
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
            },
        });

        cardElement.mount('#card-element');

        // Handle real-time validation errors from the card Element
        cardElement.on('change', ({error}) => {
            const displayError = document.getElementById('card-errors');
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const loading = document.getElementById('loading');
        const result = document.getElementById('result');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Disable submit button and show loading
            submitButton.disabled = true;
            loading.style.display = 'block';
            result.style.display = 'none';

            // Create token
            const {token, error} = await stripe.createToken(cardElement);

            if (error) {
                showResult(error.message, 'error');
                submitButton.disabled = false;
                loading.style.display = 'none';
            } else {
                // Submit to your server
                submitToken(token);
            }
        });

        async function submitToken(token) {
            const amount = document.getElementById('amount').value;
            try {
                const response = await fetch('/Stripe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        stripeToken: token.id,
                        amount: amount
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showResult(`✅ Payment successful! Charge ID: ${data.charge_id}`, 'success');
                } else {
                    showResult(`❌ ${data.message}`, 'error');
                }
            } catch (error) {
                showResult(`❌ Network error: ${error.message}`, 'error');
            }

            submitButton.disabled = false;
            loading.style.display = 'none';
        }

        function showResult(message, type) {
            result.textContent = message;
            result.className = `result ${type}`;
            result.style.display = 'block';
        }
    </script>
</body>
</html>

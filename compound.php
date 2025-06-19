<?php
$principal = $rate = $time = $frequency = $currency = '';
$amount = $interest = 0;
$error = '';

// Define conversion rates (relative to USD, adjust as needed)
$currency_rates = [
    'USD' => 1.0,  // Base currency
    'INR' => 83.5, // Approximate USD to INR rate
    'EUR' => 0.95, // Approximate USD to EUR rate
    'GBP' => 0.80  // Approximate USD to GBP rate
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $principal = filter_input(INPUT_POST, 'principal', FILTER_VALIDATE_FLOAT);
    $rate = filter_input(INPUT_POST, 'rate', FILTER_VALIDATE_FLOAT);
    $time = filter_input(INPUT_POST, 'time', FILTER_VALIDATE_FLOAT);
    $frequency = filter_input(INPUT_POST, 'frequency', FILTER_SANITIZE_STRING);
    $currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_STRING);

    // Valid compounding frequencies
    $frequencies = [
        'annually' => 1,
        'semiannually' => 2,
        'quarterly' => 4,
        'monthly' => 12
    ];

    // Validation
    if ($principal === false || $principal <= 0) {
        $error = 'Please enter a valid principal amount greater than 0.';
    } elseif ($rate === false || $rate <= 0) {
        $error = 'Please enter a valid interest rate greater than 0.';
    } elseif ($time === false || $time <= 0) {
        $error = 'Please enter a valid time period greater than 0.';
    } elseif (!array_key_exists($frequency, $frequencies)) {
        $error = 'Please select a valid compounding frequency.';
    } elseif (!array_key_exists($currency, $currency_rates)) {
        $error = 'Please select a valid currency.';
    } else {
        // Calculate compound interest in USD first
        $n = $frequencies[$frequency];
        $r = $rate / 100; // Convert percentage to decimal
        $amount_usd = $principal * pow(1 + ($r / $n), $n * $time);
        $interest_usd = $amount_usd - $principal;

        // Convert to selected currency
        $conversion_rate = $currency_rates[$currency];
        $amount = $amount_usd * $conversion_rate;
        $interest = $interest_usd * $conversion_rate;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compound Interest Calculator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: rgb(167, 191, 215);
            background-image: url('calculator-bg.jpg');
            background-size: cover; 
            background-repeat: no-repeat; 
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .calculator-container {
            max-width: 600px;
            margin: 140px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .calculator-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: inherit;
            filter: blur(10px);
            z-index: -1;
            border-radius: 15px;
        }
        .form-label {
            font-weight: bold;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: rgba(233, 236, 239, 0.5);
            border-radius: 5px;
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
        .glassmorphism-chart {
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            max-width: 400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="calculator-container">
            <h2 class="text-center mb-4">Compound Interest Calculator</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" action="" id="calculatorForm">
                <div class="mb-3">
                    <label for="principal" class="form-label">Principal Amount:</label>
                    <input type="number" step="0.01" class="form-control" id="principal" name="principal" value="<?php echo htmlspecialchars($principal); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="rate" class="form-label">Annual Interest Rate (%):</label>
                    <input type="number" step="0.01" class="form-control" id="rate" name="rate" value="<?php echo htmlspecialchars($rate); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="time" class="form-label">Time (Years):</label>
                    <input type="number" step="0.01" class="form-control" id="time" name="time" value="<?php echo htmlspecialchars($time); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="frequency" class="form-label">Compounding Frequency:</label>
                    <select class="form-select" id="frequency" name="frequency" required>
                        <option value="">Select frequency</option>
                        <option value="annually" <?php echo $frequency === 'annually' ? 'selected' : ''; ?>>Annually</option>
                        <option value="semiannually" <?php echo $frequency === 'semiannually' ? 'selected' : ''; ?>>Semi-Annually</option>
                        <option value="quarterly" <?php echo $frequency === 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                        <option value="monthly" <?php echo $frequency === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="currency" class="form-label">Currency:</label>
                    <select class="form-select" id="currency" name="currency" required>
                        <option value="">Select currency</option>
                        <option value="USD" <?php echo $currency === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                        <option value="INR" <?php echo $currency === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                        <option value="EUR" <?php echo $currency === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                        <option value="GBP" <?php echo $currency === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                    </select>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary w-50">Calculate</button>
                    <button type="button" class="btn btn-secondary w-50" id="clearButton">Clear</button>
                </div>
            </form>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
                <div class="result">
                    <h4>Results:</h4>
                    <p><strong>Final Amount:</strong> <?php echo $currency; ?> <?php echo number_format($amount, 2); ?></p>
                    <p><strong>Interest Earned:</strong> <?php echo $currency; ?> <?php echo number_format($interest, 2); ?></p>
                    <div class="glassmorphism-chart mt-4 p-3" style="border-radius: 15px; background: rgba(255,255,255,0.2); box-shadow: 0 4px 6px rgba(0,0,0,0.1); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3); max-width: 400px; margin: 0 auto;">
                        <canvas id="resultPieChart" width="300" height="300"></canvas>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clearButton = document.getElementById('clearButton');
            const form = document.getElementById('calculatorForm');
            if (clearButton && form) {
                clearButton.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent form submission

                    // Clear all input fields
                    form.reset();

                    // Manually clear all input values (for PHP-filled values)
                    form.querySelectorAll('input, select').forEach(function(el) {
                        if (el.type === 'number' || el.tagName === 'SELECT') {
                            el.value = '';
                        }
                    });

                    // Remove result and error messages if present
                    const result = document.querySelector('.result');
                    if (result) {
                        result.remove();
                    }
                    const alert = document.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                });
            }
        });

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
        window.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('resultPieChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Principal', 'Interest'],
                        datasets: [{
                            data: [<?php echo floatval($principal); ?>, <?php echo floatval($interest); ?>],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 99, 132, 0.7)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#222',
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
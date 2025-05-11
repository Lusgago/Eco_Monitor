<?php
// Inicia a sessão e verifica se o usuário está logado
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

include("conection.php");

$sql = "SELECT * FROM users";
$result = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>EcoMonitor - Calculadora de Energia Solar</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }

        button {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        .terminal {
            background: #000;
            color: #00ff00;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
        }

        .divider {
            border-top: 1px dashed #00ff00;
            margin: 15px 0;
        }

        .summary-box {
            background: #e8f8f5;
            border: 2px solid #27ae60;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .summary-box h3 {
            color: #27ae60;
            margin-top: 0;
            text-align: center;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-item strong {
            color: #2c3e50;
        }

        .loading {
            text-align: center;
            margin: 20px 0;
            display: none;
        }

        .error {
            background: #ffecec;
            border: 2px solid #e74c3c;
            color: #e74c3c;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            display: none;
        }

        .user-info {
            text-align: right;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="user-info">
            Logado como: <?php echo $_SESSION['email']; ?>
            <a href="logout.php" class="logout-btn">Sair</a>
        </div>

        <?php
        if ($result->num_rows > 0) {
            while ($linha = $result->fetch_assoc()) {
                echo "<p>Nome: " . $linha["nome"] . "</p>";
            }
        } else {
            echo "<p>Nenhum resultado encontrado.</p>";
        }
        ?>

        <h1>EcoMonitor - Calculadora de Economia Solar</h1>
        <form id="energyForm">
            <div class="form-group">
                <label for="daily_kwh">Consumo diário (kWh):</label>
                <input type="number" id="daily_kwh" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="price_kwh">Preço por kWh ($):</label>
                <input type="number" id="price_kwh" step="0.0001" required>
            </div>
            <div class="form-group">
                <label for="solar_gen">Geração solar (kWh/dia):</label>
                <input type="number" id="solar_gen" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="solar_cost">Custo de instalação solar ($):</label>
                <input type="number" id="solar_cost" step="0.01">
            </div>
            <button type="submit">Calcular</button>
        </form>

        <div id="loading" class="loading">Calculando resultados...</div>
        <div id="error" class="error"></div>

        <!-- Novo campo para exibir o resumo dos resultados -->
        <div id="summary" class="summary-box" style="display:none;">
            <h3>RESUMO DOS RESULTADOS</h3>
            <div class="summary-item">
                <span>Economia mensal:</span>
                <strong id="monthly-savings">$0.00</strong>
            </div>
            <div class="summary-item">
                <span>Economia anual:</span>
                <strong id="annual-savings">$0.00</strong>
            </div>
            <div class="summary-item">
                <span>Tempo de retorno do investimento:</span>
                <strong id="payback-time">0 anos e 0 meses</strong>
            </div>
        </div>

        <div id="result" class="terminal" style="display:none;"></div>
    </div>

    <script>
        document.getElementById('energyForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Mostrar loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('error').style.display = 'none';
            document.getElementById('summary').style.display = 'none';
            document.getElementById('result').style.display = 'none';

            // Dados para enviar ao backend
            const data = {
                daily_kwh: parseFloat(document.getElementById('daily_kwh').value),
                price_kwh: parseFloat(document.getElementById('price_kwh').value),
                solar_gen: parseFloat(document.getElementById('solar_gen').value),
                solar_cost: parseFloat(document.getElementById('solar_cost').value) || 0
            };

            // Em vez de usar fetch para a API, vamos fazer os cálculos diretamente no frontend
            // para evitar problemas de CORS
            const resultado = calcularEnergia(data);
            displayResults(resultado);
        });

        function calcularEnergia(data) {
            const daily_kwh = data.daily_kwh;
            const price_kwh = data.price_kwh;
            const solar_gen = data.solar_gen;
            const solar_cost = data.solar_cost;

            const monthly_usage = daily_kwh * 30;
            const monthly_cost_no_solar = monthly_usage * price_kwh;

            const monthly_solar_gen = solar_gen * 30;
            const remaining_energy = Math.max(0, monthly_usage - monthly_solar_gen);
            const monthly_cost_with_solar = remaining_energy * price_kwh;

            const monthly_savings = monthly_cost_no_solar - monthly_cost_with_solar;
            const annual_savings = monthly_savings * 12;

            let payback_years = 0;
            let payback_months = 0;

            if (solar_cost > 0 && annual_savings > 0) {
                const total_years = solar_cost / annual_savings;
                payback_years = Math.floor(total_years);
                payback_months = Math.floor((total_years - payback_years) * 12);
            }

            return {
                inputs: {
                    daily_kwh: daily_kwh,
                    price_kwh: price_kwh,
                    solar_gen: solar_gen,
                    solar_cost: solar_cost
                },
                results: {
                    monthly_usage: monthly_usage,
                    monthly_cost_no_solar: monthly_cost_no_solar,
                    monthly_solar_gen: monthly_solar_gen,
                    remaining_energy: remaining_energy,
                    monthly_cost_with_solar: monthly_cost_with_solar,
                    monthly_savings: monthly_savings,
                    annual_savings: annual_savings,
                    payback_years: payback_years,
                    payback_months: payback_months
                }
            };
        }

        function displayResults(data) {
            document.getElementById('loading').style.display = 'none';

            // Atualizar o resumo dos resultados
            document.getElementById('monthly-savings').textContent = '$' + data.results.monthly_savings.toFixed(2);
            document.getElementById('annual-savings').textContent = '$' + data.results.annual_savings.toFixed(2);
            document.getElementById('payback-time').textContent = data.results.payback_years + ' anos e ' + data.results.payback_months + ' meses';
            document.getElementById('summary').style.display = 'block';

            // Atualizar o terminal com resultados detalhados
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';

            // Formatação idêntica ao terminal
            resultDiv.innerHTML = `
================================================================================
                    CALCULADORA DE ENERGIA RESIDENCIAL & ECONOMIA SOLAR
================================================================================

SEUS DADOS:
• Consumo diário de energia: ${data.inputs.daily_kwh.toFixed(2)} kWh
• Preço da eletricidade: ${data.inputs.price_kwh.toFixed(4)} por kWh
• Geração solar: ${data.inputs.solar_gen.toFixed(2)} kWh/dia
• Custo de instalação: ${data.inputs.solar_cost.toFixed(2)}

------------------------------------------------------------
                    RESUMO DOS RESULTADOS
------------------------------------------------------------

PERFIL ENERGÉTICO MENSAL:
• Consumo de energia: ${data.results.monthly_usage.toFixed(2)} kWh
• Custo sem energia solar: ${data.results.monthly_cost_no_solar.toFixed(2)}
• Geração solar total: ${data.results.monthly_solar_gen.toFixed(2)} kWh
• Energia adicional necessária: ${data.results.remaining_energy.toFixed(2)} kWh
• Custo com energia solar: ${data.results.monthly_cost_with_solar.toFixed(2)}

ECONOMIA:
• Economia mensal: ${data.results.monthly_savings.toFixed(2)}
• Economia anual: ${data.results.annual_savings.toFixed(2)}

PERÍODO DE RETORNO:
• Tempo estimado de retorno: ${data.results.payback_years} anos e ${data.results.payback_months} meses
------------------------------------------------------------
                `;
        }
    </script>
</body>

</html>
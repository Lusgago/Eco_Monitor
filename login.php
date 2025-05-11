<?php
// login.php
session_start();

// Se o usuário já estiver logado, redireciona para index.php
if (isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit();
}

include("conection.php");

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conexao->real_escape_string($_POST['email']);
    $senha = $_POST['senha']; // Não precisa escapar aqui, pois será usado apenas no password_verify
    
    // Consulta SQL para buscar o hash da senha
    $sql = "SELECT id, nome, email, senha FROM users WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Usuário encontrado
        $user = $result->fetch_assoc();
        $senha_hash = $user['senha']; // Hash da senha armazenada no banco
        
        // Verifica se a senha inserida corresponde ao hash
        if (password_verify($senha, $senha_hash)) {
            // Login bem-sucedido
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['email'] = $user['email'];
            
            header("Location: index.php");
            exit();
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Usuário não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoMonitor - Login</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo {
            color: #27ae60;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }
        
        .input-group {
            margin-bottom: 25px;
            text-align: left;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            box-sizing: border-box;
            transition: border 0.3s;
        }
        
        .input-group input:focus {
            border-color: #27ae60;
            outline: none;
        }
        
        button {
            background: #27ae60;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s, transform 0.2s;
            margin-top: 10px;
        }
        
        button:hover {
            background: #2ecc71;
            transform: translateY(-2px);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .error {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 14px;
            padding: 10px;
            background: #ffecec;
            border-radius: 4px;
            display: <?php echo isset($erro) ? 'block' : 'none'; ?>;
        }
        
        .register-link {
            margin-top: 25px;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .register-link a {
            color: #27ae60;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .forgot-link {
            display: block;
            margin-top: 15px;
            text-align: right;
            font-size: 13px;
        }
        
        .forgot-link a {
            color: #7f8c8d;
            text-decoration: none;
        }
        
        .forgot-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">EcoMonitor</div>
        
        <?php if (isset($erro)): ?>
            <div class="error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            
            <div class="input-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
                <div class="forgot-link">
                    <a href="forgot-password.php">Esqueceu sua senha?</a>
                </div>
            </div>
            
            <button type="submit">Entrar</button>
        </form>
        
        <div class="register-link">
            Não tem uma conta? <a href="register.php">Crie uma agora</a>
        </div>
    </div>

    <script>
        // Efeito de foco no primeiro campo ao carregar
        document.getElementById('email').focus();
        
        // Validação básica do formulário antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            
            if (!email || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos!');
            }
        });
    </script>
</body>
</html>
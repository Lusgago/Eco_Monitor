<?php
// register.php
include("conection.php");
session_start();

$erro = "";

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitiza os dados recebidos do formulário
    $nome = $conexao->real_escape_string(trim($_POST['nome']));
    $email = $conexao->real_escape_string(trim($_POST['email']));
    $senha = $conexao->real_escape_string(trim($_POST['senha']));

    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Formato de email inválido!";
    } else {
        // Criptografa a senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Verifica se o email já está cadastrado
        $check_sql = $conexao->prepare("SELECT * FROM users WHERE email = ?");
        $check_sql->bind_param("s", $email);
        $check_sql->execute();
        $check_result = $check_sql->get_result();

        if ($check_result->num_rows > 0) {
            $erro = "Este email já está cadastrado!";
        } else {
            // Insere o novo usuário no banco de dados
            $insert_sql = $conexao->prepare("INSERT INTO users (nome, email, senha) VALUES (?, ?, ?)");
            $insert_sql->bind_param("sss", $nome, $email, $senha_hash);

            if ($insert_sql->execute()) {
                $_SESSION['mensagem'] = "Registro realizado com sucesso!";
                header("Location: login.php");
                exit();
            } else {
                $erro = "Erro ao cadastrar. Tente novamente mais tarde.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoMonitor - Registro</title>
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
        }
        .register-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }
        .logo {
            color: #27ae60;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            box-sizing: border-box;
        }
        button {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background: #2ecc71;
        }
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .login-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .login-link a {
            color: #27ae60;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">EcoMonitor</div>
        
        <?php if (!empty($erro)): ?>
            <div class="error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="input-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="input-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required autocomplete="off">
            </div>
            
            <button type="submit">Registrar</button>
        </form>
        
        <div class="login-link">
            Já tem uma conta? <a href="login.php">Faça login</a>
        </div>
    </div>
</body>
</html>
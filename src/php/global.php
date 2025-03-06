<?php
    require "../database/connection.php";

    $pdo = Database::getConnection(); // Conexão com o banco de dados

    session_set_cookie_params([
        'httponly' => true, // Não permite que o cookie seja acessado por JavaScript
        'samesite' => 'Strict', // Não permite que o cookie seja enviado por requisições de outros sites
        'secure' => isset($_SERVER['HTTPS']) // Se a requisição veio por HTTPS, então é seguro
    ]);
    session_start();
    
    $_SESSION['logged'] = $_SESSION['logged'] ?? NULL; // Se não existir, atribui NULL

    if (isset($_POST["type"])) {

        // Proteção contra CSRF
        if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== "LSZzKRtj2Ke") {
            header("Location: ../../error.php?errorCode=403");
            exit();
        };

        /* 
            Se o tipo for logout, verifica se o usuário está logado e destroi a sessão.
        */
        if ($_POST["type"] === "logout") {

            // Verifica se o usuário está logado
            if (!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) {
                header("Location: ../../index.php");
                exit();
            };

            $_SESSION = []; // Limpa a sessão
            
            session_destroy();

            // Exclui o cookie da sessão
            setcookie(session_name(), '', time() - 42000, '/');

            header("Location: ../../index.php");

            exit();
        }

        /* 
            Se o tipo for login, verifica se o usuário e senha estão corretos e cria a sessão.
        */
        else if ($_POST["type"] === "login" && $_SERVER["REQUEST_METHOD"] === "POST") {

            $user = htmlspecialchars(trim($_POST["usuario"]), ENT_QUOTES, 'UTF-8');
            $pass =htmlspecialchars(trim($_POST["senha"]), ENT_QUOTES, 'UTF-8');
            
            // Verifica se os campos estão preenchidos
            if (empty($user) || empty($pass)) {
                header("Location: ../../login.php?typeMsg=error&message=Todos os campos são obrigatórios.");
                exit();
            };
        
            try {

                // Busca o usuário no banco
                $query = $pdo->prepare("SELECT id, usuario, senha FROM usuarios WHERE usuario = :user LIMIT 1");
                $query->bindParam(":user", $user, PDO::PARAM_STR);
                $query->execute();
                
                // Verifica se o usuário existe e se a senha está correta
                $userData = $query->fetch(PDO::FETCH_ASSOC);
                
                // Se o usuário existir e a senha estiver correta, cria a sessão
                if ($userData && password_verify($pass, $userData["senha"])) {
                    session_regenerate_id(true); // Regenera o ID da sessão

                    $_SESSION["user_id"] = $userData["id"];
                    $_SESSION["usuario"] = $userData["usuario"];
                    $_SESSION["logged"] = true;
        
                    header("Location: ../../profile.php");
                    exit;
                } else {
                    header("Location: ../../login.php?typeMsg=error&message=Usuário ou senha incorretos.");
                    exit();
                };
        
            } catch (PDOException $e) {
                header("Location: ../../login.php?typeMsg=error&message=Ocorreu um erro, tente novamente.");
                error_log($e->getMessage(), 3, "/var/log/meu_sistema.log");
                exit();
            };

        }

        /* 
            Se o tipo for register, verifica se o usuário e senha estão corretos e registra no banco.
        */
        else if ($_POST["type"] === "register" && $_SERVER["REQUEST_METHOD"] === "POST"){

            $user = htmlspecialchars(trim($_POST["usuario"]), ENT_QUOTES, 'UTF-8');
            $pass = htmlspecialchars(trim($_POST["senha"]), ENT_QUOTES, 'UTF-8');
            $confirmPass = htmlspecialchars(trim($_POST["senhaC"]), ENT_QUOTES, 'UTF-8');

            // Verifica se os campos estão preenchidos
            if (empty($user) || empty($pass) || empty($confirmPass)) {
                header("Location: ../../register.php?typeMsg=error&message=Todos os campos são obrigatórios.");
                exit();
            };

            // Verifica se o usuário e senha estão no formato correto
            if (strlen($user) < 3) {
                header("Location: ../../register.php?typeMsg=error&message=O usuário deve ter no mínimo 3 caracteres.");
                exit();
            };

            // Verifica se o usuário contém apenas letras e números
            if (preg_match("/[^a-zA-Z0-9]/", $user)) {
                header("Location: ../../register.php?typeMsg=error&message=O usuário deve conter apenas letras e números.");
                exit();
            };

            // Verifica se as senhas coincidem
            if ($pass !== $confirmPass) {
                header("Location: ../../register.php?typeMsg=error&message=As senhas não coincidem.");
                exit();
            };

            // Verifica se a senha tem no mínimo 6 caracteres
            if (strlen($pass) < 6) {
                header("Location: ../../register.php?typeMsg=error&message=A senha deve ter no mínimo 6 caracteres.");
                exit();
            };

            try {
                $query = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :user LIMIT 1");
                $query->bindParam(":user", $user, PDO::PARAM_STR);
                $query->execute();

                // Verifica se o usuário já está registrado
                if ($query->fetch()) {
                    header("Location: ../../register.php?typeMsg=error&message=Usuário já registrado.");
                    exit();
                };

                // Hash na senha
                $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
                
                // Registra o usuário no banco
                $insert = $pdo->prepare("INSERT INTO usuarios (usuario, senha) VALUES (:user, :pass)");
                $insert->bindParam(":user", $user, PDO::PARAM_STR);
                $insert->bindParam(":pass", $hashedPassword, PDO::PARAM_STR);

                if ($insert->execute()) {
                    header("Location: ../../login.php?typeMsg=success&message=Registrado com sucesso.");
                } else {
                    header("Location: ../../register.php?typeMsg=error&message=Erro ao registrar.");
                    exit();
                };

            } catch (PDOException $e) {
                header("Location: ../../login.php?typeMsg=error&message=Ocorreu um erro, tente novamente.");
                error_log($e->getMessage(), 3, "/var/log/meu_sistema.log");
                exit();
            };

        } 

        /* 
            Se o tipo for upload, verifica se a imagem está correta e atualiza no banco.
        */
        else if ($_POST["type"] === "upload" && $_SERVER["REQUEST_METHOD"] === "POST"){

            // Verifica se a imagem foi enviada e se não houve erro
            if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES["profile_image"]["tmp_name"];
                $fileName = $_FILES["profile_image"]["name"];
                $fileSize = $_FILES["profile_image"]["size"];
                $fileType = $_FILES["profile_image"]["type"];
                
                // Tipos de arquivos permitidos
                $allowedTypes = ["image/jpeg", "image/png", "image/webp"];
                
                // Verifica se o arquivo é uma imagem do tipo permitido
                if (!in_array($fileType, $allowedTypes)) {
                    header("Location: ../../profile.php?typeMsg=error&message=Formato inválido. Use JPEG, PNG ou WEBP.");
                    exit();
                };
                
                // Verifica se a imagem é menor que 2MB
                if ($fileSize > 2 * 1024 * 1024) {
                    header("Location: ../../profile.php?typeMsg=error&message=Imagem muito grande. Máximo 2MB.");
                    exit();
                };
                
                // Lê o arquivo e converte em binário
                $imageData = file_get_contents($fileTmpPath);
                
                // Atualiza a imagem no banco
                $query = $pdo->prepare("UPDATE usuarios SET imagem = :imagem WHERE id = :id");
                $query->bindParam(":imagem", $imageData, PDO::PARAM_LOB);
                $query->bindParam(":id", $_SESSION["user_id"], PDO::PARAM_INT);
                
                if ($query->execute()) {
                    header("Location: ../../profile.php?typeMsg=success&message=Imagem alterada com sucesso.");
                    exit();
                } else {
                    header("Location: ../../profile.php?typeMsg=error&message=Erro ao atualizar no banco.");
                    exit();
                };

            } else {
                header("Location: ../../profile.php?typeMsg=error&message=Nenhuma imagem foi enviada.");
                exit();
            };

        }else {
           header("Location: ../../error.php?errorCode=404");
        };

    }else{
        header("Location: ../../error.php?errorCode=404");
    };

?>
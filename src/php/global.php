<?php
    require "../database/connection.php";

    $pdo = Database::getConnection();

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => isset($_SERVER['HTTPS'])
    ]);
    session_start();
    
    $_SESSION['logged'] = $_SESSION['logged'] ?? NULL; // Se não existir, atribui NULL

    if (isset($_POST["type"])) {

        if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== "LSZzKRtj2Ke") {
            header("Location: ../../error.php?errorCode=403");
            exit();
        };

        if ($_POST["type"] === "logout") {

            if (!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) {
                header("Location: ../../index.php");
                exit();
            };

            $_SESSION = [];
            
            session_destroy();

            setcookie(session_name(), '', time() - 42000, '/'); // Exclui o cookie da sessão

            header("Location: ../../index.php");

            exit();

        }else if ($_POST["type"] === "login" && $_SERVER["REQUEST_METHOD"] === "POST") {

            $user = htmlspecialchars(trim($_POST["usuario"]), ENT_QUOTES, 'UTF-8');
            $pass = trim($_POST["senha"]);
        
            if (empty($user) || empty($pass)) {
                header("Location: ../../login.php?typeMsg=error&message=Todos os campos são obrigatórios.");
                exit();
            };
        
            try {
                $query = $pdo->prepare("SELECT id, usuario, senha FROM usuarios WHERE usuario = :user LIMIT 1");
                $query->bindParam(":user", $user, PDO::PARAM_STR);
                $query->execute();
                
                $userData = $query->fetch(PDO::FETCH_ASSOC);
        
                if ($userData && password_verify($pass, $userData["senha"])) {
                    session_regenerate_id(true);

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

        }else if ($_POST["type"] === "register" && $_SERVER["REQUEST_METHOD"] === "POST"){

            $user = htmlspecialchars(trim($_POST["usuario"]), ENT_QUOTES, 'UTF-8');

            $pass = trim($_POST["senha"]);
            $confirmPass = trim($_POST["senhaC"]);

            if (empty($user) || empty($pass) || empty($confirmPass)) {
                header("Location: ../../register.php?typeMsg=error&message=Todos os campos são obrigatórios.");
                exit();
            };

            if (strlen($user) < 3) {
                header("Location: ../../register.php?typeMsg=error&message=O usuário deve ter no mínimo 3 caracteres.");
                exit();
            };

            if (preg_match("/[^a-zA-Z0-9]/", $user)) {
                header("Location: ../../register.php?typeMsg=error&message=O usuário deve conter apenas letras e números.");
                exit();
            };

            if ($pass !== $confirmPass) {
                header("Location: ../../register.php?typeMsg=error&message=As senhas não coincidem.");
                exit();
            };

            if (strlen($pass) < 6) {
                header("Location: ../../register.php?typeMsg=error&message=A senha deve ter no mínimo 6 caracteres.");
                exit();
            };

            try {
                $query = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :user LIMIT 1");
                $query->bindParam(":user", $user, PDO::PARAM_STR);
                $query->execute();

                if ($query->fetch()) {
                    header("Location: ../../register.php?typeMsg=error&message=Usuário já registrado.");
                    exit();
                };

                $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

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

        } else if ($_POST["type"] === "upload" && $_SERVER["REQUEST_METHOD"] === "POST"){
            if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES["profile_image"]["tmp_name"];
                $fileName = $_FILES["profile_image"]["name"];
                $fileSize = $_FILES["profile_image"]["size"];
                $fileType = $_FILES["profile_image"]["type"];
                
                $allowedTypes = ["image/jpeg", "image/png", "image/webp"];
                
                if (!in_array($fileType, $allowedTypes)) {
                    header("Location: ../../profile.php?typeMsg=error&message=Formato inválido. Use JPEG, PNG ou WEBP.");
                    exit();
                };
            
                if ($fileSize > 2 * 1024 * 1024) {
                    header("Location: ../../profile.php?typeMsg=error&message=Imagem muito grande. Máximo 2MB.");
                    exit();
                };
            
                $imageData = file_get_contents($fileTmpPath);
            
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
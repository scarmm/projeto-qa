<?php
    require "../database/config.php";

    $pdo = Database::getConnection();

    session_start();
    
    $_SESSION['logged'] = $_SESSION['logged'] ?? NULL;

    if (isset($_GET["type"])) {

        if ($_SERVER["REQUEST_METHOD"] === "POST" && $_GET["type"] === "logout" && $_SESSION["logged"]) {

            session_destroy();

            unset($_SESSION["user_id"]);
            unset($_SESSION["logged"]);
            unset($_SESSION["usuario"]);

            header("Location: ../../index.php");

        }else if ($_SERVER["REQUEST_METHOD"] === "POST" && $_GET["type"] === "login") {

            $user = trim($_POST["usuario"]);
            $pass = trim($_POST["senha"]);
        
            if (empty($user) || empty($pass)) {
                header("Location: ../../login.php?typeMsg=error&message=Todos os campos são obrigatórios.");
                die();
            };
        
            try {
                $query = $pdo->prepare("SELECT id, usuario, senha FROM usuarios WHERE usuario = :user LIMIT 1");
                $query->bindParam(":user", $user, PDO::PARAM_STR);
                $query->execute();
                
                $userData = $query->fetch(PDO::FETCH_ASSOC);
        
                if ($userData && password_verify($pass, $userData["senha"])) {
                    $_SESSION["user_id"] = $userData["id"];
                    $_SESSION["usuario"] = $userData["usuario"];
                    $_SESSION["logged"] = true;
        
                    header("Location: ../../profile.php");
                    exit;
                } else {
                    header("Location: ../../login.php?typeMsg=error&message=Usuário ou senha incorretos.");
                    die();
                };
        
            } catch (PDOException $e) {
                header("Location: ../../login.php?typeMsg=error&message=Erro ao conectar: " . $e->getMessage());
                die();
            };

        }else if ($_SERVER["REQUEST_METHOD"] === "POST" && $_GET["type"] === "register"){

            $user = trim($_POST["usuario"]);
            $pass = trim($_POST["senha"]);
            $confirmPass = trim($_POST["senhaC"]);

            if (empty($user) || empty($pass) || empty($confirmPass)) {
                header("Location: ../../register.php?typeMsg=error&message=Todos os campos são obrigatórios.");
                die();
            };

            if ($pass !== $confirmPass) {
                header("Location: ../../register.php?typeMsg=error&message=As senhas não coincidem.");
                die();
            };

            if (strlen($pass) < 6) {
                header("Location: ../../register.php?typeMsg=error&message=A senha deve ter no mínimo 6 caracteres.");
                die();
            };

            try {
                $query = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :user LIMIT 1");
                $query->bindParam(":user", $user, PDO::PARAM_STR);
                $query->execute();

                if ($query->fetch()) {
                    header("Location: ../../register.php?typeMsg=error&message=Usuário já registrado.");
                    die();
                };

                $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

                $insert = $pdo->prepare("INSERT INTO usuarios (usuario, senha) VALUES (:user, :pass)");
                $insert->bindParam(":user", $user, PDO::PARAM_STR);
                $insert->bindParam(":pass", $hashedPassword, PDO::PARAM_STR);

                if ($insert->execute()) {
                    header("Location: ../../login.php?typeMsg=success&message=Registrado com sucesso.");
                } else {
                    header("Location: ../../register.php?typeMsg=error&message=Erro ao registrar.");
                    die();
                };

            } catch (PDOException $e) {
                header("Location: ../../register.php?typeMsg=error&message=Erro ao conectar: " . $e->getMessage());
                die();
            };

        } else if ($_SERVER["REQUEST_METHOD"] === "POST" && $_GET["type"] === "upload"){
            if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES["profile_image"]["tmp_name"];
                $fileName = $_FILES["profile_image"]["name"];
                $fileSize = $_FILES["profile_image"]["size"];
                $fileType = $_FILES["profile_image"]["type"];
                
                $allowedTypes = ["image/jpeg", "image/png", "image/webp"];
                
                if (!in_array($fileType, $allowedTypes)) {
                    header("Location: ../../profile.php?typeMsg=error&message=Formato inválido. Use JPEG, PNG ou WEBP.");
                    exit();
                }
            
                if ($fileSize > 2 * 1024 * 1024) {
                    header("Location: ../../profile.php?typeMsg=error&message=Imagem muito grande. Máximo 2MB.");
                    exit();
                }
            
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
            }

        }else {
           header("Location: ../../404.php");
        };

    }else{
        header("Location: ../../404.php");
    };

?>
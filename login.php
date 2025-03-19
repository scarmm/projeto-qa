<?php
    session_start();

    if (isset($_SESSION["logged"])){
        header("Location: profile.php");
        die();
    };
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>

        <link rel="stylesheet" href="src/styles/index.css">
    </head>
    <body>
        <main class="container">

            <section class="panel-section">

                <form action="src/php/global.php" method="POST">
                    
                    <h1>Login</h1>
                    
                    <!-- protection -->
                    <input type="hidden" name="type" value="login">
                    <input type="hidden" name="csrf_token" value="LSZzKRtj2Ke">

                    <div class="form-group">
                        <input type="text" name="usuario" placeholder="Usuário">
                    </div>
                    <div class="form-group">
                        <input type="password" name="senha" placeholder="Senha">
                    </div>

                    <?php 
                        if (!empty($_GET["typeMsg"]) && !empty($_GET["message"])) { 
                            $typeMsg = $_GET["typeMsg"];
                            $message = $_GET["message"];

                            if ($typeMsg === "success" || $typeMsg === "error") {
                                echo "<div class=\"$typeMsg information\"><span>".htmlspecialchars($message, ENT_QUOTES, 'UTF-8')."</span></div>";
                            };
                        };
                    ?>

                    <div class="form-group">
                        <button type="submit">Entrar</button>
                    </div>
                    <div>
                        <p>Não tem uma conta? <a class="link" href="register.php">Registrar</a></p>
                    </div>
                </form>
            </section>

        </main>
    </body>
</html>
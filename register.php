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
        <title>Registrar</title>

        <link rel="stylesheet" href="src/styles/index.css">
    </head>
    <body>
        <main class="container">

            <section class="panel-section">

                <form action="src/php/global.php" method="POST">
                    
                    <h1>Registrar</h1>
                    
                    <!-- protection -->
                    <input type="hidden" name="type" value="register">
                    <input type="hidden" name="csrf_token" value="LSZzKRtj2Ke">

                    <div class="form-group">
                        <input type="text" name="usuario" placeholder="Usuário">
                    </div>
                    <div class="form-group">
                        <input type="password" name="senha" placeholder="Senha">
                    </div>
                    <div class="form-group">
                        <input type="password" name="senhaC" placeholder="Confirmar Senha">
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
                        <button type="submit">Confirmar</button>
                    </div>
                    <div>
                        <p>Já tem uma conta? <a class="link" href="login.php">Entrar</a></p>
                    </div>
                </form>
            </section>

        </main>
    </body>
</html>
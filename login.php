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
                <div>
                    <h1>Login</h1>
                </div>

                <?php if (isset($_GET["typeMsg"]) && isset($_GET["message"])){ ?>
                    <?php if ($_GET["typeMsg"] == "success"){ ?>
                        <div class="success information">
                            <span><?php echo $_GET["message"] ?></span>
                        </div>
                    <?php }else if ($_GET["typeMsg"] == "error"){ ?>
                        <div class="error information">
                            <span><?php echo $_GET["message"] ?></span>
                        </div>
                    <?php }; ?>
                <?php }; ?>

                <form action="src/php/global.php?type=login" method="POST">
                    <div class="form-group">
                        <input type="text" name="usuario" placeholder="Usuário">
                    </div>
                    <div class="form-group">
                        <input type="password" name="senha" placeholder="Senha">
                    </div>
                    <div class="form-group">
                        <button type="submit">Entrar</button>
                    </div>
                    <div>
                        <p´p>Não tem uma conta? <a class="link" href="register.php">Registrar</a></p>
                    </div>
                </form>
            </section>

        </main>
    </body>
</html>
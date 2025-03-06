<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - <?php echo $_GET["errorCode"] ?? "404"; ?></title>

        <link rel="stylesheet" href="src/styles/index.css">
    </head>
    <body>
        <main class="container">

            <section class="error-section">
                <div>
                    <h1 class="error-title"><?php echo $_GET["errorCode"] ?? "404"; ?></h1>
                </div>
                <div>
                    <p class="error-description">Erro inesperado!</p>
                </div>
            </section>

        </main>
    </body>
</html>
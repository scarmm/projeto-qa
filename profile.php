<?php
    require "src/database/connection.php";

    $pdo = Database::getConnection();

    session_start();

    // Verifica se o usuário está logado
    if (!isset($_SESSION["logged"])) {
        header("Location: index.php");
        die();
    };

    $query = $pdo->prepare("SELECT imagem FROM usuarios WHERE id = :id");
    $query->bindParam(":id", $_SESSION["user_id"], PDO::PARAM_INT);
    $query->execute();
    $userData = $query->fetch(PDO::FETCH_ASSOC);

    $userImage = !empty($userData["imagem"]) ? "data:image/jpeg;base64,".base64_encode($userData["imagem"]) : "public/user.png";
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Perfil</title>

        <link rel="stylesheet" href="src/styles/index.css">
    </head>
    <body>
        <main class="container">

            <section class="profile-section">

                <?php if (isset($_GET["typeMsg"]) && isset($_GET["message"])){ ?>
                    <?php if ($_GET["typeMsg"] == "success"){ ?>
                        <div class="success information">
                            <span><?php echo htmlspecialchars($_GET["message"], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php }else if ($_GET["typeMsg"] == "error"){ ?>
                        <div class="error information">
                            <span><?php echo htmlspecialchars($_GET["message"], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php }; ?>
                <?php }; ?>

                <form action="src/php/global.php" method="POST" enctype="multipart/form-data">

                    <!-- protection -->
                    <input type="hidden" name="type" value="upload">
                    <input type="hidden" name="csrf_token" value="LSZzKRtj2Ke">
                    
                    <div>
                        <img src="<?php echo $userImage; ?>" alt="Foto do usuário" id="previewImage">
                    </div>
                    <input type="file" name="profile_image" id="profile_image" accept="image/*">
                    <div id="saveButton">
                        <button style="background-color: rgb(53, 153, 204); width: 100%; box-shadow: 0px 0px 5px rgb(53, 153, 204);" type="submit" name="upload">Salvar</button>
                    </div>
                </form>

                <div>
                    <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION["usuario"] ?? "Usuário!", ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>

                <form action="src/php/global.php" method="POST">

                    <!-- protection -->
                    <input type="hidden" name="type" value="logout">
                    <input type="hidden" name="csrf_token" value="LSZzKRtj2Ke">

                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </section>

        </main>

        <script>
            document.getElementById("previewImage").addEventListener("click", function() {
                document.getElementById("profile_image").click();
            }); // Simula o clique no input file

            document.getElementById("profile_image").addEventListener("change", function(event) {
                const reader = new FileReader(); // Cria um objeto FileReader
                reader.onload = function(){
                    document.getElementById("previewImage").src = reader.result; // Mostra a imagem selecionada
                    document.getElementById("saveButton").style.display = "block"; // Mostra o botão de salvar
                };
                reader.readAsDataURL(event.target.files[0]); // Lê o arquivo selecionado
            }); // Mostra a imagem selecionada
        </script>
    </body>
</html>

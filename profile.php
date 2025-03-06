<?php
    require "src/database/config.php";

    $pdo = Database::getConnection();

    session_start();

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
        
        <style>
            #profile_image {
                display: none;
            }

            #previewImage {
                cursor: pointer;
                width: 250px;
                height: 250px;
                object-fit: cover;
                border-radius: 50%;
                padding: 10px;
                box-shadow: 0px 0px 5px rgba(0, 0, 0, .1);
                transition: all 300ms ease-in-out;
            }

            #previewImage:hover {
                box-shadow: 0px 0px 10px rgba(0, 0, 0, .2);
            }

            #saveButton {
                display: none;
                margin-top: 10px;
            }
        </style>

    </head>
    <body>
        <main class="container">

            <section class="profile-section">

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

                <form action="src/php/global.php?type=upload" method="POST" enctype="multipart/form-data">
                    <div>
                        <img src="<?php echo $userImage; ?>" alt="Foto do usuário" id="previewImage">
                    </div>
                    <input type="file" name="profile_image" id="profile_image" accept="image/*">
                    <div id="saveButton">
                        <button style="background-color: rgb(53, 153, 204); width: 100%; box-shadow: 0px 0px 5px rgb(53, 153, 204);" type="submit" name="upload">Salvar</button>
                    </div>
                </form>

                <div>
                    <h2>Bem-vindo, <?php echo $_SESSION["usuario"] ?? "Usuário!" ?></h2>
                </div>

                <form action="src/php/global.php?type=logout" method="POST">
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

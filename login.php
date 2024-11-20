<!doctype html>
<html lang="en">
<?php $Erreur=null?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My GPS</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="index.html" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <?php if($Erreur):?>
                  <div class="alert alert-danger" role="alert">
                    <?=$Erreur?>
                  </div>  
                  <?php endif?>
                <h1><strong>My GPS</strong></h1>
                </a>
                <p class="text-center">Your Social Tracker</p>
                <form method="post" name="login">
                  <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Username</label>
                    <input type="email" class="form-control" name="email" id="exampleInputEmail1" aria-describedby="emailHelp">
                  </div>
                  <div class="mb-4">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" name="motDePasse" class="form-control" id="exampleInputPassword1">
                  </div>
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" checked>
                      <label class="form-check-label text-dark" for="flexCheckChecked">
                        Remeber this Device
                      </label>
                    </div>
                    <a class="text-primary fw-bold" href="index.php">Forgot Password ?</a>
                  </div>
                  <input type="submit" name="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign In</a>
                  <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-bold">New to My GPS ?</p>
                    <a class="text-primary fw-bold ms-2" href="./authentication-register.html">Create an account</a>
                  </div>
                </form>
              </div>
            </div>

            <?php
                    session_start();

                    $dsn = 'mysql:host=localhost;dbname=localisation';
                    $login = 'root';
                    $password = '';

                    try {
                        $pdo = new PDO($dsn, $login, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        if (isset($_POST['submit'])) {
                            $email = $_POST["email"];
                            $motDePasse = $_POST["motDePasse"];

                            if (!empty($email) && !empty($motDePasse)) {
                                // Correction de la requête SQL (condition WHERE correcte)
                                $sql = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND motDePass = ?");
                                $sql->execute(array($email, $motDePasse));
                                $user = $sql->fetch();

                                if ($user) {
                                  
                                  $Erreur= "<p style='text-align:center; color:green;'>Connexion réussie</p>";
                                    $_SESSION['id'] = $user['id'];
                                    header("Location: index.php");
                                    exit();
                                } else {
                                  $Erreur= "p style='text-align:center; color:red;'>Email ou mot de passe incorrect</p>";
                                }
                            } else {
                              $Erreur= "<p style='text-align:center; color:red;'>Veuillez remplir tous les champs</p>";
                            }
                        }
                    } catch (PDOException $e) {
                        $Erreur="<p style='text-align:center; color:red;'>Erreur de connexion à la base de données : " . $e->getMessage() . "</p>";
                    }
                    ?>



          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
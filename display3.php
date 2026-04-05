<?php


  // {Refrescar cada 1 min para verificar si Existe un nuevo paciente:}
    $page = $_SERVER['PHP_SELF'];
    $sec = "30";
    header("Refresh: $sec; url=$page");
    //-------------------------------------------------------------------
?>
<?php 
include "conexion.php";
include "phonedetect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>*PIDIM* Pantalla Integral De Informacion Medica</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="https://i.postimg.cc/9fqYVvxh/logo.png" type="image/x-icon">
</head>
<body>
      <?php 
      $query = mysqli_query($conection,"SELECT * FROM medico WHERE idmed=1");


      $result = mysqli_num_rows($query); 
      if($result > 0){

                while ($data = mysqli_fetch_array($query)) {
                    if($data["foto"] != 'img_paciente.jpg'){
                        $foto = 'img/uploads/'.$data['foto'];
                    }else{
                            $foto = 'img/'.$data['foto'];
                        }   
            ?>

    <main>
        <aside class="sidebar " data-sidebar>
            <div class="sidebar-info">
                <figure class="avatar-box">
                    <img src="<?php echo $foto ?>" alt="avatar" width="80">
                </figure>

                <div class="info-content">
                    <h1 class="name" title="nombre"><?php echo $data["nombrem"]; ?></h1>
                    <p class="title"><?php echo $data["especialidad"]; ?></p>
                 <p class="title1">CMVZUL: <?php echo $data["comezu"]; ?></p>
                 <p class="title1">CMV: <?php echo $data["mpps"]; ?></p>
                </div>

                <button class="info-more-btn" data-sidebar-btn>
                    <span>Show</span>
                    <ion-icon name="chevron-down"></ion-icon>
                </button>
            </div>

            <div class="sidebar-info-more">
                <div class="separator"></div>

                <ul class="contacts-list">
                    <!-- <li class="contact-item">
                        <div class="icon-box">
                            <ion-icon name="mail-outline"></ion-icon>
                        </div>

                        <div class="contact-info">
                            <p class="contact-title">Email</p>

                            <a href="mailto:richard@example.com" class="contact-link">richardexample.com</a>
                        </div>
                    </li> -->

                    <!-- <li class="contact-item">
                        <div class="icon-box">
                            <ion-icon name="phone-portrait-outline"></ion-icon>
                        </div>

                        <div class="contact-info">
                            <p class="contact-title">Phone</p>

                            <a href="tel:+12133522795" class="contact-link">+1 (213) 352-2795</a>
                        </div>
                    </li> -->

                    <li class="contact-item">
                        <div class="icon-box">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>

                        <div class="contact-info">
                            <p class="contact-title">Fecha de consulta</p>
                            <time><?php echo $data["fechas_consul"]; ?></time>
                            <p class="contact-title">Horarios</p>
                            <time><?php echo $data["horarios"]; ?></time>
                        </div>
                    </li>
        <p class="time"><?php
setlocale(LC_TIME, 'es_VE.UTF-8','esp');
date_default_timezone_set ('America/Caracas');
echo(strftime("%A, %d %B %Y"));
?></p>
<p id="clock" class=clock>.</p>

<!-- 
                    <li class="contact-item">
                        <div class="icon-box">
                            <ion-icon name="location-outline"></ion-icon>
                        </div>

                        <div class="contact-info">
                            <p class="contact-title">Location</p>

                            <address>Sacramento, California, USA</address>
                        </div>
                    </li> -->
                </ul>

                <div class="separator"></div>

                <!-- <ul class="social-list">
                    <li class="social-item"><a href="#" class="social-link"><ion-icon name="logo-facebook"></ion-icon></a></li>
                    <li class="social-item"><a href="#" class="social-link"><ion-icon name="logo-twitter"></ion-icon></a></li>
                    <li class="social-item"><a href="#" class="social-link"><ion-icon name="logo-instagram"></ion-icon></a></li>
                </ul> -->
            </div>
        </aside>

        <div class="main-content">
            <!-- <nav class="navbar">
                <ul class="navbar-list">
                    <li class="navbar-item"><button class="navbar-link active" data-nav-link>About</button></li>
                    <li class="navbar-item"><button class="navbar-link" data-nav-link>Resume</button></li>
                    <li class="navbar-item"><button class="navbar-link" data-nav-link>Portfolio</button></li>
                    <li class="navbar-item"><button class="navbar-link" data-nav-link>Blog</button></li>
                    <li class="navbar-item"><button class="navbar-link" data-nav-link>Contact</button></li>
                </ul>
            </nav> -->

            <article class="about active" data-page="about">
                <header>
                    <h2 class="h2 article-title">Consutorio N° <?php echo $data["consultorio"]; ?></h2>
                </header>

                <!-- <section class="about-text">
                    <p>I'm Creative Director and UI/UX Designer from Sydney, Australia, working in web development and print media. I enjoy turning complex problems into simple, beautiful and intuitive designs.</p>
                    <p>My job is to build your website so that it is functional and user-friendly but at the same time attractive. Moreover, I add personal touch to your product and make sure that is eye-catching and easy to use. My aim is to bring across your message and identity in the most creative way. I created web design for many famous brand companies.</p>
                </section> -->

                <section class="service">
                    <!-- <h3 class="h3 service-title">What I'm doing</h3> -->

                    <ul class="service-list">
                        <li class="service-item">
                            <!-- <div class="service-icon-box">
                                <img src="https://i.postimg.cc/4389jZkP/icon-design.png" alt="icon" width="40">
                            </div> -->

                            <div class="service-content-box">
                                <h4 class="h4 service-item-title">CMV:</h4>
                                <h4 class="h4 service-item-title"><?php echo $data["mpps"]; ?></h4>
                                <!-- <p class="service-item-text">The most modern and high-quality design made at a professional level.</p> -->
                            </div>
                        </li>

                        <li class="service-item">
                            <!-- <div class="service-icon-box">
                                <img src="https://i.postimg.cc/ZqgqrqzG/icon-dev.png" alt="icon" width="40">
                            </div> -->

                            <div class="service-content-box">
                                <h4 class="h4 service-item-title">CMVZUL:</h4>
                                <h4 class="h4 service-item-title"><?php echo $data["comezu"]; ?></h4>
                           </div>

                        </li>

                        <li class="service-item">
                            <!-- <div class="service-icon-box">
                                <img src="https://i.postimg.cc/xjLdzYxZ/icon-app.png" alt="icon" width="40">
                            </div> -->

                            <div class="service-content-box">
                              <h4 class="h4 service-item-title">Especialidades:</h4>
                              <h4 class="h4 service-item-title"><?php echo $data["pacientes"]; ?></h4>
                            </div>
                        </li>

                        <li class="service-item">
                            <!-- <div class="service-icon-box">
                                <img src="https://i.postimg.cc/0NL8zHpx/icon-photo.png" alt="icon" width="40">
                            </div> -->

                     <div class="service-content-box">
                        <h4 class="h4 service-item-title">Paciente Actual</h4>
                        <h4 class="h4 service-item-title"><?php echo $data["paciente_actual"]; ?></h4>
                        <h4 class="h4 service-item-title">Paciente Siguiente</h4>
                        <h5 class="h4 service-item-title"><?php echo $data["paciente_siguiente"]; ?></h5>
                            </div>
                        </li>

                    </ul>
                </section>

                <section class="clients">
                    <h3 class="h3 clients-title">Servicios Disponibles en UDUZ</h3>

                    <ul class="clients-list has-scrollbar">
                        <li class="clients-item">
                            <a href="#"><img src="img/servicios/ECO.png" alt="logo"></a>
                        </li>

                        <li class="clients-item">
                            <a href="#"><img src="img/servicios/LAB.png" alt="logo"></a>
                        </li>

                        <li class="clients-item">
                            <a href="#"><img src="img/servicios/RX.png" alt="logo"></a>
                        </li>
                    </ul>
                    <ul class="clients-list has-scrollbar">
                        <li class="clients-item">
                            <a href="#"><img src="img/servicios/TOMO.png" alt="logo"></a>
                        </li>

                        <li class="clients-item">
                            <a href="#"><img src="img/servicios/PANO.png" alt="logo"></a>
                        </li>

                        <li class="clients-item">
                            <a href="#"><img src="img/servicios/MAMO.png" alt="logo"></a>
                        </li>
                       
                    </ul>
                </section>

            </article>
            

        </div>
    </main>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="ionicons.js"></script>
</body>
</html>
<!-- partial -->
  <script  src="script.js"></script>
  <script src="clock.js"></script>
            <?php 
        }
        } 
    ?>
</body>
</html>

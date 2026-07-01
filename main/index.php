<?php

$configFile = __DIR__ . '/../home/admin/config.json';
$config = [];

if (file_exists($configFile)) {
    $json = file_get_contents($configFile);
    $decoded = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $config = $decoded;
    }
}

?>

<html lang="es">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
        <link rel="stylesheet" href="../css/style.css">
        <title>ARSTUDIO</title>
    </head>

    <body>

        <div id="loading">
            <div class="logo_loading">
                <img src="../img/letters_white.png" class="logo_letters">
                <div class="stars"></div>
            </div>
        </div>

        <div class="content-wrap">
            
            <header>
                <div class="slogan-container">
                    <h1 class="slogan">
                        <span>OWN</span>
                        <span>YOUR</span>
                        <span>STYLE</span>
                    </h1>
                    <h2 class="slogan"><a href="https://www.instagram.com/aaleexrp/"><b>aaleexrp</b>  <i class="fab fa-instagram"></i></a></h2>
                </div>
            </header>
        
            <main>
            <section>
                <div class="content">
                <div class="grid">
                    <!-- desktop -->
                    <div class="layer">
                        <div>
                            <img src="../img/gallery/11.jpeg" alt="" />
                        </div>
                        <div>
                            <img src="../img/gallery/12.jpeg" alt="" />
                        </div>
                        <div>
                            <img src="../img/gallery/9.jpeg" alt="" />
                        </div>                    
                        <div>
                            <img src="../img/gallery/10.jpeg" alt="" />
                        </div>
                        <div>
                            <img src="../img/gallery/13.jpeg" alt="" />
                        </div>
                        <div>
                            <img src="../img/gallery/14.jpeg" alt="" />
                        </div>
                    </div>
                    <!-- Safari -->
                    <div class="layer">
                        <div>
                            <img src="../img/gallery/5.jpeg" alt="" />
                        </div>
                        <div>
                            <img src="../img/gallery/6.jpeg" alt="" />
                        </div>
                        <div>
                            <img src="../img/gallery/3.jpeg" alt="" />
                        </div>                    
                        <div>
                            <img src="../img/gallery/4.jpeg" alt="" />
                        </div>
                        <div>
                            <img src="../img/gallery/7.jpeg" alt="" />
                        </div>
                        <div>
                            <img src="../img/gallery/8.jpeg" alt="" />
                        </div>
                    </div>
                    <!-- centro -->
                    <div class="layer">
                    <div>
                        <img src="../img/gallery/1.png" alt="" />
                    </div>
                    <div>
                        <img src="../img/gallery/2.png" alt="" />
                    </div>
                    </div>
                    <!-- logo -->
                    <div class="scaler">
                        <img src="../img/arstudio.png" alt="" 
                            draggable="false" 
                            ondragstart="return false;" 
                            oncontextmenu="return false;" 
                            class="protected-image">
                    </div>
                </div>
                </div>
            </section>

            <?php if ($config['main']['mapa']['visible'] ?? false): ?>

                <section>
                    <h2 class="fluid"><br><?= htmlspecialchars($config['main']['mapa']['titulo'] ?? 'Google Maps') ?></h2>

                    <div class="map-card">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3161.5199181579137!2d-3.8194895253215675!3d37.5899210227887!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd6ddffecdb58f3b%3A0xb7db56329283d542!2sAR%20STUDIO!5e0!3m2!1ses!2ses!4v1780051583948!5m2!1ses!2ses"
                            allowfullscreen=""
                            loading="lazy">
                        </iframe>
                    </div>
                </section>
            <?php endif; ?>


            <?php if ($config['main']['cita']['visible'] ?? false): ?>

                <section>
                    <a href="" style="text-decoration:none;"><h2 class="fluid"><?= htmlspecialchars($config['main']['cita']['titulo'] ?? 'Pedir Cita') ?> <br><span class="break"><br></span>
                    <span style="font-size:20px;">(Próximamente)</span></h2></a>
                </section>
            <?php endif; ?>

            <?php if ($config['main']['precios']['visible'] ?? false): ?>

                <section>
                    <h2 class="fluid">
                        <?= htmlspecialchars($config['main']['precios']['titulo'] ?? 'Precios') ?>
                    </h2>

                    <?php foreach (($config['main']['precios']['items'] ?? []) as $item): ?>

                        <?php if (!empty($item['visible'])): ?>
                            <h4 class="fluid">
                                <?= htmlspecialchars($item['titulo']) ?>:
                                <?= htmlspecialchars($item['precio']) ?>€
                            </h4>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </section>

            <?php endif; ?>
            



            </main>

            

            <footer class="copyright">
                <p>© 2026 alfonsogm</p>
            </footer>

        </div>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="../js/loading.js"></script>
        <script type="module" src="../js/animation.js"></script>
        <script src="../js/lightmode.js"></script>
        <script src="../js/drawnone.js"></script>

    </body>
</html>
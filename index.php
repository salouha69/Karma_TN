<?php
// Connexion à la base de données
session_start();
require_once 'config.php';

try {
    // Tableau des produits
    $products = [
        ['id' => 1, 'name' => 'ساعة', 'description' => 'ساعة أنيقة لجميع المناسبات', 'image_path' => 'assets/montre.jpg', 'category' => 'إكسسوارات'],
        ['id' => 2, 'name' => 'ذراع تحكم بلايستيشن 4', 'description' => 'ذراع تحكم جديدة لبلايستيشن 4', 'image_path' => 'assets/manette.jpg', 'category' => 'موضة وترفيه'],
        ['id' => 3, 'name' => 'سوار', 'description' => 'سوار عصري لأناقة حديثة', 'image_path' => 'assets/bracelet.jpg', 'category' => 'إكسسوارات']
    ];

    // Insérer les produits dans la base de données
    foreach ($products as $product) {
        // Vér gedurende si le produit existe déjà (par ID ou nom pour éviter les doublons)
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE id = :id OR name = :name");
        $stmt->execute(['id' => $product['id'], 'name' => $product['name']]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Le produit n'existe pas, on l'insère
            $stmt = $db->prepare("
                INSERT INTO products (id, name, description, image_path, category)
                VALUES (:id, :name, :description, :image_path, :category)
            ");
            $stmt->execute([
                'id' => $product['id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'image_path' => $product['image_path'],
                'category' => $product['category']
            ]);
        }
    }

    // Récupérer les produits depuis la base de données
    $products = $db->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $products_error = "Unable to load products: " . $e->getMessage();
}

// Fetch testimonials
try {
    $testimonials = $db->query("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 3")->fetchAll();
} catch (PDOException $e) {
    $testimonials = [];
    $testimonials_error = "Unable to load testimonials: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Karma TN</title>
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
    <header class="header" id="header">
        <div class="header__border"></div>
        <nav class="nav container">
            <a href="index.php" class="nav__logo">
                <img src="assets/karma.jpg" alt="Logo Karma TN" /> Karma TN
            </a>
            <div class="nav__menu">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="index.php" class="nav__link active-link">
                            <i class="ri-home-5-fill"></i> <span>الصفحة الرئيسية</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="#about" class="nav__link">
                            <i class="ri-award-fill"></i> <span>حول</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="#collections" class="nav__link">
                            <i class="ri-store-fill"></i> <span>المجموعات</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="#testimonial" class="nav__link">
                            <i class="ri-message-3-fill"></i> <span>الشهادات</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="logout.php" class="nav__link">
                                <i class="ri-logout-box-fill"></i> <span>تسجيل الخروج</span>
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="nav__link">
                                <i class="ri-user-fill"></i> <span>تسجيل الدخول</span>
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <section class="home section" id="home">
        <div class="home__container container grid">
            <div class="home__data">
                <h2 class="home__subtitle">الأناقة والراحة</h2>
                <h1 class="home__title">اكتشف الموضة مع <img src="assets/karma.jpg" alt="Karma TN" /> Karma TN</h1>
                <p class="home__description">إكسسوارات عصرية وملابس ترفيهية لتعبر عن أسلوبك الفريد يوميًا.</p>
                <a href="#collections" class="button">استكشف مجموعاتنا <i class="ri-arrow-right-line"></i></a>
            </div>
            <img src="assets/karma.jpg" alt="Hero Image" class="home__img" />
        </div>
    </section>

    <section class="collections section" id="collections">
        <div class="collections__bg">
            <div class="collections__container container grid">
                <div class="collections__data">
                    <h2 class="section__title">معرفة المزيد</h2>
                    <p class="collections__description">إكسسوارات أنيقة وملابس ترفيهية تجمع بين الأناقة والراحة.</p>
                </div>
                <div class="collections__content">
                    <?php foreach ($products as $product): ?>
                        <div class="collections__card">
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="collections__img" />
                            <h3 class="collections__name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="collections__description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <a href="order2.php" product_id=<?php echo $product['id']; ?> class="button collections__button">شراء <i class="ri-shopping-cart-fill"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!empty($products_error)): ?>
                        <p class="error"><?php echo htmlspecialchars($products_error); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <section class="about" id="about">
            <div class="about__bg section">
                <div class="about__container container grid">
                    <div class="about__data">
                        <h2 class="section__title">رؤيتنا</h2>
                        <p class="about__description">
                            Karma TN، مقرها تونس، تقدم إكسسوارات وملابس ترفيهية تحتفي بالفردية والاتجاهات الحديثة.
                        </p>
                    </div>
                    <img src="assets/fashion.jpg" alt="about image" class="about__img" />
                </div>
            </div>
        </section>

        <section class="steps" id="steps">
            <div class="steps__bg section">
                <h2 class="section__title">أسلوبك، شغفنا</h2>
                <div class="steps__container container grid">
                    <img src="assets/img/fashion-bg.png" alt="steps image" class="steps__bg-img" />
                    <div class="steps__content">
                       
                        <div class="steps__card">
                            <div class="steps__circle">
                                <div class="steps__subcircle">01</div>
                                <img src="assets/casque.jpg" alt="steps image" class="steps__img" />
                            </div>
                            <p class="steps__description">
                                تصميم مستوحى من أحدث الاتجاهات.
                            </p>
                        </div>
                        <div class="steps__card steps__card-move">
                            <div class="steps__circle">
                                <div class="steps__subcircle">02</div>
                                <img src="assets/quality.jpg" alt="steps image" class="steps__img" />
                            </div>
                            <p class="steps__description">
                                مواد ذات جودة لراحة دائمة.
                            </p>
                        </div>
                        <div class="steps__card">
                            <div class="steps__circle">
                                <div class="steps__subcircle">03</div>
                                <img src="assets/delivery.jpg" alt="steps image" class="steps__img" />
                            </div>
                            <p class="steps__description">
                                توصيل سريع لأناقة فورية.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="testimonial" id="testimonial">
            <div class="testimonial__bg section">
                <div class="testimonial__container container grid">
                    <div class="testimonial__data">
                        <?php if (isset($testimonials_error)): ?>
                            <div class="error-message"><?= $testimonials_error ?></div>
                        <?php endif; ?>
                        <?php foreach ($testimonials as $index => $testimonial): ?>
                            <div class="testimonial__item <?= $index === 0 ? 'active' : '' ?>">
                                <h2 class="section__title"><?= htmlspecialchars($testimonial['content']) ?></h2>
                                <span class="testimonial__name"><?= htmlspecialchars($testimonial['author']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer__bg">
            <img src="assets/karma.jpg" alt="footer image" class="footer__bg-img" />
            <div class="footer__container container grid">
                <div class="footer__data grid">
                    <div>
                        <a href="index.php" class="footer__logo">
                            <img src="assets/karma.jpg" alt="Logo Karma TN" /> Karma TN
                        </a>
                        <h3 class="footer__title">اشترك في نشرتنا الإخبارية</h3>
                    </div>
                    <form action="newsletter.php" method="POST" class="footer__form grid" id="newsletterForm">
                        <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" class="footer__input" required />
                        <button class="button footer__button" type="submit">
                            الاشتراك <i class="ri-arrow-right-s-line"></i>
                        </button>
                        <p class="footer__description">
                            نحترم بياناتك. اقرأ
                            <a href="#" class="footer__privacy">سياسة الخصوصية</a>
                        </p>
                        <div id="newsletterMessage"></div>
                    </form>
                </div>
                <div class="footer__content grid">
                    <div class="footer__social">
                        <a href="https://www.facebook.com/profile.php?id=100080346387879" target="_blank" class="footer__social-link">
                            <i class="ri-facebook-fill"></i>
                        </a>
                        <a href="https://www.instagram.com/karma__tn/" target="_blank" class="footer__social-link">
                            <i class="ri-instagram-fill"></i>
                        </a>
                  
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <a href="#" class="scrollup" id="scroll-up">
        <i class="ri-arrow-up-line"></i>
    </a>

    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('newsletterMessage');
                messageDiv.innerHTML = data.message;
                messageDiv.style.color = data.success ? 'green' : 'red';
                if (data.success) this.reset();
            })
            .catch(error => {
                document.getElementById('newsletterMessage').innerHTML = 'حدث خطأ ما';
                document.getElementById('newsletterMessage').style.color = 'red';
            });
        });
    </script>
</body>
</html>

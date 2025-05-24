<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script>```php
<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error_message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
    } catch(PDOException $e) {
        $error_message = "حدث خطأ أثناء تسجيل الدخول: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>تسجيل الدخول - كارما TN</title>
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
    <header class="header" id="header">
        <div class="header__border"></div>
        <nav class="nav container">
            <a href="index.php" class="nav__logo">
                <img src="assets/karma.jpg" alt="شعار كارما TN" /> كارما TN
            </a>
            <div class="nav__menu">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="index.php" class="nav__link">
                            <i class="ri-home-5-fill"></i> <span>الرئيسية</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="index.php#about" class="nav__link">
                            <i class="ri-award-fill"></i> <span>من نحن</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="index.php#collections" class="nav__link">
                            <i class="ri-store-fill"></i> <span>المجموعات</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="index.php#testimonial" class="nav__link">
                            <i class="ri-message-3-fill"></i> <span>الشهادات</span>
                        </a>
                    </li>
                    <li class="nav__item">
                        <a href="register.php" class="nav__link">
                            <i class="ri-user-add-fill"></i> <span>التسجيل</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="login-main">
        <form id="loginForm" method="POST">
            <h1>تسجيل الدخول</h1>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" placeholder="أدخل بريدك الإلكتروني" required />
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required />
            </div>
            <button type="submit">تسجيل الدخول</button>
            <p class="form-link">
                ليس لديك حساب؟ <a href="register.php">سجّل الآن</a>
            </p>
        </form>
    </main>

    <footer class="footer">
        <div class="footer__bg">
            <img src="assets/karma.jpg" alt="صورة التذييل" class="footer__bg-img" />
            <div class="footer__container container grid">

                <div class="footer__data grid">
                    <div>
                        <a href="index.php" class="footer__logo">
                            <img src="assets/karma.jpg" alt="شعار كارما TN" /> كارما TN
                        </a>
                        <h3 class="footer__title">اشترك في نشرتنا الإخبارية</h3>
                    </div>
                    <form action="newsletter.php" method="POST" class="footer__form grid" id="newsletterForm">
                        <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" class="footer__input" required />
                        <button class="button footer__button" type="submit">
                            الاشتراك <i class="ri-arrow-right-s-line"></i>
                        </button>
                        <p class="footer__description">
                            نحن نحترم بياناتك. اقرأ
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
                        <a href="https://www.instagram.com/karma__tn" target="_blank" class="footer__social-link">
                            <i class="ri-instagram-fill"></i>
                        </a>
                
                    </div>
                </div>
            </div>
        </div>
    </footer>

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

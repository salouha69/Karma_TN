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
</script>

<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];

    // التحقق من البيانات
    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $error_message = "جميع الحقول مطلوبة.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "البريد الإلكتروني غير صالح.";
    } elseif (!preg_match('/^\+?\d{8,15}$/', $phone)) {
        $error_message = "رقم الهاتف غير صالح. استخدم الأرقام فقط (من 8 إلى 15 رقم، + اختيارية).";
    } elseif (strlen($password) < 6) {
        $error_message = "يجب أن تحتوي كلمة المرور على 6 أحرف على الأقل.";
    } else {
        try {
            // التحقق مما إذا كان البريد الإلكتروني أو اسم المستخدم أو رقم الهاتف مستخدم مسبقاً
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email OR username = :username OR phone = :phone");
            $stmt->execute([':email' => $email, ':username' => $username, ':phone' => $phone]);
            if ($stmt->fetch()) {
                $error_message = "البريد الإلكتروني أو اسم المستخدم أو رقم الهاتف مستخدم بالفعل.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO users (username, email, phone, password) 
                                     VALUES (:username, :email, :phone, :password)");
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':password' => $hashed_password
                ]);

                $user_id = $db->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                header('Location: index.php');
                exit;
            }
        } catch(PDOException $e) {
            $error_message = "حدث خطأ أثناء التسجيل: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>التسجيل - Karma TN</title>
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/styles.css" />
    <style>
        /* لجعل النص من اليمين لليسار */
        body {
            direction: rtl;
            text-align: right;
        }
        /* تعديل بعض التنسيقات لتناسب اتجاه RTL */
        .form-group label {
            float: right;
        }
    </style>
</head>
<body>
    <header class="header" id="header">
        <div class="header__border"></div>
        <nav class="nav container">
            <a href="index.php" class="nav__logo">
                <img src="assets/karma.jpg" alt="شعار Karma TN" /> Karma TN
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
                        <a href="login.php" class="nav__link">
                            <i class="ri-user-fill"></i> <span>تسجيل الدخول</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="register-main">
        <form id="registerForm" method="POST">
            <h1>التسجيل</h1>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">اسم المستخدم :</label>
                <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required />
            </div>
            <div class="form-group">
                <label for="email">البريد الإلكتروني :</label>
                <input type="email" id="email" name="email" placeholder="أدخل بريدك الإلكتروني" required />
            </div>
            <div class="form-group">
                <label for="phone">رقم الهاتف :</label>
                <input type="tel" id="phone" name="phone" placeholder="أدخل رقم هاتفك (مثال: +21612345678)" required />
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور :</label>
                <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required />
            </div>
            <button type="submit">تسجيل</button>
            <p class="form-link">
                هل لديك حساب؟ <a href="login.php">تسجيل الدخول</a>
            </p>
        </form>
    </main>

    <footer class="footer">
        <div class="footer__bg">
            <img src="assets/karma.jpg" alt="صورة في الفوتر" class="footer__bg-img" />
            <div class="footer__container container grid">
                <div class="footer__data grid">
                    <div>
                        <a href="index.php" class="footer__logo">
                            <img src="assets/karma.jpg" alt="شعار Karma TN" /> Karma TN
                        </a>
                        <h3 class="footer__title">اشترك في النشرة الإخبارية</h3>
                    </div>
                    <form action="newsletter.php" method="POST" class="footer__form grid" id="newsletterForm">
                        <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" class="footer__input" required />
                        <button class="button footer__button" type="submit">
                            الاشتراك <i class="ri-arrow-right-s-line"></i>
                        </button>
                        <p class="footer__description">
                            نحن نحترم خصوصيتك. اقرأ
                            <a href="#" class="footer__privacy">سياسة الخصوصية</a>
                        </p>
                        <div id="newsletterMessage"></div>
                    </form>
                </div>
                <div class="footer__content grid">
                    <div class="footer__social">
                        <a href="https://www.facebook.com/karmatn" target="_blank" class="footer__social-link">
                            <i class="ri-facebook-fill"></i>
                        </a>
                        <a href="https://www.instagram.com/karma__tn" target="_blank" class="footer__social-link">
                            <i class="ri-instagram-fill"></i>
                        </a>
                        <a href="https://twitter.com/karmatn" target="_blank" class="footer__social-link">
                            <i class="ri-twitter-fill"></i>
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
                document.getElementById('newsletterMessage').innerHTML = 'حدث خطأ';
                document.getElementById('newsletterMessage').style.color = 'red';
            });
        });
    </script>
</body>
</html>


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

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// جلب المنتجات
try {
    $products = $db->query("SELECT * FROM products")->fetchAll();
} catch(PDOException $e) {
    $products = [];
    $error_message = "غير قادر على تحميل المنتجات: " . $e->getMessage();
}

// معالجة الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = sanitize($_POST['product_id']);
    $quantity = sanitize($_POST['quantity']);
    $total_price = sanitize($_POST['total_price']);
    $user_id = $_SESSION['user_id'];

    // التحقق من صحة البيانات
    if (empty($product_id) || $quantity <= 0 || $total_price <= 0) {
        $error_message = "يرجى اختيار منتج وكمية صحيحة.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price) 
                                 VALUES (:user_id, :product_id, :quantity, :total_price)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':total_price' => $total_price
            ]);

            $order_id = $db->lastInsertId();
            $success_message = "تم تقديم طلبك رقم #$order_id بنجاح!";
        } catch(PDOException $e) {
            $error_message = "خطأ أثناء تسجيل الطلب: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>تقديم طلب - Karma TN</title>
    <link rel="shortcut icon" href="assets/karma.jpg" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/styles.css" />
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
                        <a href="logout.php" class="nav__link">
                            <i class="ri-logout-box-fill"></i> <span>تسجيل الخروج</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="order-main">
        <form id="orderForm" method="POST">
            <h1>تقديم طلب</h1>
            <?php if (isset($success_message)): ?>
                <div class="order-confirmation"><?= htmlspecialchars($success_message) ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="product">المنتج:</label>
                <select id="product" name="product_id" required>
                    <option value="">اختر منتجاً</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" data-price="<?= $product['price'] ?>">
                            <?= htmlspecialchars($product['name']) ?> (<?= number_format($product['price'], 2) ?> دينار تونسي)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">الكمية:</label>
                <input type="number" id="quantity" name="quantity" min="1" value="1" max="3" required>
            </div>
            <div class="total-price" id="totalPrice">المجموع: 7.00 دينار تونسي</div>
            <input type="hidden" id="formTotalPrice" name="total_price" value="0.00" />
            <button type="submit" id="orderButton" disabled>إرسال الطلب</button>
        </form>
    </main>

    <footer class="footer">
        <div class="footer__bg">
            <img src="assets/karma.jpg" alt="صورة تذييل الصفحة" class="footer__bg-img" />
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
                            اشترك <i class="ri-arrow-right-s-line"></i>
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

        const productSelect = document.getElementById('product');
        const quantityInput = document.getElementById('quantity');
        const totalPriceDiv = document.getElementById('totalPrice');
        const formTotalPrice = document.getElementById('formTotalPrice');
        const orderButton = document.getElementById('orderButton');

        function updateTotalPrice() {
            const price = parseFloat(productSelect.selectedOptions[0].dataset.price) || 0;
            const quantity = parseInt(quantityInput.value) || 1;
            const total =  ((price * quantity)+7).toFixed(2);
            totalPriceDiv.textContent = `المجموع: ${total} دينار تونسي`;
            formTotalPrice.value = total;
            orderButton.disabled = !productSelect.value || quantity <= 0;
        }

        productSelect.addEventListener('change', updateTotalPrice);
        quantityInput.addEventListener('input', updateTotalPrice);
    </script>
</body>
</html>


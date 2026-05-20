<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Доставка</title>
    <link rel="stylesheet" href="stylesite.css?v=1600">
</head>

<body>

<header class="header">
    <div class="logo">
        <h1>Книжный мир</h1>
        <p>Открой книгу — открой мир</p>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="favorites.php">Избранное</a></li>
            <li><a href="cart.php">Корзина</a></li>
            <li><a href="delivery.php">Доставка</a></li>

            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php">Личный кабинет</a></li>
                <li><a href="logout.php">Выход</a></li>
            <?php else: ?>
                <li><a href="login.php">Вход</a></li>
                <li><a href="register.php">Регистрация</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="main-content">

    <section class="banner">
        <div class="banner-text">
            <h2>Доставка</h2>
        </div>
    </section>

    <section class="delivery-page">

        <div class="delivery-card">
            <h3>Курьерская доставка</h3>
            <p>Доставка по городу прямо до двери.</p>
            <p><strong>Срок:</strong> 1–2 дня</p>
            <p><strong>Стоимость:</strong> 250 ₽</p>
        </div>

        <div class="delivery-card">
            <h3>Пункт выдачи</h3>
            <p>Заберите заказ в удобном пункте выдачи.</p>
            <p><strong>Срок:</strong> 2–4 дня</p>
            <p><strong>Стоимость:</strong> 150 ₽</p>
        </div>

        <div class="delivery-card">
            <h3>Почта России</h3>
            <p>Доставка доступна в любой регион.</p>
            <p><strong>Срок:</strong> 5–10 дней</p>
            <p><strong>Стоимость:</strong> от 300 ₽</p>
        </div>

    </section>

    <section class="delivery-info">
        <h3>Как оформить доставку?</h3>
        <p>Добавьте книги в корзину, нажмите «Купить», затем укажите удобный способ получения заказа.</p>
    </section>

</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section about-section">
            <h3>О нас</h3>
            <p>Книжный магазин с 2010 года</p>
            <p class="copyright">© 2025 Книжный мир. Все права защищены.</p>
        </div>

        <div class="footer-section contacts-section">
            <h3>Контакты</h3>
            <p>Горячая линия: 8-800-123-45-67</p>
            <p>Email: KnizhnyMir@mail.ru</p>
        </div>
    </div>
</footer>

</body>
</html>
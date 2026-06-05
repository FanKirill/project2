<?php
// index.php – главная страница с анкетой разработчика (задание 8)
session_start();
require_once 'includes/functions.php';

// Если пользователь авторизован, загружаем его данные для заполнения формы
$form_values = [];
if (isset($_SESSION['user_id']) && isset($_SESSION['application_id'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$_SESSION['application_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userData) {
        $stmtLang = $pdo->prepare("SELECT pl.name FROM application_languages al JOIN programming_languages pl ON al.language_id = pl.id WHERE al.application_id = ?");
        $stmtLang->execute([$_SESSION['application_id']]);
        $langs = $stmtLang->fetchAll(PDO::FETCH_COLUMN);
        $form_values = [
            'fullname' => $userData['fullname'],
            'phone' => $userData['phone'],
            'email' => $userData['email'],
            'birthdate' => $userData['birthdate'],
            'gender' => $userData['gender'],
            'fav_langs' => $langs,
            'bio' => $userData['biography'],
            'contract_agreed' => $userData['contract_agreed'] ? 'on' : ''
        ];
    }
} else {
    // Если не авторизован, пробуем взять из кук (сохранённые данные)
    if (isset($_COOKIE['saved_data'])) {
        $saved = json_decode($_COOKIE['saved_data'], true);
        if (is_array($saved)) $form_values = $saved;
    }
}
$flash = '';
if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поддержка сайтов на Drupal | Drupal-coder</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <link rel="stylesheet" href="css/shrift/import.css">
    <link rel="stylesheet" href="css/desktop/additional.css">
    <link rel="stylesheet" href="css/desktop/header-block.css">
    <link rel="stylesheet" href="css/desktop/services-block.css">
    <link rel="stylesheet" href="css/desktop/support-block.css">
    <link rel="stylesheet" href="css/desktop/plan-block.css">
    <link rel="stylesheet" href="css/desktop/team-block.css">
    <link rel="stylesheet" href="css/desktop/cases-block.css">
    <link rel="stylesheet" href="css/desktop/reviews-block.css">
    <link rel="stylesheet" href="css/desktop/partners-block.css">
    <link rel="stylesheet" href="css/desktop/faq-block.css">
    <link rel="stylesheet" href="css/desktop/webform-block.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/accordion.js"></script>
    <style>
        /* Дополнительные стили для формы анкеты */
        .webform-block_form .form-group select,
        .webform-block_form .form-group input[type="date"],
        .webform-block_form .form-group textarea {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 0.5rem;
            background: white;
        }
        .radio-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.3rem;
        }
        .radio-group label {
            font-weight: normal;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        select[multiple] {
            min-height: 120px;
        }
        .login-status {
            text-align: right;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background: #f5f5f5;
            border-radius: 2rem;
            font-size: 0.9rem;
        }
        .api-message {
            margin-bottom: 1rem;
            padding: 0.8rem;
            border-radius: 0.5rem;
        }
        .api-success { background: #d4edda; color: #155724; }
        .api-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <section class="header-block">
        <div class="header-block_container" >
            <div class="header-block_all">
                <div class="header-block_panel">
                    <div class="header-block_logo">
                        <a href="#"><img src="materials/img/drupal-coder.svg" alt="logo.png" width="200px"></a>
                    </div>
                    <div class="header-block_nav">
                        <nav>
                            <ul>
                                <li><a href="#">ПОДДЕРЖКА DURPAL</a></li>
                                <li>
                                    <div class="dropdown">
                                        <a href="" class="dropbtn">АДМИНИСТРИРОВНИЕ</a>
                                        <div class="dropdown-content">
                                            <a href="#">МИГРАЦИЯ</a>
                                            <a href="#">БЭКАПЫ</a>
                                            <a href="#">АУДИТ БЕЗОПАСНОСТИ</a>
                                            <a href="#">ОПТИМИЗАЦИЯ СКОРОСТИ</a>
                                            <a href="#">ПЕРЕЕЗД НА HTTPS</a>
                                        </div>
                                    </div>
                                </li>
                                <li><a href="#">ПРОДВИЖЕНИЕ</a></li>
                                <li><a href="#">РЕКЛАМА</a></li>
                                <li><a href="#">О НАС</a></li>
                                <li><a href="#">ПРОЕКТЫ</a></li>
                                <li><a href="#">КОНТАКТЫ</a></li>
                                <li><a href="index.php">Анкета</a></li>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <li><a href="logout.php">Выйти (<?= htmlspecialchars($_SESSION['login'] ?? '') ?>)</a></li>
                                <?php else: ?>
                                    <li><a href="login.php">Войти</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="header-block_main">
                    <div class="header-block_title-text-link">
                        <div class="header-block_title"><h1>Поддержка <br> сайтов на Drupal</h1></div>
                        <div class="header-block_text"><p>Сопровождение и поддержка сайтов <br>на CMS Drupal любых версий и запущенности</p></div>
                        <a href="#" class="header-block_link">ТАРИФЫ</a>
                    </div>
                    <div class="header-block_about-all">
                        <div class="header-block_about">
                            <div class="header-block_one">
                                <div class="header-block_a">
                                    <div class="header-block_vertical-line-one-group">
                                        <div class="header-block_vertical-line-one"></div>
                                        <div class="header-block_vertical-line-one_img-text"><h1 style="font-size: 60px;">#1</h1><img src="materials/img/cup.png" alt="cup.png" class="img-header"><br></div>
                                        <div class="header-block_vertical-line-one_text"><p>Drupal-разработчик<br>в России по версии<br>Рейтинга Рунета</p></div>
                                    </div>
                                </div>
                                <div class="header-block_b">
                                    <div class="header-block_vertical-line-two-group">
                                        <div class="header-block_vertical-line-two"></div>
                                        <div class="header-block_vertical-line-two_text-1"><h1 style="font-size: 40px;">3+</h1><br></div>
                                        <div class="header-block_vertical-line-two_text-2"><p>средний опыт<br>специалистов более<br>3 лет</p></div>
                                    </div>
                                </div>
                                <div class="header-block_c">
                                    <div class="header-block_vertical-line-three-group">
                                        <div class="header-block_vertical-line-three"></div>
                                        <div class="header-block_vertical-line-three_text-1"><h1 style="font-size: 40px;">14</h1><br></div>
                                        <div class="header-block_vertical-line-three_text-2"><p>лет опыта в сфере<br>Drupal</p></div>
                                    </div>
                                </div>
                            </div>
                            <div class="header-block_two">
                                <div class="header-block_d">
                                    <div class="header-block_vertical-line-four-group">
                                        <div class="header-block_vertical-line-four"></div>
                                        <div class="header-block_vertical-line-four_text-1"><h1 style="font-size: 40px;">200+</h1></div>
                                        <div class="header-block_vertical-line-four_text-2"><p>модулейи тем<br>в формате DrupalGive</p></div>
                                    </div>
                                </div>
                                <div class="header-block_e">
                                    <div class="header-block_vertical-line-five-group">
                                        <div class="header-block_vertical-line-five"></div>
                                        <div class="header-block_vertical-line-five_text-1"><h1 style="font-size: 40px;">35 000</h1><br></div>
                                        <div class="header-block_vertical-line-five_text-2"><p>часов поддержки<br>сайтов на Drupal</p></div>
                                    </div>
                                </div>
                                <div class="header-block_f">
                                    <div class="header-block_vertical-line-six-group">
                                        <div class="header-block_vertical-line-six"></div>
                                        <div class="header-block_vertical-line-six_text-1"><h1 style="font-size: 50px;">200+</h1><br></div>
                                        <div class="header-block_vertical-line-six_text-2"><p>Проектов<br>на поддержке</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <video autoplay muted loop playsinline preload="auto" class="header-block_video">
            <source type="video/mp4" src="materials/img/video.mp4">
        </video>
    </section>

    <section>
        <div class="services-block">
            <div class="services-block_text">
                <div><h1 class="all-group-text">13  лет совершенствуем<br>компетенции в Drupal<br>поддержке!</h1></div>
                <div><p class="services-block_text-p" >Разрабатываем и оптимизируем модули, расширяем<br>функциональность сайтов, обновляем дизайн</p></div>
            </div>
            <div class="services-block_adv2">
                <div class="services-block_adv2-line-one">
                    <div class="services-block_adv2-line-one_text-icon2-1"><img src="materials/img/competency-1.svg" alt=""><p style="width: 200px;">Добавление информации на сайт, создание новых разделов</p></div>
                    <div class="services-block_adv2-line-one_text-icon2-2"><img src="materials/img/competency-2.svg" alt=""><p style="width: 200px;">Разработка и оптимизация модулей сайта</p></div>
                    <div class="services-block_adv2-line-one_text-icon2-3"><img src="materials/img/competency-3.svg" alt=""><p style="width: 180px;">Интеграция с CRM, 1C, платежными системами, любыми веб-сервисами</p></div>
                    <div class="services-block_adv2-line-one_text-icon2-4"><img src="materials/img/competency-4.svg" alt=""><p style="width: 200px;">Любые доработки функционала и дизайна</p></div>
                </div>
                <div class="services-block_adv2-line-two">
                    <div class="services-block_adv2-line-one_text-icon2-5"><img src="materials/img/competency-5.svg" alt=""><p style="width: 200px;">Аудит и мониторинг безопасности Drupal сайтов</p></div>
                    <div class="services-block_adv2-line-one_text-icon2-6"><img src="materials/img/competency-6.svg" alt=""><p style="width: 200px;">Миграция, импорт контента и апгрейд Drupal</p></div>
                    <div class="services-block_adv2-line-one_text-icon2-7"><img src="materials/img/competency-7.svg" alt=""><p style="width: 180px;">Оптимизация и ускорение Drupal-сайтов</p></div>
                    <div class="services-block_adv2-line-one_text-icon2-8"><img src="materials/img/competency-8.svg" alt=""><p style="width: 200px;">Веб-маркетинг, консультации и работы по SEO</p></div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="support-block">
            <h1 class="all-group-text" style="text-align: center; margin-bottom: 6rem;">Поддрежка <br>от Drupal-coder</h1>
            <div class="support-block_group-line">
                <div class="support-block_group-line-one">
                    <div class="support-block_ support-block_group-line-one_text-icon-support1"><p><span class="support-block_group-number"><span>01.</span></span></p><b class="support-block_group-b">Постановка задачи по Email</b><p style="margin-bottom: 20px;"><span class="support-block_group-p">Удобная и привычная модель постановки задач, при которой задачи фиксируются и никогда не теряются.</span></p><img src="materials/img/support1.svg" alt=""></div> 
                    <div class="support-block_ support-block_group-line-one_text-icon-support2"><p><span class="support-block_group-number">02.</span></p><b class="support-block_group-b">Система Helpdesk – отчетность, прозрачность</b><p style="margin-bottom: 20px;"><span class="support-block_group-p">Возможность посмотреть все заявки в работе и отработанные часы в личном кабинете через браузер.</span></p><img src="materials/img/support2.svg" alt="" ></div>
                    <div class="support-block_ support-block_group-line-one_text-icon-support3"><p><span class="support-block_group-number">03.</span></p><b class="support-block_group-b">Расширенная техническая поддержка</b><p style="margin-bottom: 35px;"><span class="support-block_group-p">Возможность организации расширенной техподдержки с 6:00 до 22:00 без выходных.</span></p><img src="materials/img/support3.svg" alt=""></div>
                    <div class="support-block_ support-block_group-line-one_text-icon-support4"><p><span class="support-block_group-number">04.</span></p><b class="support-block_group-b">Персональный менеджер проекта</b><p><span class="support-block_group-p">Ваш менеджер проекта  всегда в курсе текущего состояния проекта и в любой момент готов ответить на любые вопросы.</span></p><img src="materials/img/support4.svg" alt=""></div>
                </div>
                <div class="support-block_group-line-two">
                    <div class="support-block_ support-block_group-line-two_text-icon-support5"><p><span class="support-block_group-number">05.</span></p><b class="support-block_group-b">Удобные способы оплаты</b><p style="margin-bottom: 20px;"><span class="support-block_group-p">Безналичный расчет по договору или электронные деньги: WebMoney, Яндекс.Деньги, Paypal.</span></p><img src="materials/img/support5.svg" alt=""></div>
                    <div class="support-block_ support-block_group-line-two_text-icon-support6"><p><span class="support-block_group-number">06.</span></p><b class="support-block_group-b">Работаем с SLA и NDA</b><p style="margin-bottom: 50px;"><span class="support-block_group-p">Работа в рамках соглашений о конфиденциальности и об уровне качетсва работ.</span></p><img src="materials/img/support6.svg" alt=""></div>
                    <div class="support-block_ support-block_group-line-two_text-icon-support7"><p><span class="support-block_group-number">07.</span></p><b class="support-block_group-b">Штатные специалисты</b><p style="margin-bottom: 50px;"><span class="support-block_group-p">Надежные штатные специалисты, никаких фрилансеров.</span></p><img src="materials/img/support7.svg" alt=""></div>
                    <div class="support-block_ support-block_group-line-two_text-icon-support8"><p><span class="support-block_group-number">08.</span></p><b class="support-block_group-b">Удобные каналы связи</b><p style="margin-bottom: 75px;"><span class="support-block_group-p">Консультации по телефону, скайпу, в месенджерах.</span></p><img src="materials/img/support8.svg" alt=""></div>
                </div>
            </div>
        </div>
        <div class="services-block_background">
            <div class="services-block_background-img">
                <img src="materials/img/laptop.png" alt="" class="img">
            </div>
            <div class="services-block_background-text">
                <h1 class="services-block_background-h1">Экспертиза в Drupal,<br>опыт 14 лет!</h1>
                <div class="services-block_background-group-main">
                    <div class="services-block_background-group1">
                        <div class="services-block_background-vertical-line-one"></div> 
                        <div class="services-block_background-vertical-line-one_text"><p>Только системный подход – контроль версий, резервированиеи тестирование!</p></div>
                    </div>
                    <div class="services-block_background-group2">
                        <div class="services-block_background-vertical-line-two"></div>
                        <div class="services-block_background-vertical-line-two_text"><p>Только Drupal сайты, не берем на поддержку сайты на других CMS!</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="plan-block">
            <h1 class="all-group-text" style="text-align: center; margin-bottom: 5rem;">Тарифы</h1>
            <div class="plan-block_card-block">
                <div class="plan-block_card-block_card1">
                    <div class="card1-content">
                        <p><span class="plan-block_card-block-ph1">Стартовый</span><br><span class="plan-block_card-block-ph2">2000<span class="plan-block_card-block-ph3">₽ </span><span class="plan-block_card-block-ph4">в час</span></span></p>
                        <hr>
                        <div class="plan-block_card-block_card1-text">
                            <ul>
                                <li><p>Предоплата от 2 часов</p></li>
                                <li><p>Консультации и работы по SEO</p></li>
                                <li><p>Услуги дизайнера</p></li>
                                <li><p>Стандартное время реакции</p></li>
                                <li><p>Неиспользованные оплаченные часы переносятся на следующий месяц</p></li>
                            </ul>
                        </div>
                        <div class="plan-block_link-group"><a href="#" class="plan-block_link">СВЯЖИТЕСЬ С НАМИ!</a></div>
                    </div>
                </div>
                <div class="plan-block_card-block_card2">
                    <div class="card2-content">
                        <p><span class="plan-block_card-block-ph1">Бизнес</span><br><span class="plan-block_card-block-ph2">2500<span class="plan-block_card-block-ph3">₽ </span><span class="plan-block_card-block-ph4">в час</span></span></p>
                        <hr>
                        <div class="plan-block_card-block_card2-text">
                            <ul>
                                <li><p>Предоплата от 2 часов</p></li>
                                <li><p>Консультации и работы по SEO</p></li>
                                <li><p>Услуги дизайнера</p></li>
                                <li><p>Стандартное время реакции</p></li>
                                <li><p>Неиспользованные оплаченные часы переносятся на следующий месяц</p></li>
                            </ul>
                        </div>
                        <div class="plan-block_link-group"><a href="#" class="plan-block_link">СВЯЖИТЕСЬ С НАМИ!</a></div>
                    </div>
                </div>
                <div class="plan-block_card-block_card3">
                    <div class="card3-content">
                        <p><span class="plan-block_card-block-ph1">VIP</span><br><span class="plan-block_card-block-ph2">3000<span class="plan-block_card-block-ph3">₽ </span><span class="plan-block_card-block-ph4">в час</span></span></p>
                        <hr>
                        <div class="plan-block_card-block_card3-text">
                            <ul>
                                <li><p>Предоплата от 2 часов</p></li>
                                <li><p>Консультации и работы по SEO</p></li>
                                <li><p>Услуги дизайнера</p></li>
                                <li><p>Стандартное время реакции</p></li>
                                <li><p>Неиспользованные оплаченные часы переносятся на следующий месяц</p></li>
                            </ul>
                        </div>
                        <div class="plan-block_link-group"><a href="#" class="plan-block_link">СВЯЖИТЕСЬ С НАМИ!</a></div>
                    </div>
                </div>
            </div>
            <div class="plan-block_text">
                <p class="plan-block_text-p">Вам не подходят наши тарифы? Оставьте заявку и мы<br>предложим вам индивидуальные условия!</p>
                <br>
                <a href="#" class="plan-block_text-a">ПОЛУЧИТЬ ИНДИВИДУАЛЬНЫЙ ТАРИФ</a>
            </div>
            <div class="plan-block_about">
                <div class="plan-block_about-group-text"><h1 class="all-group-text" style="margin-bottom: 5rem;">Наши профессиональные разработчики <br>выполняют быстро любые задачи</h1></div>
                <div class="plan-block_about-group">
                    <div class="plan-block_about-group-1"><img src="materials/img/competency-20.svg" alt="" class="plan-block_about-img"><p><span class="plan-block_about-h1">от 1ч</span><br><span class="plan-block_about-p">Настройка события GA в интернет-магазине</span></p></div>
                    <div class="plan-block_about-group-2"><img src="materials/img/competency-21.svg" alt="" class="plan-block_about-img"><p><span class="plan-block_about-h1">от 20ч</span><br><span class="plan-block_about-p">Разработка мобильной версии сайта</span></p></div>
                    <div class="plan-block_about-group-3"><img src="materials/img/competency-21.svg" alt="" class="plan-block_about-img"><p><span class="plan-block_about-h1">от 8ч</span><br><span class="plan-block_about-p">Интеграция модуля оплаты</span></p></div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="team-block">
            <div><h1 class="all-group-text" style="text-align: center;">Команда</h1></div>
            <div class="team-block_group">
                <div class="team-block_group-line-one">
                    <div class="team-block_group-line-one_text-img"><img src="materials/img/IMG_2472_0.jpg" alt=""><br><p><span class="team-block_text-h1">Сергей Синица</span><br><span class="team-block_text-p">Руководитель отдела веб- <br>разработки, канд. техн. наук, <br>заместитель директора</span></p></div>
                    <div class="team-block_group-line-one_text-img"><img src="materials/img/IMG_2539_0.jpg" alt=""><br><span class="team-block_text-h1">Роман Агабеков</span><br><span class="team-block_text-p">Руководитель отдела DevOPS, директор</span></div>
                    <div class="team-block_group-line-one_text-img"><img src="materials/img/IMG_2474_1.jpg" alt=""><br><span class="team-block_text-h1">Алексей Синица</span><br><span class="team-block_text-p">Руководитель отдела поддержки сайтов</span></div>
                </div>
                <div class="team-block_group-line-two">
                    <div class="team-block_group-line-one_text-img"><img src="materials/img/IMG_2522_0.jpg" alt=""><br><span class="team-block_text-h1">Дарья Бочкарёва</span><br><span class="team-block_text-p">Руководитель отдела продвижения контекстной рекламы и контент-поддрежки сайтов</span></div>
                    <div class="team-block_group-line-one_text-img"><img src="materials/img/IMG_9971_16.jpg" alt=""><br><span class="team-block_text-h1">Ирина Торкунова</span><br><span class="team-block_text-p">Менеджер по работе с клиентами</span></div>
                </div>
            </div> 
        </div>
    </section>

    <section>
        <div class="cases-block">
            <h1 class="all-group-text" style="text-align: center; margin-bottom: 2rem;">Последние кейсы</h1>
            <div class="cases-block_group">
                <div class="cases-block_group-card1-line-one">
                    <div class="cases-block_group-card1"><img src="materials/case/1.jpg" alt="" class="img1"><p><span class="g">Найстройка кэширования <br>даных. Апгрейд сервера.<br> Ускорение работы сайта в <br>30 раз!</span><br><span class="case-date">04.05.2020</span><br><span class="g1">Влияние скорости загрузки страниц<br>сайта отказы и конверсии. Кейс<br>ускорения...</span></p></div>
                    <div class="cases-block_group-card2"><img src="materials/case/3.jpg" alt="" class="img3"><div class="text-overlay"><p><span class="hh">Использование отчетов Ecommerce <br> в Яндекс.Марките</span></p></div></div>
                </div>
                <div class="cases-block_group-card1-line-two">
                    <div class="cases-block_group-card3"><img src="materials/case/3.jpg" alt="" class="img3"><div class="text-overlay"><p><span class="h1">Повышение конверсии<br>страниц с формой заявки<br>с применением AB- <br>тестирования<br></span><span class="case-date">24.01.20</span></p></div></div>
                    <div class="cases-block_group-card4"><img src="materials/case/4.jpg" alt="" class="img4"><p><span span class="g">Обмен товарами и заказами <br>интернет-магазинов на Drupal 7<br> с 1С: Предприятие, МойСклад,<br> Класс365 <br> </span><span class="case-date">22.08.2О19</span><br><br><span class="h1">Опубликован <a href="#" style="text-decoration: none;">релиз модуля...</a></span></p></div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="reviews-block">
            <h1 class="all-group-text" style="text-align: center; margin-bottom: 2rem;">Отзывы</h1>
            <div class="reviews-block_slaider">
                <div class="slider-gallery">
                    <div>
                        <div class="slide-content">
                            <img src="materials/img/logo_0.png" alt="Изображение 1" class="img2">
                            <p>Долгие поиски единственного и неповторимого мастера на многострадальный сайт www.cielparfum.com, который был собран крайне некомпетентным программистом и раз в месяц стабильно грозил погибнуть, привели меня на сайт и, в итоге, к ребятам из Drupal-coder. И вот уже практически полгода как не проходит и дня, чтобы я не поудивлялась и не порадовалась своему везению! Починили все, что не работало от поиска до отображения меню. Провели редизайн - не отходя от желаемого, но со своими существенными и качественными дополнениями. Осуществили ряд проектов конкурсы, тесты и тд. А уж мелких починок и доработок не счесть! И главное все качественно и быстро (не взирая на не самый "быстрый" тариф). Есть вопросы замечательный Алексей всегда подскажет, поддержит, отремонтирует и/или просто сделает с нуля. Есть задумка для реализации замечательный Сергей обсудит и предложит идеальный вариант. Есть проблема - замечательные Надежда и Роман починят, поправят, сделают! Ребята доказали, что эта СМЅ мощная и грамотная система управления. Надеюсь, что наше сотрудничество затянется надолго! Спасибо!!!</p>
                            <br>
                            <p style="font-weight: 300;">С уважением, Наталья Сушкова руководитель Отдела веб-проектов Группы компаний «Си Эль парфюм» <a href="" style="text-decoration: none;">http://www.cielparfum.com/</a></p>
                        </div>
                    </div>
                    <div><div class="slide-content"><img src="materials/img/img.jpeg" alt="Изображение 2"><p>Текст для изображения 2</p></div></div>
                    <div><div class="slide-content"><img src="materials/img/java2.jpeg" alt="Изображение 3"><p>Текст для изображения 3</p></div></div>
                    <div><div class="slide-content"><img src="materials/img/java3.jpeg" alt="Изображение 4"><p>Текст для изображения 4</p></div></div>
                    <div><div class="slide-content"><img src="materials/img/java4.jpeg" alt="Изображение 5"><p>Текст для изображения 5</p></div></div>
                    <div><div class="slide-content"><img src="materials/img/java5.jpeg" alt="Изображение 6"><p>Текст для изображения 6</p></div></div>
                    <div><div class="slide-content"><img src="materials/img/java6.jpeg" alt="Изображение 7"><p>Текст для изображения 7</p></div></div>
                    <div><div class="slide-content"><img src="materials/img/java7.jpeg" alt="Изображение 8"><p>Текст для изображения 8</p></div></div>
                </div>
                <hr style="margin-top: 3rem; margin-bottom: 3rem;">
                <div class="slider-page-info"></div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
            <script src="js/slider.js"></script>
        </div>
    </section>

    <section>
        <div class="partners-block">
            <div class="partners-block_text">
                <h1 class="all-group-text">С нами работают</h1>
                <p class="partners-block_text-p">Десятки компаний доверяют нам самое ценное, что у них есть в интернете – свои <br>согласование сайты. Мы делаем всё, чтобы наше сотрудничество было долгим.</p>
            </div>
            <div class="partners-block_img-group">
                <div class="partners-block_img-group-one">
                    <img src="materials/img/logo-estee.png" alt="Изображение 1" class="marquee-image">
                    <img src="materials/img/logo.png" alt="Изображение 2" class="marquee-image">
                    <img src="materials/img/logo_0.png" alt="Изображение 3" class="marquee-image">
                </div>
                <div class="partners-block_img-group-two">
                    <img src="materials/img/logo_2.png" alt="Изображение 4" class="marquee-image">
                    <img src="materials/img/lpcma_rus_v4.jpg" alt="Изображение 5" class="marquee-image">
                    <img src="materials/img/nashagazeta_ch.png" alt="Изображение 6" class="marquee-image">
                </div>
            </div>
        </div>
    </section>

    <section>
        <h1 style="color: #050c33; font-family: Montserrat, sans-serif; font-weight: 700; font-size: 42px; text-align: center;margin-bottom: 6rem;">FAQ</h1>
        <div class="faq-block">
            <div class="faq-section">
                <div class="faq-item"><div class="faq-question"><span class="faq-number">1.</span> Кто непосредственно занимается поддержкой?</div><div class="faq-answer">Ответ на первый вопрос.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">2.</span> Как организована работа поддержки?</div><div class="faq-answer">Ответ на второй вопрос.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">3.</span> Что происходит, когда отработаны все предоплаченные часы за месяц?</div><div class="faq-answer">Ответ.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">4.</span> Что происходит, когда не отработаны все предоплаченные часы за месяц?</div><div class="faq-answer">Ответ.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">5.</span> Как происходит оценка и согласование планируемого времени на выполнение заявок?</div><div class="faq-answer">Ответ.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">6.</span> Сколько программистов выделяется на проект?</div><div class="faq-answer">Ответ.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">7.</span> Как подать заявку на внесение изменений на сайте?</div><div class="faq-answer">Ответ.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">8.</span> В течение какого времени начинается работа по заявке?</div><div class="faq-answer">Ответ.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">9.</span> В какое время работает поддержка?</div><div class="faq-answer">Ответ.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">10.</span> Подходят ли услуги поддержки, если необходимо произвести обновление ядра Drupal или модулей?</div><div class="faq-answer">Ответ.</div></div>
                <div class="faq-item"><div class="faq-question"><span class="faq-number">11.</span> Можно ли пообщаться со специалистом голосом или в мессенджере?</div><div class="faq-answer">Ответ.</div></div>
            </div>
        </div>
    </section>

    <section>
        <div class="webform-block">
            <div class="webform-block_text-contact-form">
                <div class="webform-block_text-contact">
                    <div class="webform-block_text">
                        <h1 class="webform-block_text-h1">Анкета разработчика</h1>
                        <p class="webform-block_text-p">Заполните профиль – после сохранения вы получите логин и пароль для редактирования своих данных.</p>
                    </div>
                    <div class="webform-block_contact">
                        <p class="webform-block_contact-p">8 800 222-26-73</p>
                        <a href="#" class="webform-block_contact-a">info@drupal-coder.ru</a>
                    </div>
                </div>
                <div class="webform-bloc_feedback-form-and-showMessage">
                    <div class="webform-block_form" id="popup-container">
                        <?php if ($flash): ?>
                            <div class="api-message api-success"><?= htmlspecialchars($flash) ?></div>
                        <?php endif; ?>
                        <div id="apiMessage"></div>
                        <form id="devForm" method="POST" action="handler.php">
                            <div class="form-block">
                                <div class="form-group">
                                    <label for="fullname">ФИО *</label>
                                    <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($form_values['fullname'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Телефон</label>
                                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($form_values['phone'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="email">E-mail *</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($form_values['email'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="birthdate">Дата рождения</label>
                                    <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($form_values['birthdate'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Пол</label>
                                    <div class="radio-group">
                                        <label><input type="radio" name="gender" value="male" <?= (($form_values['gender'] ?? '') == 'male') ? 'checked' : '' ?>> Мужской</label>
                                        <label><input type="radio" name="gender" value="female" <?= (($form_values['gender'] ?? '') == 'female') ? 'checked' : '' ?>> Женский</label>
                                        <label><input type="radio" name="gender" value="other" <?= (($form_values['gender'] ?? '') == 'other') ? 'checked' : '' ?>> Другой</label>
                                        <label><input type="radio" name="gender" value="unspecified" <?= (!isset($form_values['gender']) || $form_values['gender'] == 'unspecified') ? 'checked' : '' ?>> Не указан</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="fav_langs">Любимые языки программирования *</label>
                                    <select name="fav_langs[]" id="fav_langs" multiple size="6" required>
                                        <?php $allLangs = ['Pascal','C','C++','JavaScript','PHP','Python','Java','Haskell','Clojure','Prolog','Scala','Go']; ?>
                                        <?php foreach ($allLangs as $lang): ?>
                                            <option value="<?= $lang ?>" <?= (isset($form_values['fav_langs']) && in_array($lang, $form_values['fav_langs'])) ? 'selected' : '' ?>><?= $lang ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small>Удерживайте Ctrl для выбора нескольких</small>
                                </div>
                                <div class="form-group">
                                    <label for="bio">Биография</label>
                                    <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($form_values['bio'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group agree-group">
                                    <input type="checkbox" id="contract" name="contract_agreed" value="on" <?= (isset($form_values['contract_agreed']) && $form_values['contract_agreed'] == 'on') ? 'checked' : '' ?> required>
                                    <label for="contract">Я принимаю условия обработки данных и пользовательского соглашения *</label>
                                </div>
                                <button class="webform-block_form-sumbit" type="submit">Сохранить анкету</button>
                            </div>
                        </form>
                    </div>
                    <div class="form-message-area" id="message-area" style="display: none;"></div>
                </div>
            </div>
            <hr style="margin-top: 4rem;">
            <div class="webform-block_contact-group">
                <div class="webform-block_contact-img">
                    <a href="#"><img src="materials/icons/f2.png" alt=""></a>
                    <a href="#"><img src="materials/icons/wk.png" alt=""></a>
                    <a href="#"><img src="materials/icons/tg.png" alt=""></a>
                    <a href="#"><img src="materials/icons/youtube.png" alt=""></a>
                </div>
                <div class="webform-block_contact-d">
                    <p>Проект ООО «Инитлаб», Краснодар, Россия.<br>Drupal является зарегистрированной торговой<br>маркой Dries Buytaert.</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('devForm');
            const msgDiv = document.getElementById('apiMessage');
            const isAuthorized = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
            const applicationId = <?= $_SESSION['application_id'] ?? 0 ?>;

            function showMessage(text, isError) {
                msgDiv.innerHTML = `<div class="api-message ${isError ? 'api-error' : 'api-success'}">${escapeHtml(text)}</div>`;
                setTimeout(() => { if (msgDiv.firstChild) msgDiv.removeChild(msgDiv.firstChild); }, 6000);
            }
            function escapeHtml(str) {
                return str.replace(/[&<>]/g, function(m) {
                    if (m === '&') return '&amp;';
                    if (m === '<') return '&lt;';
                    if (m === '>') return '&gt;';
                    return m;
                });
            }
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                let url = '/api.php/forms';
                let method = 'POST';
                if (isAuthorized && applicationId > 0) {
                    url = `/api.php/forms/${applicationId}`;
                    method = 'PUT';
                }
                try {
                    const response = await fetch(url, { method: method, body: formData });
                    const result = await response.json();
                    if (response.ok && result.status === 'success') {
                        if (result.login && result.password) {
                            showMessage(`Анкета сохранена! Ваш логин: ${result.login}, пароль: ${result.password}. Сохраните их для редактирования.`, false);
                            if (!isAuthorized) form.reset();
                        } else {
                            showMessage(result.message || 'Данные успешно обновлены!', false);
                            if (isAuthorized) setTimeout(() => location.reload(), 1500);
                        }
                    } else {
                        let errMsg = result.message || 'Ошибка сервера';
                        if (result.errors) errMsg = Object.values(result.errors).join('; ');
                        showMessage('Ошибка: ' + errMsg, true);
                    }
                } catch (error) {
                    showMessage('Ошибка сети: ' + error.message, true);
                }
            });
        });
    </script>
</body>
</html>
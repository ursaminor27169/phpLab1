<?php
session_start(); // подключаем механизм сессий
?>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Главная страница</title>
    <link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<body>
<?php
    function debug_to_console($data, $context = 'Debug in Console') {

        // Buffering to solve problems frameworks, like header() in this and not a solid return.
        ob_start();

        $output  = 'console.info(\'' . $context . ':\');';
        $output .= 'console.log(' . json_encode($data) . ');';
        $output  = sprintf('<script>%s</script>', $output);

        echo $output;
    }
    ////////////////////////////////////////////////////////////////////
    // обрабатываем выход
    ////////////////////////////////////////////////////////////////////
    function csv_to_array($file) {
        ini_set('auto_detect_line_endings', true);

        $file = fopen($file, 'rt') or die('Unable to open file!');

        $returnVal = array();
        $header = null;

        while(($row = fgetcsv($file, 1000, ';')) !== false){

            if($header === null){
                $header = $row;
                continue;
            }
            if (!$row[0]) {
                break;
            }
            $newRow = array();
            for($i = 0; $i<count($row); $i++){
                $newRow[$header[$i]] = $row[$i];
            }
            $returnVal[] = $newRow;
        }

        fclose($file);
        return $returnVal;
    }

    if (isset($_GET['logout'])) // если был переход по ссылке Выход
    {
        unset($_SESSION['user']); // удаляем информацию о пользователе
        header('Location: /'); // переадресация на главную страницу
        exit(); // дальнейшая работа скрипта излишняя
    }
    ////////////////////////////////////////////////////////////////////
    // если аутентификации нет, но переданы данные для ее проведения
    ////////////////////////////////////////////////////////////////////
    $csv = csv_to_array('users.csv');
    if (!isset($_SESSION['user']) && isset($_POST['login']) &&
        isset($_POST['password']) && $csv) {
        foreach ($csv as $test_user) // пока не найден конец файла
        {
            // разбиваем текущую строку файла в массив

            if (trim($test_user['login']) == $_POST['login']) // если найден логин
            {
                if (isset($test_user['password']) && // если пароли совпали
                    trim($test_user['password']) == $_POST['password']) // сохраняем
                {
                    $_SESSION['user'] = $test_user; // в сессию
                    header('Location: /index.php'); // редирект на главную
                } else // если пароль не совпал
                    break; // прекращаем итерации
            }
        }
        echo '<h1>Неверный логин или пароль!</h1>';
    }
    ////////////////////////////////////////////////////////////////////
    // если аутентификации все еще нет
    ////////////////////////////////////////////////////////////////////
    if (!isset($_SESSION['user'])) {
        // выводим форму для аутентификации
        echo '
                <form id="login" name="auth" method="post" action="">
                <fieldset id="inputs">
                <input id="username" placeholder="Логин" autofocus="" type="text" name="login"';
        //если логин уже вводился ранее и был передан в программу
        if (isset($_POST['login']))
            echo ' value="' . $_POST['login'] . '"'; // заполняем значение поля
        echo '><input id="password" placeholder="Пароль" type="password" name="password">
                </fieldset>
                <fieldset id="actions">
                <input id="submit" type="submit" value="Войти">
                </fieldset>
                </form>';
    } else
        ////////////////////////////////////////////////////////////////////
        // если аутентификация успешно произведена
        ////////////////////////////////////////////////////////////////////
    {
        echo '<div class="exit"><a href="/?logout=">Выход</a></div>'; // выводится ссылка для выхода
        // выводится информация для аутентифицированного пользователя
        echo '<h1>Добро пожаловать, ' .$_SESSION['user']['user']. '!</h1>';
        include 'tree.php'; // выводим содержимое дерева файлов
    }
?>
</body>


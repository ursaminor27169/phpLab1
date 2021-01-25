<?php
/**
 * Simple helper to debug to the console
 *
 * @param $data object, array, string $data
 * @param $context string  Optional a description.
 *
 * @return string
 */
    function outdirInfo($path, $level)
    {
        $files = scandir($path);
        foreach($files as $file) {
            /* Не считываем текущий и родительский каталог */
            if (($file == '.') || ($file == '..')) continue;
            $f0 = $path.'/'.$file; // Отображаем полный путь к файлу
            /* если это папка, то... */
            if (is_dir($f0)) {
                /* С помощью рекурсии выводим содержимое полученной директории */
                if ($level < 6) {
                    echo '<li><p>'.$file.'</p><ul>';
                    $level += 1;
                    outdirInfo($f0,$level);
                    echo '</ul></li>';
                } else {
                    echo '<li><p>'.$file."".'</p></li>';
                }
            }
            /* Если это файл, то просто выводим название файла */
            else echo makeLink($file, $f0);
        }
    }

    function makeLink( $name, $path )
    {
        return '<li><p><a target = "_blank" href="'.'viewer.php?filename='. UrlEncode($path).'">'.$name.'</a></p></li>'; // выводим ссылку в HTML-код страницы
    }

    function recursiveRemoveDir($dir) {

        $includes = glob($dir.'/*');
        foreach ($includes as $include) {
            debug_to_console(is_dir($include));
            if(is_dir($include)) {
                recursiveRemoveDir($include);
            }
            else {
                unlink($include);
            }
        }
        rmdir($dir);
    }


    echo '<ul id="dir_tree"><h3>'.$_SERVER['DOCUMENT_ROOT'].'</h3>'; // выводит начало тега блока дерева каталогов
    outdirInfo($_SERVER['DOCUMENT_ROOT'],0 ); // выводит дерево каталогов
    echo '</ul>'; // конец блока дерева каталогов

?>
    <form method="post" enctype="multipart/form-data" action="/">
        <label for="dir-name">Каталог на сервере</label>
        <input type="text" name="dir-name" id="dir-name">
        <label for="myfilename">Локальный файл</label>
        <input type="file" name="myfilename">
        <input type="submit" value="Отправить файл на сервер">
    </form>
<?php

    if( isset($_FILES['myfilename']) ) // были отправлены данные формы
    {
        if( isset($_FILES['myfilename']['tmp_name']) ) // если файл загружен
        {
            if( $_FILES['myfilename']['tmp_name'] ) // если файл существует
            {
                // копируем его и выводим сообщение об успешной загрузке
                            move_uploaded_file($_FILES['myfilename']['tmp_name'],
                                makeName($_FILES['myfilename']['name']));
                echo 'Файл '.$_FILES['myfilename']['name'].' загружен на сервер';
                echo $_POST['dir-name'];
            }
        elseif ( isset($_FILES['dir-name']) )
            recursiveRemoveDir( $_POST['dir-name'] ); // удаляем каталог
        } else {
            echo '<p>Ошибка загрузки!</p>';
        }
    }
    function makeName($filename)
    {
        if( !file_exists($_POST['dir-name']) ) // если каталога не существует
        {
            umask(0); // сбрасываем значение umask
            mkdir($_SERVER['DOCUMENT_ROOT'].'/'.$_POST['dir-name'], 0777, true); // создаем ее
        }
        $array = explode('.', $filename);
        $ext = end($array);
        $n=1; // начиная с 1 цикл пока существует файл
        while( file_exists($_POST['dir-name'].'/'.$n.'.'.$ext )) // с текущем номером
            $n++; // - увеличиваем номер
        debug_to_console($_POST['dir-name']);

        updateFileList($_SERVER['DOCUMENT_ROOT'].'/'.$_POST['dir-name'].'/'.$n.'.'.$ext);
        if ($_POST['dir-name'] == "")
            return ($_SERVER['DOCUMENT_ROOT'].'/'.$n.'.'.$ext); // возвращаем свободное имя
        else
            return ($_SERVER['DOCUMENT_ROOT'].'/'.$_POST['dir-name'].'/'.$n.'.'.$ext); // возвращаем свободное имя
    }

    function updateFileList($filename)
    {
        ini_set('auto_detect_line_endings', true);

        $file = fopen('users.csv', 'a') or die('Unable to open file!');
        fputs($file, $filename.';'.$_SESSION['user']['user'].'\n');

        fclose($file);
    }
?>
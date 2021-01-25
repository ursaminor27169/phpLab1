<?php
session_start(); // подключаем механизм сессий
function show_file($filename) {
    $f = fopen( $filename, 'rt' ); // открываем файл в текстовом режиме
    if( $f ) // если файл успешно открыт
    {
        $content = ''; // содержимое файла пока пусто
        while( !feof($f) ) // цикл, пока не достигнут конец файла
            $content .= fgets( $f ); // читаем строку файла
        echo "// ".$content; // выводим содержимое файла
        fclose( $f ); // закрываем файл
    }
    else
        echo 'Ошибка открытия файла '. $_GET['filename'];
}

function debug_to_console($data, $context = 'Debug in Console') {

    // Buffering to solve problems frameworks, like header() in this and not a solid return.
    ob_start();

    $output  = 'console.info(\'' . $context . ':\');';
    $output .= 'console.log(' . json_encode($data) . ');';
    $output  = sprintf('<script>%s</script>', $output);

    echo $output;
}
function file_user($user, $filename) {
    if ($filename === $_SERVER['DOCUMENT_ROOT'].'/'.'users.csv') {
        echo 'Секретная информация';
        exit();
    }
    ini_set('auto_detect_line_endings', true);

    $file = fopen('users.csv', 'rt') or die('Файл не найден!');

    $returnVal = array();
    $header = null;
    $owner = null;
    while(($row = fgetcsv($file, 1000, ';')) !== false){
        if (!$row[0]) {
            break;
        }
    }
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
    foreach($returnVal as $row) {
        debug_to_console($row['file']);
        if ($row['file'] !== $filename) {
            continue;
        } else {
            $owner = $row['user'];
        }
    }
    fclose($file);
    if ($owner === null)
        echo 'Системный файл';
    elseif ($owner === $user)
        show_file($_GET['filename']);
    else
        echo 'Нет прав доступа!';

}

if( !isset($_SESSION['user']) ) { echo 'Необходима аутентификация'; exit(); }
if(!isset($_GET['filename'])) { echo 'Имя файла не указано!'; exit();}

file_user($_SESSION['user']['user'],$_GET['filename']);

?>
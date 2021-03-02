<?php
namespace Web;

class Parser {
    public static function getPage($params = []){
        if($params){
            if(!empty($params["url"])){
                $url = $params["url"];
                // заголовок User-Agent
                $useragent = !empty($params["useragent"]) ? $params["useragent"] : "Mozilla/5.0 (Windows NT 6.3; W…) Gecko/20100101 Firefox/57.0";
                // время выполнения запроса на сервер
                $timeout = !empty($params["timeout"]) ? $params["timeout"] : 5;
                // время ожидания соединения
                $connecttimeout = !empty($params["connecttimeout"]) ? $params["connecttimeout"] : 5;
                // если нам потребуется проверить только заголовки,
                // которые отдаёт сервер на наш запрос этот параметр нам просто будет необходим;
                $head = !empty($params["head"]) ? $params["head"] : false;
                // файл, в который будут записывать куки нашего донора контента и при обращении передаваться
                $cookie_file = !empty($params["cookie"]["file"]) ? $params["cookie"]["file"] : false;
                // иногда может быть необходимо, запрещать передачу сессионных кук
                $cookie_session = !empty($params["cookie"]["session"]) ? $params["cookie"]["session"] : false;
                // IP прокси-сервера
                $proxy_ip = !empty($params["proxy"]["ip"]) ? $params["proxy"]["ip"] : false;
                // порт прокси-сервера
                $proxy_port = !empty($params["proxy"]["port"]) ? $params["proxy"]["port"] : false;
                // тип прокси CURLPROXY_HTTP, CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A или CURLPROXY_SOCKS5_HOSTNAME
                $proxy_type = !empty($params["proxy"]["type"]) ? $params["proxy"]["type"] : false;
                // массив заголовков
                $headers = !empty($params["headers"]) ? $params["headers"] : false;
                // для отправки POST запроса
                $post = !empty($params["post"]) ? $params["post"] : false;

                if($cookie_file){
                    file_put_contents(__DIR__."/".$cookie_file, "");
                }

                // открытие сессии
                $ch = curl_init();

                // настройки сессии
                // CURLOPT_URL – первый и обязательный - это адрес, на который мы обращаемся;
                curl_setopt($ch, CURLOPT_URL, $url);
                // CURLINFO_HEADER_OUT –массив с информацией о текущем соединении
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                // отображаем контент
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connecttimeout);

                // Для вывода заголовков ответа
                if($head){
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                }

                // Для работы со ссылками с SSL сертификатом
                if(strpos($url, "https") !== false){
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                }

                // Учитываем нюансы для cooke
                // Проблема при сохранении (к примеру указание относительных путей)
                if($cookie_file){
                    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__."/".$cookie_file);
                    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__."/".$cookie_file);

                    if($cookie_session){
                        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
                    }
                }

                // Добавляем в параметры сеанса прокси
                if($proxy_ip && $proxy_port && $proxy_type){
                    curl_setopt($ch, CURLOPT_PROXY, $proxy_ip.":".$proxy_port);
                    curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy_type);
                }

                // Заголовки
                if($headers){
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }

                // Возможность отправки запросов Post
                if($post){
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                }

                // отправка запроса
                $content = curl_exec($ch);
                // отслеживаем информацию при запросе
                $info      = curl_getinfo($ch);

                $error = false;

                if($content === false){
                    $data = false;

                    $error["message"] = curl_error($ch); // сообщение об ошибке
                    $error["code"]      = self::$error_codes[
                    curl_errno($ch) // код ошибки
                    ];
                }else{
                    $data["content"] = $content;
                    $data["info"]      = $info;
                }
                // закрываем сессию
                curl_close($ch);

                return [
                    "data"    => $data,
                    "error" => $error
                ];
            }
        }
        return false;
    }

    private static $error_codes = [
        "CURLE_UNSUPPORTED_PROTOCOL",
        "CURLE_FAILED_INIT",

        // Тут более 60 элементов, в архиве вы найдете весь список

        "CURLE_FTP_BAD_FILE_LIST",
        "CURLE_CHUNK_FAILED"
    ];
}
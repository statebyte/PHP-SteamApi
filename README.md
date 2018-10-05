# PHP-SteamApi [![Build Status](https://travis-ci.org/theelsaud/PHP-SteamApi.svg?branch=release)](https://travis-ci.org/theelsaud/PHP-SteamApi)
PHP класс для работы со SteamApi а также SteamID

### Функции
В классе и в API имя метода совпадают с именем функции.

| Function | Discription | STEAM WEB API |
| ------ | ------ | ------ |
| render()  | Возвращяет  CommunityID, SteamID, SteamID3, URL | no |
| GetPlayerSummaries()  | Информация об аккаунте | yes |

### Установка

Подключить файл SteamApi.php
```php
require "../SteamApi.php";
```

### Пример

```php
try{
  $api = new SteamApi( key, api_key );
  var_dump($api->render());
}catch(Exception $e){
  echo $e->getMessage();
}
```
где 
> key - это ID или ссылка на пользователя (CommunityID: 76561197960287930, SteamID: STEAM_1:0:11101, url: https://steamcommunity.com/profiles/76561197960287930 or https://steamcommunity.com/id/gabelogannewell)
> api_key - это Steam Web Api Key (Получить можно тут: https://steamcommunity.com/dev/apikey)


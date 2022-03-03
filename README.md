# odnoklassniki-php-sdk
Класс для работы с API OK/RU

Для работы нужен **accessToken** полученный при авторизации!

```php
$ok = new OdnoklassnikiApi(
		'51200010XXX','CIADJGJGDIHBXXXXX',
		'078EFA54EE9B4276B1CA4FE0',
		'tkn1cAzw6FhpYk1U4i7o4RumnCXXXXXXXXXXX',
		'GROUP_CONTENT;VALUABLE_ACCESS;LONG_ACCESS_TOKEN;GROUP_CONTENT;'
);

$groups = $ok->group_getInfo('52223116181643');

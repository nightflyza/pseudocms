# PseudoCMS

Файлова міні-CMS для [YALF](https://yalf.nightfly.in.ua/). Накладається поверх існуючої установки. Не змінює жодні модулі чи бібліотеки самого фреймворку.

## Склад пакета

| Шлях | Призначення |
|------|-------------|
| `api/libs/api.pseudocms.php` | Бібліотека PseudoCMS (сховище, вивід, адмінка) |
| `modules/general/pages/` | Публічні HTML-сторінки |
| `modules/general/articles/` | Стрічка та перегляд статей |
| `modules/general/rss/` | RSS 2.0 |
| `modules/general/recent/` | Віджет останніх N статей |
| `modules/general/pcmsmgr/` | Адмін CRUD (право `CMSMGR`) |
| `modules/general/pcmsmonitor/` | Монітор змін файлів |
| `languages/ukrainian/pseudocms.php` | Локалізація українською |
| `content/documents/pseudocms/` | Каталог даних (сторінки, статті тощо) |
| `media/` | Директорія для завантажень медіа-файлів |
| `skins/pseudocms.css` | Приклад публічного CSS (`PCMS_CUSTOM_STYLES`) |

## Встановлення

1. Скопіюйте файли пакету в корінь YAL.
2. У `config/alter.ini` увімкніть CMS:

```ini
PCMS_ENABLED=1
```

3. У `config/yalf.ini` додайте публічні модулі до гостьового доступу:

```ini
YALF_NO_AUTH_MODULES="articles,pages,rss,recent,pcmsmonitor"
```

4. Для адмінки: обов'язкова опція `YALF_AUTH_ENABLED=1` та право `CMSMGR` адміністратора.
5. Зробіть `content/documents/pseudocms/` та директорію завантаження медіа файлів доступними для запису
```sh
chmod -R 777 content/documents/pseudocms media
```

## Публічні URL-адреси

| Що | URL |
|----|-----|
| Сторінка | `?module=pages&pageid=about` |
| Статті | `?module=articles` |
| Стаття | `?module=articles&a=123` |
| Останні | `?module=recent` |
| RSS | `?module=rss` |
| Адмінка | `?module=pcmsmgr` |
| Монітор | `?module=pcmsmonitor` |

## Дані

Зберігаються як JSON у `content/documents/pseudocms/`: сторінки, статті, чернетки, елементи. Медіа - у каталозі з `PCMS_MEDIA_PATH` (якщо задано).

## Елементи (вбудовування)

Окремого гостьового URL немає. Елементи керуються в `pcmsmgr`. вміст елементу може бути HTML і/або PHP. Можете викликати їх з власного модуля чи прямо з шаблону:

```php
$cms = new PseudoCMS();
$cms->renderElement('header'); // вивести як HTML
$cms->runElement('sidebar');   // виконати як PHP (без тегів <?php)
```

`runElement` повертає результат `eval` або `false`, якщо елемента немає.

## CSS-класи

Зарезервовані хуки для власного стилю (`PCMS_CUSTOM_STYLES`). Імена стабільні - зберігайте їх, замінюючи `skins/pseudocms.css` чи власний CSS. Адмінка (`pcmsmgr`) ці класи не використовує, вони існують тільки для відображення в публічних модулях.

### Обгортки розмітки

| Клас | Де | Що |
|------|----|----|
| `pcms_feed` | Стрічка статей | Зовнішня обгортка списку |
| `pcms_recent` | Модуль recent | Обгортка останніх N |
| `pcms_recent_more` | Модуль recent | Рядок посилання «Усі статті» |
| `pcms_feed_item` | Стрічка + recent | Один запис |
| `pcms_feed_title` | Стрічка + recent | Заголовок (`h3` з посиланням) |
| `pcms_feed_excerpt` | Стрічка + recent | Короткий текст |
| `pcms_article` | Одна стаття | Зовнішня обгортка |
| `pcms_article_body` | Одна стаття | Повний текст |
| `pcms_article_date` | Стрічка + стаття | Рядок дати |
| `pcms_page` | Сторінка | Зовнішня обгортка |
| `pcms_page_body` | Сторінка | HTML-вміст |
| `pcms_pager` | Стрічка статей | Пагінація |
| `pcms_pager_link` | Стрічка статей | Посилання номерів сторінок |

### Допоміжні класи BBCode статей (лише `type=text`)

| Клас | BBCode | Елемент |
|------|--------|---------|
| `pcms_article_link` | `[url]` / `[url=…]` | `<a>` |
| `pcms_article_image` | `[img]` | `<img>` |
| `pcms_article_list` | `[list]` + `[*]` | `<ul>` |
| `pcms_article_code` | `[code]` | `<pre>` |
| `pcms_article_hr` | `[hr]` | `<hr>` |


## Опції `alter.ini`

```ini
;PseudoCMS enable flag
PCMS_ENABLED=1
;PseudoCMS articles per feed page
PCMS_ARTICLES_PERPAGE=10
;PseudoCMS article date render: 0=hide, 1=date only, 2=date and time
PCMS_ARTICLES_DATE_RENDER=2
;Optional custom public name for articles feed window
PCMS_ARTICLES_CUSTOM_NAMING="News"
;PseudoCMS articles creation default mode: 1 - html, 0 - text
PCMS_ARTICLES_DEFAULT_HTML=0
;Browser page title: 0 - disabled, 1 - append to getPageTitle, 2 - replace with content title
PCMS_MUTABLE_TITLES=1
;Optional public stylesheet for pages/articles. Empty - disabled.
PCMS_CUSTOM_STYLES="skins/pseudocms.css"
;Recent articles module (?module=recent) item count (0 or missing - hide list)
PCMS_RECENT_COUNT=3
;Public RSS module enabled?
PCMS_RSS_ENABLED=1
;Public media directory, empty - media uploads disabled
PCMS_MEDIA_PATH="media/"
;Allowed media extensions, comma-separated, case-insensitive
PCMS_MEDIA_ALLOWED="jpeg,jpg,gif,png,pdf"
;CMS file-change monitor module enabled?
PCMS_MONITOR_ENABLED=1
;Remote IPs allowed to use pcmsmonitor without auth, comma-separated, empty - allowed all
PCMS_MONITOR_IPS=""
;Relative paths ignored while scanning, comma-separated files or directories
PCMS_MONITOR_IGNORE_PATHS=""
```
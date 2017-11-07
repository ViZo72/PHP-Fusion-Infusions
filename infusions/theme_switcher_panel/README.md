# Theme Switcher

![Preview](https://raw.githubusercontent.com/RobiNN1/PHP-Fusion-Infusions/master/infusions/theme_switcher_panel/preview.png)

##### In maincore.php Line 250, change set_theme() to
```php
$theme = !empty($_COOKIE[COOKIE_PREFIX.'theme']) ? $_COOKIE[COOKIE_PREFIX.'theme'] : (empty($userdata['user_theme']) ? fusion_get_settings("theme") : $userdata['user_theme']);
set_theme($theme);
```

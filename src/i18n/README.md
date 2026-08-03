# File-based i18n

System translations live in flat PHP category files at
`src/i18n/{language}/{category}.php`. Each file must return an associative
`string => string` array. No database query is involved.

```php
__('title');
__('title', [], 'about_us');
__('welcome', ['name' => 'Anna']);
__('send', [], 'forms');
```

## Adding a category

Create the same flat file where translations are available, for example
`src/i18n/en/forms.php` and `src/i18n/cs/forms.php`. Category names may contain
only lowercase letters, digits, and underscores.

## Adding a language

Add its code to `supported` in `src/i18n/config.php`, create the matching
directory, and add at least `general.php`. The default language is English and
does not have a URL prefix.

## Fallback order

For `__('key', [], 'category')`, lookup is current language/category, current
language/general, English/category, English/general, then the key itself.
Missing category files are allowed. Each language/category file is loaded at
most once per request.

For dynamic pages with translated slugs, pass `localizedPaths` in controller
view data:

```php
$this->view('blog/detail', [
    'localizedPaths' => [
        'en' => 'blog/english-slug',
        'cs' => 'blog/cesky-slug',
        'de' => 'blog/deutscher-slug',
    ],
]);
```

Omit a language when that dynamic translation does not exist. The language
switcher and `hreflang` output will omit it too.

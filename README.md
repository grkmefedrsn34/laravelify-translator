# 🌍 Laravelify Translator

Automatic translation package for Laravel. Scans your Blade/PHP files for translation keys and generates `lang/` files using Google Translate or MyMemory — in seconds.

No manual copy-paste. No spreadsheets. Just run one command.

---

## Requirements

- PHP 8.1+
- Laravel 10, 11 or 12

---

## Installation

```bash
composer require laravelify/translator
```

Publish the config file:

```bash
php artisan vendor:publish --tag=laravelify-translator-config
```

---

## Configuration

Add to your `.env`:

```env
# Source language
TRANSLATOR_SOURCE_LOCALE=en

# Target languages (comma separated)
TRANSLATOR_LOCALES=tr,de,fr

# Output format: json | php | both
TRANSLATOR_FORMAT=both

# Engine: mymemory | google_free
TRANSLATOR_ENGINE=mymemory
```

---

## Usage

### One command does everything:

```bash
php artisan translate:run
```

### Or step by step:

```bash
# 1. See what keys exist in your project
php artisan translate:scan

# 2. Translate and generate lang files
php artisan translate:generate
```

### Useful flags:

```bash
# Preview without writing any files
php artisan translate:run --dry-run

# Force re-translate everything
php artisan translate:run --force

# See all found keys
php artisan translate:scan --show
```
lang/
├── tr.json
├── de.json
├── tr/
│ └── messages.php
└── de/
└── messages.php


---

## License

MIT
---

## Output

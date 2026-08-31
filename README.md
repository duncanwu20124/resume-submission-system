# resume-submission-system
A Scrum-based student resume submission system for students and professors.

## install dependency
1. [PHP](https://www.php.net/)

- windows:
```bash
winget install PHP.PHP.8.5
```

- macOS
```bash
brew install php
```

- Linux
```bash
sudo apt update
sudo apt install php php-cli
```

2. [Composer](https://getcomposer.org/)

3. uncommand extension in `php.ini`:
- `intl`
- `curl`
- `fileinfo`
- `mbstring`
- `openssl`
- `pdo_mysql`
- `mysqli`
- `pdo_sqlite`
- `sqlite3`
- `zip`

4. install composer dependency
```bash
cd resume-submission-system/
composer install
```

## start demo

1. move into dir
```bash
cd resume-submission-system/
```

2. install composer dependency
```bash
composer install
```

3. start server
```bash
php spark serve
```

4. visit http://localhost:8080

5. admin pages http://localhost:8080/AdminController/login
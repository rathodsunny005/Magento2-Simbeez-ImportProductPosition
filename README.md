# Magento 2 Simbeez_ImportProductPosition

## Overview
Simbeez_ImportProductPosition is a Magento 2 extension that provides change product position at category page. you can download sample file from backend and using that format import the file and it update the position in category page.

---

## Folder Structure in GitHub

Your GitHub repository should follow this structure:

```
Magento2-Simbeez-ImportProductPosition/   <-- This is your GitHub repo
│── Simbeez/
│   ├── ImportProductPosition/
│   │   ├── etc/
│   │   │   ├── module.xml
│   │   ├── Model/
│   │   ├── Controller/
│   │   ├── Helper/
│   │   ├── view/
│   │   ├── registration.php
│   │   ├── composer.json
│   │   ├── README.md
```

After cloning the repository, move the contents inside `app/code/` in your Magento 2 installation:

```bash
cd /path/to/magento2/app/code/
git clone https://github.com/yourusername/Magento2-Simbeez-ImportProductPosition.git Simbeez/ImportProductPosition
```

This will result in the following Magento 2 directory structure:

```
app/
├── code/
│   ├── Simbeez/
│   │   ├── ImportProductPosition/
│   │   │   ├── etc/
│   │   │   ├── Model/
│   │   │   ├── Controller/
│   │   │   ├── Helper/
│   │   │   ├── view/
│   │   │   ├── registration.php
│   │   │   ├── composer.json
│   │   │   ├── README.md
```

---

## Installation

### 1. Manual Installation (Recommended for Development)

1. Navigate to your Magento root directory:

   ```bash
   cd /path/to/magento2/
   ```

2. Copy the module files to `app/code/Simbeez/ImportProductPosition/`.

3. Run the following Magento CLI commands:

   ```bash
   php bin/magento module:enable Simbeez_ImportProductPosition
   php bin/magento setup:upgrade
   php bin/magento cache:flush
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy -f
   ```

### 2. Installation via Composer

1. Run the following command:

   ```bash
   composer require simbeez/import-product-position
   ```

2. Enable the module and run setup commands:

   ```bash
   php bin/magento module:enable Simbeez_ImportProductPosition
   php bin/magento setup:upgrade
   php bin/magento cache:flush
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy -f
   ```

---

## Uninstallation

If you want to remove the module, run:

```bash
php bin/magento module:disable Simbeez_ImportProductPosition
php bin/magento setup:upgrade
php bin/magento cache:flush
rm -rf app/code/Simbeez/ImportProductPosition/
composer remove simbeez/import-product-position
```

---

## Changelog
### Version 1.0.0
- Initial release

---

## Support
For any issues, please open a GitHub issue or contact [rathodsunny005@gmail.com].

---

## License
This module is licensed and copyright


# Upgrading Leconfe

Note: Before upgrading your installation, perform a complete backup of your
data files and database. If the upgrade process fails, you will need to recover
from backup before continuing.


## Upgrade to latest version
Upgrading to the latest version of Leconfe involves updating the codebase and running the upgrade script.

### Update the codebase
- Download the latest version of Leconfe from the [Leconfe website](https://leconfe.com/download/) or from the [Leconfe GitHub repository](https://github.com/OpenSynergic/leconfe/releases).

- Extract the downloaded archive into the empty directory.
- Copy the following files and directories from your existing installation to the new installation: 
    - .env
    - storage/app
- Backup the current installation directory, and replace it with the new installation directory.

### Run the upgrade script
There's to way to upgrade Leconfe, using the command line or using the web interface .

#### Using the command line
If you have the CLI version of PHP installed (e.g., `/usr/bin/php`), you can
run the following command from the Leconfe installation directory:

```bash
php artisan leconfe:upgrade
```

#### Using the web interface
If you don't have access to the command line, you can run the upgrade script from the web interface.
Access your website, Leconfe will detect that an upgrade is needed and will redirect you to the upgrade page.
Follow the upgrade instructions to complete the upgrade process.


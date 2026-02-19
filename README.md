> [!CAUTION]
> **DEPRICATION NOTICE**: This script no longer works on latest versions of CtrlPanel and Paymenter.

# Ctrlpanel.gg to Paymenter users migrator

This program migrates all user attributes including passwords, email verification, created and updated timestamps.

## Features

- Users with Admin role in ctrlpanel will be made Admin in Paymenter as well.
- Timestamps such as `updated_at` and `created_at` will be migrated too.
- If the user has verified their email on ctrlpanel, their email will automatically be verified in paymenter.

## Usage

Unzip the file in a directory on a server.
Fill the database connection variables at the top of the migrator.php file.

Run `php migrator.php` And then input `y` to continue.

If everything goes well, it should start migrating users, it will skip users with same email (already existing users).
After migration, your users can log in with the same credentials they used on ctrlpanel.

## License

This software is licensed under GPL v3.0, see LICENSE file for more details.

<?php

// Ctrlpanel.gg to Paymenter users migrator
// This program migrates all user attributes including passwords, email verification, created and updated timestamps.
/////////////////////////////////////////
// These details can be found in .env files of your ctrlpanel and paymenter instances. 
$db_details = [
    'ctrlpanel' => [
        'host' => '127.0.0.1',
        'user' => 'controlpaneluser',
        'password' => 'USE_YOUR_OWN_PASSWORD',
        'database' => 'controlpanel'
    ],
    'paymenter' => [
        'host' => '127.0.0.1',
        'user' => 'local',
        'password' => 'local',
        'database' => 'paymenter'
    ]
];

/////////////////////////////////////////

// Copyright (C) 2023 Vikas Dongre

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <https://www.gnu.org/licenses/>.

$style = "\033[1m";
echo "\n$style\033[32mCtrlpanel.gg to Paymenter users migrator \033[30m[GPLv3.0] \033[0m\n";
echo "$style\033[32mThis program migrates all user attributes including passwords, email verification, created\n and updated timestamps.\033[0m\n\n";

$app_name = "$style\033[34m[MIGRATOR]\033[0m ";
$continue = readline("Continue? [y/n] ");

if (!in_array(strtolower($continue), ['yes', 'y'])) {
    echo "$style\033[31mExiting...\033[0m\n";
    exit(0);
}

try {
    $ctrlpaneldb = new mysqli($db_details['ctrlpanel']['host'], $db_details['ctrlpanel']['user'], $db_details['ctrlpanel']['password'], $db_details['ctrlpanel']['database']);
    $paymenterdb = new mysqli($db_details['paymenter']['host'], $db_details['paymenter']['user'], $db_details['paymenter']['password'], $db_details['paymenter']['database']);
} catch (\Throwable $th) {
    echo "$style\033[31mConnection to database failed, are the details correct?\033[0m - $th\n";
    exit(1);
}

echo "\n$app_name$style\033[32mStarting Migration\033[0m\n";

$userSQL = "SELECT * FROM `users`";
$ctrlpanelUserResult = mysqli_query($ctrlpaneldb, $userSQL);
$paymenterUserResult = mysqli_query($paymenterdb, $userSQL);

$count = [
    'migrated' => 0,
    'skipped' => 0,
    'failed' => 0,
];

while ($ctrlpaneluser = $ctrlpanelUserResult->fetch_assoc()) {
    $id = $ctrlpaneluser["id"];
    $name = explode(' ', $ctrlpaneluser["name"]);
    $first = $name[0];
    $last = isset($name[1]) ? $name[1] : "User";

    $email = $ctrlpaneluser['email'];
    $role_id = $ctrlpaneluser['role'] == 'admin' ? 1 : 2;

    $password = $ctrlpaneluser['password'];

    // These fields are not present in ctrlpanel, but I am leaving them as null variables for readablility.
    $address = null;
    $city = null;
    $state = null;
    $zip = null;
    $country = null;
    $phone = null;
    $companyname = null;
    $remember_token = null;
    $created_at = isset($ctrlpaneluser['created_at']) ? $ctrlpaneluser['created_at'] : null;
    $updated_at = isset($ctrlpaneluser['updated_at']) ? $ctrlpaneluser['updated_at'] : null;
    $credits = 0.00;
    $tfa_secret = null;
    $email_verified_at = isset($ctrlpaneluser['email_verified_at']) ? $ctrlpaneluser['email_verified_at'] : null;

    $checkusersql = mysqli_query($paymenterdb, "SELECT * FROM `users` WHERE `email` = '$email'");
    if (mysqli_num_rows($checkusersql) > 0) {
        echo "$app_name$style\033[33mUser `$email` already exists. Skipping!\033[0m\n";
        $count['skipped']++;
    } else {
        try {
            $sql = "INSERT INTO users (first_name, last_name, email, role_id, email_verified_at, password, address, city, state, zip, country, phone, companyname, remember_token, created_at, updated_at, credits, tfa_secret) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $paymenterdb->prepare($sql);
            $stmt->bind_param("sssissssssssssssis", $first, $last, $email, $role_id, $email_verified_at, $password, $address, $city, $state, $zip, $country, $phone, $companyname, $remember_token, $created_at, $updated_at, $credits, $tfa_secret);
            $stmt->execute();
            $stmt->close();

            echo "$app_name$style\033[32mUser `$email` created successfully!\033[0m\n";
            $count['migrated']++;
        } catch (\Throwable $th) {
            echo "$app_name$style\033[31mThere was an error migrating `$email`: $th\033[0m\n";
            $count['failed']++;
        }
    }
}

echo "\n$app_name$style\033[32mMigration Complete! \033[0m\n";
echo $app_name . "\033[0mMigrated: $style\033[32m" . $count['migrated'] . "\033[0m, Skipped: $style\033[33m" . $count['skipped'] . "\033[0m, Failed: $style\033[31m" . $count['failed'] . "\033[0m\n";

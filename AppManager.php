<?php

# Helper functions
# -------------------------------------------------------------------
function isNullOrEmpty($value)
{
    return $value == null || $value == "";
}
function outputResults($message, $error, $data)
{
    header("Content-type: application/json");
    echo json_encode(array(
        "message" => $message,
        "error" => $error,
        "data" => $data
    ));
}
function generateRandomString($length = 10, $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
{
    $randomString = "";
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
# -------------------------------------------------------------------

# Initialize database connection
# -------------------------------------------------------------------

$servername = "localhost";
$dbname     = "DATABASE_NAME";
$username   = "DATABASE_USERNAME";
$password   = "DATABASE_PASSWORD";

$connection = new mysqli($servername, $username, $password, $dbname);
if ($connection->connect_error) {
    outputResults("database error", true, array(
        "errorMessage" => $connection->connect_error
    ));
    return;
}

if (empty(mysqli_query($connection, "SELECT id FROM licenses"))) {
    mysqli_query($connection, "CREATE TABLE licenses (
id INT(11) AUTO_INCREMENT PRIMARY KEY,
licenseKey VARCHAR(50) NOT NULL,
ownerName VARCHAR(50) NOT NULL
)");
}
if (empty(mysqli_query($connection, "SELECT id FROM properties"))) {
    mysqli_query($connection, "CREATE TABLE properties (
id INT(11) AUTO_INCREMENT PRIMARY KEY,
appStatus tinyint(1) NOT NULL,
appVersion VARCHAR(50) NOT NULL
)");
}

# -------------------------------------------------------------------

$query = $_GET["query"];
if (isNullOrEmpty($query)) {
    outputResults("'query' parameter cannot be null", true, null);
    return;
}

if ($query == "enablelicense") {
    $license = $_GET["license"];
    $owner   = $_GET["owner"];
    if (isNullOrEmpty($license)) {
        outputResults("'license' parameter cannot be null", true, null);
        return;
    }
    if (isNullOrEmpty($owner)) {
        outputResults("'owner' parameter cannot be null", true, null);
        return;
    }
    $result = $connection->query("SELECT * FROM licenses WHERE licenseKey='$license'");
    if ($result == true) {
        if (mysqli_num_rows($result) > 0) {
            outputResults("input error", true, array(
                "errorMessage" => "license exists"
            ));
        } else {
            if ($connection->query("INSERT INTO licenses(licenseKey, ownerName) VALUES ('$license', '$owner')") == true) {
                outputResults("enabled", false, null);
            } else {
                outputResults("database error", true, array(
                    "errorMessage" => $connection->error
                ));
            }
        }
    } else {
        outputResults("database error", true, array(
            "errorMessage" => $connection->error
        ));
    }
} else if ($query == "disablelicense") {
    $license = $_GET["license"];
    if (isNullOrEmpty($license)) {
        outputResults("'license' parameter cannot be null", true, null);
        return;
    }
    $result = $connection->query("SELECT * FROM licenses WHERE licenseKey='$license'");
    if ($result == true) {
        if (mysqli_num_rows($result) > 0) {
            if ($connection->query("DELETE FROM licenses WHERE licenseKey='$license'") == true) {
                outputResults("disabled", false, null);
            } else {
                outputResults("database error", true, array(
                    "errorMessage" => $connection->error
                ));
            }
        } else {
            outputResults("input error", true, array(
                "errorMessage" => "license not exists"
            ));
        }
    } else {
        outputResults("database error", true, array(
            "errorMessage" => $connection->error
        ));
    }
} else if ($query == "validationlicense") {
    $license = $_GET["license"];
    if (isNullOrEmpty($license)) {
        outputResults("'license' parameter cannot be null", true, null);
        return;
    }
    $result = $connection->query("SELECT * FROM licenses WHERE licenseKey='$license'");
    if ($result == true) {
        if (mysqli_num_rows($result) > 0) {
            outputResults("validated", false, array(
                "valid" => true
            ));
        } else {
            outputResults("validated", false, array(
                "valid" => false
            ));
        }
    } else {
        outputResults("database error", true, array(
            "errorMessage" => $connection->error
        ));
    }
} else if ($query == "getlicenseslist") {
    $licenses = array();
    $result   = $connection->query("SELECT * FROM licenses");
    if ($result == true) {
        if (mysqli_num_rows($result) > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($licenses, array(
                    "licenseKey" => $row['licenseKey'],
                    "ownerName" => $row['ownerName']
                ));
            }
        }
    } else {
        outputResults("database error", true, array(
            "errorMessage" => $connection->error
        ));
    }
    outputResults("listed", false, array(
        "licenses" => $licenses
    ));
} else if ($query == "generatelicense") {
    outputResults("generated", false, array(
        "generatedLicense" => generateRandomString(30, "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ")
    ));
} else if ($query == "setappproperties") {
    $status  = $_GET["status"];
    $version = $_GET["version"];
    if (isNullOrEmpty($status)) {
        outputResults("'status' parameter cannot be null", true, null);
        return;
    }
    if (isNullOrEmpty($version)) {
        outputResults("'version' parameter cannot be null", true, null);
        return;
    }
    $result = $connection->query("SELECT * FROM properties");
    if ($result == true) {
        if (mysqli_num_rows($result) > 0) {
            if ($connection->query("UPDATE properties SET appStatus=$status, appVersion='$version'") == true) {
                outputResults("was set", false, null);
            } else {
                outputResults("database error", true, array(
                    "errorMessage" => $connection->error
                ));
            }
        } else {
            if ($connection->query("INSERT INTO properties(appStatus, appVersion) VALUES ($status, '$version')") == true) {
                outputResults("was set", false, null);
            } else {
                outputResults("database error", true, array(
                    "errorMessage" => $connection->error
                ));
            }
        }
    } else {
        outputResults("database error", true, array(
            "errorMessage" => $connection->error
        ));
    }
} else if ($query == "getappproperties") {
    $result = $connection->query("SELECT * FROM properties LIMIT 1");
    $row    = $result->fetch_assoc();
    outputResults("", false, array(
        "appStatus" => (bool) $row["appStatus"],
        "appVersion" => $row["appVersion"]
    ));
}

$connection->close();

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $source_ip = $_POST["source_ip"];
    $destination_ip = $_POST["destination_ip"];
    $rule_name = $_POST["rule_name"];
    $port = $_POST["port"];

    $errorFields = array();

    // Validate IP addresses
    function validateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    // Validate port format (TCP/UDP followed by port number)
    function validatePort($port) {
        return preg_match('/^(TCP|UDP)\d+$/', $port);
    }

    if (!validateIP($source_ip)) {
        $errorFields[] = "source_ip";
    }
    if (!validateIP($destination_ip)) {
        $errorFields[] = "destination_ip";
    }
    if (!validatePort($port)) {
        $errorFields[] = "port";
    }

    if (count($errorFields) > 0) {
        $errorMessage = "Please enter valid IP addresses for Source and Destination, and a valid port in the format 'TCP##' or 'UDP##'.";
        echo $errorMessage . ":" . implode(",", $errorFields);
    } else {
        $errorMessage = "valid:x,x";
		echo $errorMessage;
		
    }
}
?>

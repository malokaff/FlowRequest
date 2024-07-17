<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div id="confirmReturn"></div>

<?php

include('../EW-demo-frontend/config.php');
$curl = curl_init();


function sendApiRequest($url, $headers, $body, $cookieFile, $method) {
	global $curl;

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $method,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_POSTFIELDS => $body,
		CURLOPT_COOKIEFILE => $cookieFile,
		CURLOPT_COOKIEJAR => $cookieFile,
		CURLOPT_HEADER => true
	));

	$response = curl_exec($curl);

	if (curl_errno($curl)) {
		echo curl_error($curl);
		die();
	}

	$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($http_code == intval(200)) {
		return $response;
	} else {
		return "Ressource introuvable : " . $http_code . $response;
	}

	curl_close($curl);
}

//display confirmation message
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"]=="toConfirm") {
    $source_ip = $_POST["source_ip"];
    $destination_ip = $_POST["destination_ip"];
    $rule_name = $_POST["rule_name"];
    $port = $_POST["port"];
	$protocol = substr($port, 0, 3); // Get the first 3 characters
	$portNumber = substr($port, 3); // Get the characters starting from the 4th position

	// IP addresses and port are valid
	// Proceed with further processing
	// For example:
	 echo "Rule Name: " . $rule_name . "<br>";
	 echo "Source IP: " . $source_ip . "<br>";
	 echo "Destination IP: " . $destination_ip . "<br>";
	 echo "Port: " . $port . "<br>";
	?>
	<form id="ruleForm" method="POST">
		<input type="hidden" name="source_ip" value="<?php echo htmlspecialchars($source_ip); ?> ">
		<input type="hidden" name="destination_ip" value="<?php echo htmlspecialchars($destination_ip); ?>">
		<input type="hidden" name="rule_name" value="<?php echo htmlspecialchars($rule_name); ?>">
		<input type="hidden" name="port" value="<?php echo htmlspecialchars($port); ?>">
		<input type="hidden" name="action" value="confirm">
		<input type="button" value="confirm Rule" id="addRuleButton">
	</form>

	<script>
	$(document).ready(function() {
		$("#addRuleButton").click(function() {
			if (confirm("Are you sure you want to add this rule?")) {
				$.ajax({
					url: "submit_firewall_rule.php",
					type: "POST",
					data: $("#ruleForm").serialize(),
					success: function(submitConfirm) {
					  // Handle the submission response if needed
					  $('#formSubmitReturn').html(submitConfirm);
					}
				});
			}
		});
	});
	</script>
	<?php
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"]=="confirm") {
    $source_ip = $_POST["source_ip"];
    $destination_ip = $_POST["destination_ip"];
    $rule_name = $_POST["rule_name"];
    $port = $_POST["port"];
	$protocol = substr($port, 0, 3); // Get the first 3 characters
	$portNumber = substr($port, 3); // Get the characters starting from the 4th position

	// IP addresses and port are valid
	// Proceed with further processing
	// For example:
	 echo "Rule Name: " . $rule_name . "<br>";
	 echo "Source IP: " . $source_ip . "<br>";
	 echo "Destination IP: " . $destination_ip . "<br>";
	 echo "Port: " . $port . "<br>";
	
	//authent API PSM
	//API Authentication:
	$url = 'https://'.$ip_psm.'/v1/login';
	$headers = array('Content-Type: text/plain');
	$body = '{"username": "'.$usr_PSM.'","password": "'.$pwd_PSM.'","tenant": "default"}';
	$response = sendApiRequest($url,$headers,$body,'./cookie.txt','POST'
	
	
	
	);
	//echo $response;
	
	//parse cookie info
	preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
	$cookies = array();
	foreach($matches[1] as $item) {
		parse_str($item, $cookie);
		$cookies = array_merge($cookies, $cookie);
	}
	//print_r($cookies);
	
	//2nd request to get rule
	$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/pod1-vrf-auto';
	$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
	$body = '{"username": "'.$usr_PSM.'","password": "'.$pwd_PSM.'","tenant": "default"}';
	$response = sendApiRequest($url,$headers,$body,'./cookie.txt','GET');
	//echo $response;
	//print_r($headers);
	// Your HTTP response containing JSON
	$httpResponse = $response;

	// Use regular expressions to extract JSON content from the response
	$pattern = '/\{.*\}/s'; // Pattern to match JSON data
	preg_match($pattern, $httpResponse, $matches);

	if (!empty($matches)) {
		$jsonContent = $matches[0];
		// Now $jsonContent contains the extracted JSON
		$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
		// Access and work with the extracted JSON data
		//var_dump($decodedJson);
	} else {
		echo "No JSON found in the HTTP response.";
	}
	// Assuming $jsonContent holds your extracted JSON
	// Decode JSON to PHP array
	$decodedJson = json_decode($jsonContent, true);

	if ($decodedJson && isset($decodedJson['spec']['rules'])) {
		$rules = $decodedJson['spec']['rules'];
		
		// Now $rules contains the "rules" array
		//var_dump($rules);
		
		// You can loop through the rules if it's an array
		//foreach ($rules as $rule) {
			// Access individual rule properties as needed
			//echo "<br>Rule name: " . $rule['name'] . "\n";
			//echo "<br>Action: " . $rule['action'] . "\n";
			// ... (access other properties of each rule)
		//}
	} else {
		echo "<br>No 'rules' found in the JSON content.";
				}

	// add new rule into json
	$newRule = [
		"proto-ports" => [
			[
				"protocol" => $protocol,
				"ports" => $portNumber
			]
		],
		"action" => "permit",
		"from-ip-addresses" => [$source_ip],
		"to-ip-addresses" => [$destination_ip],
		"name" => $rule_name
	];

	// Check if the 'rules' key exists in the JSON structure
	if (isset($decodedJson['spec']['rules'])) {
		// Add the new rule to the beginning of the existing rules array
		array_unshift($decodedJson['spec']['rules'], $newRule);
	} else {
		// If the 'rules' key doesn't exist, create it and add the new rule
		$decodedJson['spec']['rules'] = [$newRule];
	}

	// Convertir le tableau PHP modifié en JSON
	$updatedJson = json_encode($decodedJson, JSON_PRETTY_PRINT);

	// Maintenant, $updatedJson contient le JSON mis à jour avec la nouvelle règle ajoutée
	echo "<br><br>";
			
	//header("Refresh:0,url=submit_firewall_rule.php");
	
	//3rd request to add rule
	$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/pod1-vrf-auto';
	$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
	//$body='{"kind": "NetworkSecurityPolicy","meta": {"name": "autodeny","tenant": "default"},"spec": {"attach-tenant": true,"rules": [{"from-ip-addresses": ["'.$source_ip.'"],"to-ip-addresses": ["'.$destination_ip.'"],"proto-ports": [{ "protocol": "'.$protocol.'","ports": "'.$portNumber.'"}],"action": "permit","name": "'.$rule_name.'"},{"from-ip-addresses": ["any"],"to-ip-addresses": ["any"],"proto-ports": [{ "protocol": "any"}],"action": "permit","name": "permit_any"}]}}';
	$body=$updatedJson;
	
	$httpResponse = sendApiRequest($url,$headers,$body,'./cookie.txt','PUT');
	//echo $response;
	
	//collect status of the answer
	$pattern = '/\{.*\}/s'; // Pattern to match JSON data
	preg_match($pattern, $httpResponse, $matches);

	if (!empty($matches)) {
		$jsonContent = $matches[0];
		// Now $jsonContent contains the extracted JSON
		$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
	// Check if the 'status' key and 'propagation-status' key exist
	if (isset($decodedJson['status']['propagation-status'])) {
		$propagationStatus = $decodedJson['status']['propagation-status'];
		
		// Accessing specific fields within propagation-status
		$generationId = $propagationStatus['generation-id'];
		$updated = $propagationStatus['updated'];
		$pending = $propagationStatus['pending'];
		$status = $propagationStatus['status'];
		
		// Output or use the propagation status values as needed
		echo "Generation ID: " . $generationId . "<br>";
		echo "Updated: " . $updated . "<br>";
		echo "Pending: " . $pending . "<br>";
		echo "Status: " . $status . "<br>";
	} else {
		echo "No 'propagation-status' found in the JSON content.<br>";
		//echo $httpResponse;
		$result = $decodedJson['result'];
		$str = $result['Str'];
		$messages = $decodedJson['message'];
		 // Access the 'Str' value
		echo '<b><font color=red>';
		echo "Str: " . $str . "<br>";

		// Access the 'message' array
		echo "Messages: <br>";
		foreach ($messages as $message) {
			echo "- " . $message . "<br>";
			}
		echo '</font></b>';
		}		
		
	} else {
		echo "No JSON found in the HTTP response.";	
	//header("Refresh:0,url=submit_firewall_rule.php");
	}
}

?>

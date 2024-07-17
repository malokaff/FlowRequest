<!DOCTYPE html>
<html>
<head>
  <title>Firewall Rule Form</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h2>Firewall Rule Form</h2>

<form id="firewallForm" action="submit_firewall_rule.php" method="POST">

  <div class="form-group">
    <label for="rule_name">Rule Name:</label>
    <input type="text" id="rule_name" name="rule_name">
  </div>

  <div class="form-group">
    <label for="source_ip">Source IP Address:</label>
    <input type="text" id="source_ip" name="source_ip">
  </div>

  <div class="form-group" id="destination_ip_div" name="destination_ip_div">
    <label for="destination_ip">Destination IP Address:</label>
    <input type="text" id="destination_ip" name="destination_ip">
  </div>

  <div class="form-group">
    <label for="port">Port:</label>
    <input type="text" id="port" name="port">
  </div>

  <div class="form-group">
    <input type="submit" value="Submit">
  </div>
  <input type="hidden" name="action" value="toConfirm">
</form>

<div id="validationMessage" class="errorInput"></div>
<br>
<br>
<div id="formSubmitReturn"></div>

<script>
$(document).ready(function() {
  $('#firewallForm').submit(function(e) {
    e.preventDefault(); // Prevent the default form submission
    
    var formData = $(this).serialize(); // Serialize form data
    
    // AJAX request to validate input
   $.ajax({
      type: 'POST',
      url: 'validate_input.php',
      data: formData,
      success: function(response) {
        var parts = response.split(':');
        var errorMessage = parts[0];
        var errorFields = parts[1].split(',');

        $('#validationMessage').text(errorMessage);
		if (errorMessage === 'valid') {
          // Form data is valid, perform regular form submission
          //$('#firewallForm').unbind('submit').submit();
		  
        // Remove previous error classes
        //$('.error').removeClass('error');
		$('#destination_ip').css("border", "1px solid #ccc");
		$('#source_ip').css("border", "1px solid #ccc");
		$('#port').css("border", "1px solid #ccc");
		  
		$.ajax({
            type: 'POST',
            url: 'submit_firewall_rule.php', 
            data: formData,
            success: function(submitResponse) {
              // Handle the submission response if needed
              $('#formSubmitReturn').html(submitResponse);
            }
          });		  
		  
		} else {
		

        // Highlight fields with errors
        errorFields.forEach(function(field) {
			//alert(field);
		  var element = document.getElementById(field);
		  //alert(element);
		  //element.style.border = "1px solid red";
          //$('#' + field.trim()).addClass('error');
		  $('#' + field.trim()).css("border", "1px solid red");
        });
      } 
	 }
    });
  });
});
</script>


</body>
</html>

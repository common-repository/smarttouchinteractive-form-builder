<?php

if (file_exists(plugin_dir_path( __FILE__ ))."cptkey.php"){
$cptcode = fopen(plugin_dir_path( __FILE__ )."cptkey.php", "r") or die("Unable to open file!");
$cptkey=fread($cptcode,filesize(plugin_dir_path( __FILE__ )."cptkey.php"));
fclose($cptcode);
}
?>
<script src="https://www.google.com/recaptcha/api.js?onload=CaptchaCallback&render=explicit" async defer></script>
<script>
var CaptchaCallback = function() {    
	var ids = jQuery('.recaptchaST').map(function() {
	return jQuery(this).attr('id');
	});
    for (var i=0;i<ids.length;i++)
      {
          grecaptcha.render(ids[i], {
          'sitekey' : '<?php echo $cptkey; ?>',
          'callback' : ids[i]
          }); 
      }    
};

var st_element1= function(response){ 
cptValidation1(response);
 };
var st_element2= function(response){ 
cptValidation2(response);
 };
var st_element3= function(response){ 
cptValidation3(response);
 };
var st_element4= function(response){ 
cptValidation4(response);
 };

 var st_element5= function(response){ 
cptValidation5(response);
 };
 var st_element6= function(response){ 
cptValidation6(response);
 };
 var st_element7= function(response){ 
cptValidation7(response);
 };
 var st_element8= function(response){ 
cptValidation8(response);
 };
 var st_element9= function(response){ 
cptValidation9(response);
 };
 var st_element10= function(response){ 
cptValidation10(response);
 };

</script>

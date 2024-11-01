<?php if ( ! defined( 'ABSPATH' ) ){ exit;}
error_reporting(0);
if (file_exists(plugin_dir_path( __FILE__ ))."hphp.php"){
$hphp = fopen(plugin_dir_path( __FILE__ )."hphp.php", "r") or die("Unable to open file!");
$hpval=fread($hphp,filesize(plugin_dir_path( __FILE__ )."hphp.php"));
fclose($hphp);
}

if (file_exists(plugin_dir_path( __FILE__ ))."hscript.php"){
$hscript = fopen(plugin_dir_path( __FILE__ )."hscript.php", "r") or die("Unable to open file!");
$hsval=fread($hscript,filesize(plugin_dir_path( __FILE__ )."hscript.php"));
fclose($hscript);
}

if (file_exists(plugin_dir_path( __FILE__ ))."fphp.php"){
$fphp = fopen(plugin_dir_path( __FILE__ )."fphp.php", "r") or die("Unable to open file!");
$fpval=fread($fphp,filesize(plugin_dir_path( __FILE__ )."fphp.php"));
fclose($fphp);
}

if (file_exists(plugin_dir_path( __FILE__ ))."fscript.php"){
$fscript = fopen(plugin_dir_path( __FILE__ )."fscript.php", "r") or die("Unable to open file!");
$fsval=fread($fscript,filesize(plugin_dir_path( __FILE__ )."fscript.php"));
fclose($fscript);
}


if (file_exists(plugin_dir_path( __FILE__ ))."fophp.php"){
$fophp = fopen(plugin_dir_path( __FILE__ )."fophp.php", "r") or die("Unable to open file!");
$foval=fread($fophp,filesize(plugin_dir_path( __FILE__ )."fophp.php"));
fclose($fophp);
}

if (file_exists(plugin_dir_path( __FILE__ ))."nextgen.php"){
$nextgen = fopen(plugin_dir_path( __FILE__ )."nextgen.php", "r") or die("Unable to open file!");
$nextgen_code=fread($nextgen,filesize(plugin_dir_path( __FILE__ )."nextgen.php"));
fclose($nextgen);
}

if (file_exists(plugin_dir_path( __FILE__ ))."cptkey.php"){
$cptcode = fopen(plugin_dir_path( __FILE__ )."cptkey.php", "r") or die("Unable to open file!");
$cptkey=fread($cptcode,filesize(plugin_dir_path( __FILE__ )."cptkey.php"));
fclose($cptcode);
}

?>
<script>
function viewCode()
{
	window.open("http://www.javascript-coder.com","mywindow","menubar=1,resizable=1,width=550,height=450");
}	
</script>

<div id="dvLoading" class="blind-txt" ></div>

<div id="lightlist0" class="lightlist2" style="">
<div class="popupcode">
<textarea name="fo_code" rows="13" cols="99" placeholder="<?php echo"<?php code.... ?>" ?>" ><?php echo $nextgen_code; ?></textarea>
<br>

<div class="okbtnwrap">
<input id="closebtn0" class="closebtnok" type="button" value="Close" onclick="light_cl()" >
</div>
</div>
</div>
<div id="fadelist" style="display: ;"></div>
<form name="add_hf" id="smart-touch-form-builder-add-code" action="<?php echo get_admin_url(); ?>admin.php?page=sti-header-footer&hf=update" method="post">
	<table class="form-table">
		<tbody>

			<tr valign="top">
				<th scope="row"><label for="form-name"><?php _e( 'Google Captcha Key' , 'smart-touch-form-builder'); ?></label></th>
				<td>
                  <input type="text" name="captch_key" size="60" placeholder="Captch Key" value="<?php echo $cptkey; ?>">
				<p class="description"><?php _e( 'Add Google Captcha Key' , 'smart-touch-form-builder'); ?></p>	
				</td>
			</tr>
		<tr valign="top">
				<th scope="row"><label for="form-name"><?php _e( 'Header Script' , 'smart-touch-form-builder'); ?></label></th>
				<td>
                  <textarea name="hp_code" rows="6" cols="40" placeholder="<?php echo"<?php code.... ?>" ?>"><?php echo $hpval; ?></textarea>
				<p class="description"><?php _e( 'Add PHP Code' , 'smart-touch-form-builder'); ?></p>	
				</td>
				<td>
                  <textarea name="hs_code" rows="6" cols="40" placeholder="<script> code.... </script>"><?php echo $hsval;  ?></textarea>
				<p class="description"><?php _e( 'Add Script' , 'smart-touch-form-builder'); ?></p>	
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="form-email-sender-name"><?php _e( 'Footer Script' , 'smart-touch-form-builder'); ?></label></th>
				<td>
					 <textarea name="fp_code" rows="6" cols="40" placeholder="<?php echo"<?php code.... ?>" ?>" ><?php echo $fpval; ?></textarea>

					<p class="description"><?php _e( 'Add PHP Code' , 'smart-touch-form-builder'); ?></p>
				</td>
				<td>
					 <textarea name="fs_code" rows="6" cols="40" placeholder="<script> code.... </script>" ><?php echo $fsval;  ?></textarea>

					<p class="description"><?php _e( 'Add Script' , 'smart-touch-form-builder'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="form-email-sender-name"><?php _e( 'NextGen Code' , 'smart-touch-form-builder'); ?></label></th>
				<td colspan="2">
					 <textarea name="fo_code" rows="12" cols="99" placeholder="<?php echo"<?php code.... ?>" ?>" ><?php echo $foval; ?></textarea>
					<p class="description" onclick="light_open(0)" style="cursor:pointer;font-weight:bold;color:#23282d;" >Click to View Tracking Help Code</span>
					</p>
				</td>
				
			</tr>
				<tr valign="top">
					<th scope="row">&nbsp;</th>
					<td>
						<p class="submit">
					<input id="submit" class="button button-primary" type="submit" value="Save" onclick="jQuery('#dvLoading').show();" name="hfdata">
					</p>
					</td>
				</tr>
		</tbody>
	</table>



</form>



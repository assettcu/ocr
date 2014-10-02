<?php
/**
 * Login page
 */
$this->pageTitle="OCR Service";

$imager = new Imager("images/lock.png");
$imager->width = "16px";
$imager->height = "16px";
$imager->attributes["title"] = "This password is passed through 128-bit AES encryption for authentication.";

$flashes = new Flashes();
$flashes->render();
?>

<div style="margin-top:25px;">
    <div class="image-container" style="float:left;display:block;width:110px;text-align:center;">
    	<img src="<?=Yii::app()->baseUrl?>/images/ocr.png" alt="Optical Character Recognition Service" />
    </div>
    <div class="text-container" style="float:left;display:block;width:750px;margin-bottom: 25px; font-size:14px;">
    	<h1>Welcome to the Optical Character Recognition Service</h1>
    	This website provides a service to help make PDFs text searchable. In addition, this service will clean, rotate and properly align the PDF files. You first upload PDFs to the service, then they are processed with our software, then they will become available for download.<br/>
    </div>
</div>

<br class="clear" />

<center>
    <form method="post">
        <input type="hidden" name="propertyform" />
        <table id="post-form-table">
            <tr>
                <th width="150px"><div <?php echo ($error == "username") ? 'class="error"' : ''; ?>> <span class="icon icon-user3"> </span> Identikey Username</div></th>
                <td><input type="text" name="username" id="username" value="<?php @$_REQUEST["username"]; ?>" maxlength="8" /></td>
            </tr>
            <tr>
                <th><div <?php echo ($error == "password") ? 'class="error"' : ''; ?>>  <span class="icon icon-key2"> </span> Identikey Password</div></th>
                <td>
                    <input type="password" name="password" id="password" value="" /> <?php $imager->render(); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="calign"><button id="submit" class="submit" style="font-size:12px;">Continue to OCR Service &gt;</button></td>
            </tr>
        </table>
    </form>
</center>

<script>
jQuery(document).ready(function(){
    $("#submit").button();
    $("#submit").click(function(){
        $(this).removeClass("ui-state-hover");
        $(this).addClass("disabled");
        $(this).prop("value","Logging in...");
        $("#login-form").submit();
        return true;
    });
});
</script>
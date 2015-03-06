<?php
$flashes = new Flashes();
$flashes->render();
?>

<div style="margin-top:10px;">
    <div>
        <h1 class="type1">Welcome to the <?php echo Yii::app()->name; ?> Service</h1>
        This website provides a service to help make PDFs text searchable. In addition, this service will clean, rotate and properly align the PDF files. You first upload PDFs to the service, then they are processed with our software, then they will become available for download.<br/>
    </div>
</div>

<br class="clear" />

<div class="row top-bottom-padding-3">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Login</h3>
            </div>
            <div class="panel-body">
                <form method="post">
                    <div id="credentialsBody" class="col-md-8 col-md-offset-2">
                    <div class="form-group">
                        <label for="identikeyInput">Identikey</label>
                        <input type="text" class="form-control" name="username" id="username" placeholder="Identikey">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                    </div>
                    <button id="loadMailboxesButton" class="btn-block btn btn-primary">Login</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
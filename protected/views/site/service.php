<?php
StdLib::set_debug_state("Dev");
$manager = new Manager();
$ncfiles = $manager->load_files("NC");
$files = $manager->load_files("C");

$flashes = new Flashes;
$flashes->render();
?>

<!-- Load Queue widget CSS and jQuery -->
<style type="text/css">
    @import url(//<?php echo WEB_LIBRARY_PATH; ?>/jquery/modules/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css);
</style>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<script type="text/javascript" src="//<?php echo WEB_LIBRARY_PATH; ?>/jquery/modules/plupload/js/plupload.full.js"></script>
<script type="text/javascript" src="//<?php echo WEB_LIBRARY_PATH; ?>/jquery/modules/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

<div class="main-frame">

    <div class="ocr-container">
        <div class="title" style="font-size:24px;margin-bottom:5px;">Upload PDF Files</div>
        <div id="html5_uploader">You browser doesn't support native upload. Try Firefox 3 or Safari 4.</div>
    </div>
    
    <div class="queued-files-container ocr-container">
        <div class="title">Queued Files</div>
        <div style="float:right;" id="countdown"></div>
        
        <div style="margin-bottom:5px;">selected: <a href="#" id="queued-mass-remove">remove</a></div>
        
        <table id="queued-files">
          <thead>
            <tr>
              <th class="calign" style="width:30px;">
              	<label for="checkall-queued" style="display:none;">Check All Queued</label>
              	<input type="checkbox" name="checkall-queued" id="checkall-queued" />
              </th>
              <th>File Name</th>
              <th class="calign" style="width:94px;">File Size</th>
              <th class="calign" style="width:163px;">Date Uploaded</th>
              <th class="calign" style="width:97px;">Action</th>
            </tr>
          </thead>
          <tbody>
          	<?php if(empty($ncfiles)): ?>
          		<tr>
          			<td colspan="5" class="calign mvalign" style="padding:10px;border:2px solid #ccc;background-color:#f0f0f0;">
          				You currently have no files that are in the queue.
          			</td>
          		</tr>
          	<?php else: ?>
    	        <?php foreach($ncfiles as $file): ?>
    	        <tr>
    	          <td class="calign">
    	          	<?php if($file->status=="Queued" || $file->status=="Failed"): ?>
    	          		<label for="<?=$file->fileid;?>" style="display:none;">Select File <?=$file->fileid;?>:</label>
    	          		<input type="checkbox" name="queued-boxes" value="<?=$file->fileid?>" />
    	          	<?php endif; ?>
    	          </td>
    	          <td><?=$file->filename?></td>
    	          <td class="calign"><?=formatBytes($file->filesize);?></td>
    	          <td class="calign"><?=StdLib::format_date($file->date_uploaded,"nice");?></td>
    	          <td class="calign" id="status-<?=$file->fileid?>" value="<?=$file->status?>"><?=$file->get_status_text();?></td>
    	        </tr>
    	        <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
    </div>
  
  <div class="processed-files-container">
    <div class="title" style="font-size:24px;margin-bottom:5px;">Processed Files</div>
    <div style="margin-bottom:5px;">selected: <a href="#" id="processed-mass-remove">remove</a> | <a href="#" id="processed-mass-reprocess">reprocess</a> | <a href="#" id="processed-mass-download">download</a></div>
    <table id="processed-files">
      <thead>
        <tr>
          <th class="calign" style="width:30px;">
          	<label for="checkall-processed" style="display:none;">Check All Processed</label>
          	<input type="checkbox" name="checkall-processed" id="checkall-processed" />
          </th>
          <th>File Name</th>
          <th class="calign" style="width:94px;">File Size</th>
          <th class="calign" style="width:163px;">Date Completed</th>
          <th class="calign" style="width:97px;">Action</th>
        </tr>
      </thead>
      <tbody>
      	<?php if(empty($files)): ?>
      		<tr id="processed-empty">
      			<td colspan="5" class="calign mvalign" style="padding:10px;border:2px solid #ccc;background-color:#f0f0f0;">
      				You currently have no files that have been processed.
      			</td>
      		</tr>
      	<?php else: ?>
	        <?php foreach($files as $file): ?>
	        <tr>
	          <td class="calign">
          		<label for="<?=$file->fileid;?>" style="display:none;">Select File <?=$file->fileid;?>:</label>
            	<input type="checkbox" name="processed-boxes" value="<?=$file->fileid?>" id="<?=$file->fileid;?>" />
	          </td>
	          <td><?=$file->filename?></td>
	          <td class="calign"><?=formatBytes($file->filesize,2);?></td>
	          <td class="calign"><?=StdLib::format_date($file->date_completed,"nice");?></td>
	          <td class="calign">
	            <?php if($file->status!="Expired"): ?>
	            <button class="download-button" title="Download Processed PDF" value="<?=$file->fileid?>">Download</button>
	            <?php else: ?>
	            <span style="color:#a00;">Expired</span>
	            <?php endif; ?>
	          </td>
	        </tr>
	        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
</div>

<div id="remove-mass-dialog" title="Remove Processed Files?">
  <p>
    Are you sure you wish to remove the selected processed files? There are <span id="num-selected">0</span> files selected.
  </p>
</div>

<div id="stats-dialog" title="Statistics">
  <table>
    <tr>
      <td>Number of Files Uploaded</td>
      <td id="num-files-uploaded">0</td>
    </tr>
    <tr>
      <td>Number of Downloads</td>
      <td id="num-files-downloaded">0</td>
    </tr>
    <tr>
      <td>Number of Failed Files</td>
      <td id="num-files-downloaded">0</td>
    </tr>
    <tr>
      <td>Average File Size</td>
      <td id="num-files-downloaded">0</td>
    </tr>
    <tr>
      <td>Average Time to Process</td>
      <td id="num-files-downloaded">0</td>
    </tr>
  </table>
</div>

<script>
var updateFilesTimer;
var uploader;
var semaphore = 0;
jQuery(document).ready(function($){
  
    if($("table#queued-files tbody tr").length > 0 && $("table#queued-files tbody").find("#processed-empty").length == 0) {
        updateFilesTimer = window.setInterval('updateFiles()',1300);
    } else {
    	window.clearInterval(updateFilesTimer);
    }
  
    uploader = $('#html5_uploader').pluploadQueue({
       runtimes:        'html5',
       container:       'html5_uploader',
       url:             '<?php echo Yii::app()->createUrl('_upload_files'); ?>',
       max_file_size:   '25mb',
       chunk_size:      '1mb',
       unique_names:    false,
       browse_button:   'Select Images',
       filters:         [{
            title:          "OCR Files",
            extensions:     "pdf"
       }]
    });
    
    var uploader = $('#html5_uploader').pluploadQueue();
    uploader.bind('UploadComplete', function(up,files){
        // $('form')[0].submit();
        files.forEach(function(element, index, array){
        	console.log(element);
            if(element.status == 5) {
                semaphore = semaphore + 1;
                $.ajax({
                    url:        "<?php echo Yii::app()->createUrl('_add_uploaded_file'); ?>",
                    data:       "filename="+escape(element["name"])+"&size="+escape(element["size"]),
                    success:    function(){
                        semaphore = semaphore - 1;
                    }   
                });
            }
        });
        setInterval(function(){if(semaphore==0){ window.location.reload(); }},1000);
    });
    
  
  $(document).on('click',"#upload-file-button",function(){
    $("#upload-dialog").dialog("open");
    return false;
  });
  
  $("#remove-mass-dialog").dialog({
    "autoOpen":   false,
    "modal":      true,
    "width":      340,
    "height":     160,
    "draggable":  true,
    "resizable":  false,
    "buttons":    {
      "Nevermind":    function(){
        $(this).dialog("close");
      },
      "Remove Files": function(){
        var ids = "";
        $.each($("input[type='checkbox'][name='processed-boxes']:checked"),function(index){
          ids += $(this).attr('value')+",";
        });
        ids = ids.substring(0,ids.length-1);
        $.ajax({
          "url":      "<?=Yii::app()->createUrl('_remove_files');?>",
          "data":     "fileids="+ids,
          "success":  function(data)
          {
            if(data==1)
            {
              window.location.reload();
              return false;
            }
            else
            {
              alert(data);
            }
          }
        });
      }
    }
  });
  
  $("#stats-dialog").dialog({
    "autoOpen":   false,
    "modal":      false,
    "width":      340,
    "height":     160,
    "draggable":  true,
    "resizable":  false
  });
  
  $(document).on('click',"#processed-mass-reprocess",function(){
    if($("input[type='checkbox'][name='processed-boxes']:checked").length==0) return false;
    var ids = "";
    $.each($("input[type='checkbox'][name='processed-boxes']:checked"),function(index){
      ids += $(this).attr('value')+",";
    });
    ids = ids.substring(0,ids.length-1);
    $.ajax({
      "url":    "<?=Yii::app()->createUrl('_reprocess_files');?>",
      "data":   "fileids="+ids,
      "success":  function(data){
        window.location.reload();
      }
    });
  });
  
  $(document).on('click',"#queued-mass-remove",function(){
    if($("input[type='checkbox'][name='queued-boxes']:checked").length==0) return false;
    var ids = "";
    $.each($("input[type='checkbox'][name='queued-boxes']:checked"),function(index){
      ids += $(this).attr('value')+",";
    });
    ids = ids.substring(0,ids.length-1);
    $.ajax({
      "url":    "<?=Yii::app()->createUrl('_remove_files');?>",
      "data":   "fileids="+ids,
      "success":  function(data){
        window.location.reload();
      }
    });
  });
  
  $(document).on('click',"#processed-mass-remove",function(){
    if($("input[type='checkbox'][name='processed-boxes']:checked").length==0) return false;
    $("#num-selected").html($("input[type='checkbox'][name='processed-boxes']:checked").length);
    $("#remove-mass-dialog").dialog("open");
     $('.ui-dialog :button').blur();
  });
  
  $(document).on('click',"#processed-mass-download",function(){
    if($("input[type='checkbox'][name='processed-boxes']:checked").length==0) return false;
    if($("input[type='checkbox'][name='processed-boxes']:checked").length==1)
    {
      window.location = "<?=Yii::app()->createUrl('download')?>?fileid="+$("input[type='checkbox'][name='processed-boxes']:checked").attr('value');
      return false;
    }
    var ids = "";
    $.each($("input[type='checkbox'][name='processed-boxes']:checked"),function(index){
      ids += $(this).attr('value')+",";
    });
    ids = ids.substring(0,ids.length-1);
    window.location = "<?=Yii::app()->createUrl('massdownload');?>?fileids="+ids;
    return false;
    
  });
  
  $(document).on('click',".download-button",function(){
    window.location = "<?=Yii::app()->createUrl('download')?>?fileid="+$(this).attr('value');
    return false;
  });
  
  $(document).on('change',"#checkall-queued",function(){
    if($(this).is(":checked")){
      $("input[type='checkbox'][name='queued-boxes']").attr('checked','checked');
    } else {
      $("input[type='checkbox'][name='queued-boxes']").removeAttr('checked');
    }
  });
  $(document).on('change',"#checkall-processed",function(){
    if($(this).is(":checked")){
      $("input[type='checkbox'][name='processed-boxes']").attr('checked','checked');
    } else {
      $("input[type='checkbox'][name='processed-boxes']").removeAttr('checked');
    }
  });

});

var flag = false;
    
function updateFiles()
{
  $.ajax({
    "url":      "<?=Yii::app()->createUrl('_update_files');?>",
    "dataType": "JSON",
    "success":  function(data){
      flag = true;
      $.each(data,function(key,value){
        if($("#status-"+key).attr("value")=="Processing" && value["status"] == "Completed")
        {
          $("#status-"+key).parent().fadeOut('slow',function(){ 
          		$(this).remove();
				if($("#queued-files tbody tr").length == 0) {
					$("#queued-files tbody").prepend("<tr><td colspan=\"5\" class=\"calign mvalign\" style=\"padding:10px;border:2px solid #ccc;background-color:#f0f0f0;\">You currently have no files that are in the queue.</td></tr>")
				}
          });
          $.ajax({
            "url":      "<?=Yii::app()->createUrl('_load_processed_files');?>",
            "data":     "fileid="+key,
            "success":   function(data){
              $("#processed-empty").remove();
              $("#processed-files tbody").prepend(data);
              $("#processed-files button").button(); 
            }
          });
        }
        else
        {
          $("#status-"+key).attr("value",value["status"]);
          $("#status-"+key).html(value["disp_status"]);
          if(value["status"]=="Processing" || value["status"]=="Queued")
          {
            flag = false;
            if(value["status"]=="Processing")
            $("#status-"+key).parent().find("input").remove();
          }
        }
      });
    },
    "error": 	function(data) {

    }
  });
  if(flag===true)
  {
    window.clearInterval(updateFilesTimer);
  }
}
</script>
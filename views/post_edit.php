<?php
if (!isset($title)) $title = "";
if (!isset($content)) $content = "";
if (!isset($prompt_color)) $prompt_color = "w3-green";
?>
<div id="publishModal" class="w3-modal w3-animate-opacity">
  <div class="w3-modal-content">
    <header class="w3-container <?=$prompt_color?>">
      <span onclick="document.getElementById('publishModal').style.display = 'none'" class="w3-button w3-hover-blue w3-display-topright">
        <i class="fa fa-times"></i>
      </span>
      <h2>Publish?</h2>
    </header>
    <div class="w3-container">
      <p>Are you sure you want to publish this content?</p>
      <div class="w3-right">
        <button onclick="document.getElementById('publishModal').style.display = 'none'" class="w3-margin w3-button w3-hover-blue w3-green">No</button>
        <button onclick="document.getElementById('publishModal').style.display = 'none';saveAndPublish();" class="w3-margin w3-button w3-hover-blue w3-green">Yes</button>
      </div>
    </div>
  </div>
</div>
<div id="deleteModal" class="w3-modal w3-animate-opacity">
  <div class="w3-modal-content">
    <header class="w3-container w3-red">
      <span onclick="document.getElementById('deleteModal').style.display = 'none'" class="w3-button w3-hover-blue w3-display-topright">
        <i class="fa fa-times"></i>
      </span>
      <h2>Delete?</h2>
    </header>
    <div class="w3-container">
      <p>Are you sure you want to delete this content?</p>
      <div class="w3-right">
        <button onclick="document.getElementById('deleteModal').style.display = 'none'" class="w3-margin w3-button w3-hover-blue w3-green">No</button>
        <button onclick="document.getElementById('deleteModal').style.display = 'none';deletePost();" class="w3-margin w3-button w3-hover-blue w3-red">Yes</button>
      </div>
    </div>
  </div>
</div>
<?php
$atts = array("id" => "postForm");
echo form_open($callback, $atts);
?>
<div class="w3-container">
  <input name="title" placeholder="Title" class="w3-input w3-margin-bottom w3-margin-top w3-border w3-round" type="text" value="<?=$title?>"/>
  <textarea id="edi" name="editor">
  </textarea>
</div>
<?php
$ci =& get_instance();
$ci->load->splint("francis94c/ci-parsedown", "+Parsedown", null, "parsedown");
?>
<div class="w3-padding w3-margin w3-border w3-round" id="preview">
  <?=$ci->parsedown->text($content)?>
</div>
<div id="content" style="display:none;">
<?=$content?>
</div>
<script type="text/javascript">
var simpleMDE = new SimpleMDE(
  {
    element: document.getElementById("edi")
  }
);
simpleMDE.value(document.getElementById("content").innerHTML);
//var markdown = simpleMDE.value();
//markdown = markdown.replace(/    /g, "  \r\n");
//simpleMDE.value(markdown);
/**
 * [save description]
 * @return [type] [description]
 */
function save() {
  document.getElementById("action").value = "save";
  document.getElementById("postForm").submit();
}
/**
 * [saveAndPublish description]
 * @return [type] [description]
 */
function saveAndPublish() {
  document.getElementById("action").value = <?=$type == "edit" ? "\"publish\"" : "\"createAndPublish\"";?>;
  document.getElementById("postForm").submit();
}
/**
 * [promptPublish description]
 * @return [type] [description]
 */
function promptPublish() {
  document.getElementById("publishModal").style.display = "block";
}
/**
 * [promptDelete description]
 * @return [type] [description]
 */
function promptDelete() {
  document.getElementById("deleteModal").style.display = "block";
}
/**
 * [delete description]
 * @return [type] [description]
 */
function deletePost() {
  document.getElementById("action").value = "delete";
  document.getElementById("postForm").submit();
}
</script>
<input type="hidden" name="id" value="<?=$type == "edit" ? $id : ""?>"/>
<input id="action" type="hidden" name="action" value="publish"/>
<?=form_close();?>
<button onclick="save();" class="w3-margin w3-button w3-teal w3-round w3-hover-theme"><?=$type == "edit" ? "Save" : "Create"?></button>
<button onclick="promptPublish();" class="w3-margin w3-button w3-teal w3-hover-theme w3-round"><?=$type == "edit" ? "Save and Publish" : "Create and Publish"?></button>
<?php if ($type == "edit") {?>
<button onclick="promptDelete();" class="w3-margin w3-button w3-red w3-hover-theme w3-round">Delete</button>
<?php }?>

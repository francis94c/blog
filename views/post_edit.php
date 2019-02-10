<div id="modal" class="w3-modal w3-animate-opacity">
  <div class="w3-modal-content">
    <header class="w3-container w3-green">
      <span onclick="document.getElementById('modal').style.display = 'none'" class="w3-button w3-hover-blue w3-display-topright">
        <i class="fa fa-times"></i>
      </span>
      <h2>Publish?</h2>
    </header>
    <div class="w3-container">
      <p>Are you sure you want to publish this article?</p>
      <div class="w3-right">
        <button onclick="document.getElementById('modal').style.display = 'none'" class="w3-margin w3-button w3-hover-blue w3-green">No</button>
        <button onclick="document.getElementById('modal').style.display = 'none';saveAndPublish();" class="w3-margin w3-button w3-hover-blue w3-green">Yes</button>
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
simpleMDE.value(document.getElementById("content")).innerHTML;
//var markdown = simpleMDE.value();
//markdown = markdown.replace(/    /g, "  \r\n");
//simpleMDE.value(markdown);

function save() {
  document.getElementById("action").value = <?=$type == "edit" ? "\"save\"" : "\"create\"";?>;
  document.getElementById("postForm").submit();
}

function saveAndPublish() {
  document.getElementById("action").value = <?=$type == "edit" ? "\"publish\"" : "\"createAndPublish\"";?>;
  document.getElementById("postForm").submit();
}

function promptPublish() {
  document.getElementById("modal").style.display = "block";
}
</script>
<input type="hidden" name="id" value="<?=$type == "edit" ? $id : ""?>"/>
<input id="action" type="hidden" name="action" value="publish"/>
<?=form_close();?>
<button onclick="save();" class="w3-margin w3-button w3-theme-d2 w3-round w3-hover-theme"><?=$type == "edit" ? "Save" : "Create"?></button>
<button onclick="promptPublish();" class="w3-margin w3-button w3-theme-d2 w3-hover-theme w3-round"><?=$type == "edit" ? "Save and Publish" : "Create and Publish"?></button>

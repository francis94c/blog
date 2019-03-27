<a href="<?=site_url($callback)?>" style="text-decoration:none;">
  <div class="w3-container w3-card w3-round w3-margin w3-padding w3-animate-opacity">
    <h1><?=$title?></h1>
    <div class="w3-border w3-padding w3-round">
      <p class="text-size w3-padding"><?=$content?></p>
    </div>
    <div class="w3-right">
      <p class="w3-text-gray">
        <?php if ($filter) {?>
        <span class="w3-text-<?=$published == 1 ? "green" : "black"?>">
          <i class="fa fa-stop"></i>
        </span>
        <?php }?>
        <?=$published == 1 ? "Published " : "Created "?><?=$date_time?>
      </p>
    </div>
  </div>
</a>

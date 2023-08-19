<h1>Server meta for [ <?php echo $guild["name"]; ?> ]</h1>
<p><?php echo $guild["description"]; ?></p>

<?php foreach( $guild as $key => $value): ?>
    <p><?php echo "<b>" . $key . "</b>\t" . (is_array($value) ? "[" . implode(", ", $value) . "]" : $value); ?></p>
<?php endforeach; ?>

<!-- <h3>Features</h3>
<p>[<?php echo implode(", ", $guild["features"]); ?>]</p>
<h3>Roles</h3> -->
<b>[roleId] roleName</b>
<?php foreach($guild["roles"] as $idx=>$role): ?>
<p><?php echo "[" . $role["id"] . "]\t" . $role["name"]; ?></p>
<?php endforeach; ?>
<h3>Channels</h3>
<b>[channelId] channelName</b>
<?php foreach($channels as $key=>$channel): ?>
<p><?php echo "[" . $channel["id"] . "]\t" . $channel["name"]; ?></p>
<?php endforeach; ?>

<div>action</div>
<div></div>
<div>user action</div>
<div>user details</div>
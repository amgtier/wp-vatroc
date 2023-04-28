<label>
    <p  class="input-label"><?php echo $label; ?></p>
    <input type="hidden" name="<?php echo $name; ?>" />
    <?php foreach( $options as $key => $val ): ?>
        <p> <?php echo $key . $val; ?> </p>
    <?php endforeach; ?>
</label>
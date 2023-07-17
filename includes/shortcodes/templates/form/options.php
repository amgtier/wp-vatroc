<label>
    <p  class="input-label"><?php echo $label . ($required ? "<span class=required>*</span>" : null); ?></p>
    <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
    <p><?php echo $value; ?></p>
</label>
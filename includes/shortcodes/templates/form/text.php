<label>
    <p class="input-label"><?php echo $label; ?></p>
    <?php if ( $read_only ): ?>
        <p class="view-form-value"><?php echo $value; ?></p>
    <?php else: ?>
        <input 
            type="text" 
            placeholder="<?php echo $placeholder; ?>" 
            class="form <?php echo $autosave; ?>"
            name="<?php echo $name; ?>"
            value="<?php echo $value; ?>"
            <?php echo $required; ?>
            <?php echo $disabled; ?>
        />
    <?php endif; ?>
    <span class="toggle-value"></span>
</label>
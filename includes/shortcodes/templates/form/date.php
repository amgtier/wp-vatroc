<label>
    <?php echo $label; ?>
    <?php if ( $read_only ): ?>
        <p class="view-form-value"><?php echo $value; ?></p>
    <?php else: ?>
        <input 
            type="date" 
            placeholder="<?php echo $placeholder; ?>"
            class="form <?php echo $autosave; ?>"
            name="<?php echo $name; ?>"
            value="<?php echo $value; ?>"
            <?php echo $required; ?>
            <?php echo $disabled; ?>
        />
    <?php endif; ?>
</label>
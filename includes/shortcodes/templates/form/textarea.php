<label>
    <?php echo $label . ($required ? "<span class=required>*</span>" : null); ?>
    <?php if ( $read_only ): ?>
        <p class="view-form-value"><?php echo $value; ?></p>
    <?php else: ?>
        <textarea 
            placeholder="<?php echo $placeholder; ?>"
            class="form <?php echo $autosave; ?>"
            name="<?php echo $name; ?>"
            <?php echo $required; ?>
            <?php echo $disabled; ?>
        ><?php echo $value; ?></textarea>
    <?php endif; ?>
</label>
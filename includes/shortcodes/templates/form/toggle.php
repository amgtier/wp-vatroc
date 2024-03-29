<label>
    <p class="input-label"><?php echo $label . ($required ? "<span class=required>*</span>" : null); ?></p>
    <label class="form toggle-switch <?php echo $autosave; ?>">
    <?php if ( $read_only ): ?>
        <!-- <span class="toggle-slider round read-only <?php echo $value ? "checked" : null; ?>"></span> -->
        <?php echo $value ? "Yes" : "No" ?>
    <?php else: ?>
        <input type="hidden" name="<?php echo $name; ?>" value="false" />
        <input 
            type="checkbox"
            name="<?php echo $name; ?>"
            <?php echo $value ? "checked" : null; ?>
            <?php echo $disabled; ?>
        />
        <span class="toggle-slider round"></span>
        <span class="toggle-value" data-name="<?php echo $name; ?>"></span>
    <?php endif; ?>
    </label>
</label>
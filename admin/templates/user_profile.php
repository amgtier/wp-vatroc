<h3 id="profile-vatroc-tool">VATROC Tool</h3>
  <table class="form-table">
    <tr>
      <th>
            <label for="vatsim_uid">VATSIM UID</label>
      </th>
      <td>
        <input name="vatsim_uid" type="text" id="vatsim_uid" value="<?php echo get_user_meta( $user->ID, "{$meta_prefix}vatsim_uid", true ); ?>">
        </td>
    </tr>
    <tr>
      <th>
            <label for="vatsim_rating">VATSIM Rating</label>
      </th>
      <td>
<?php if ( $can_edit ) : ?>
        <select name="vatsim_rating" id="vatsim_rating" >
            <option disabled val=""></option>
<?php
            foreach( VATROC::$vatsim_rating as $key => $val ) {
                $selected = $vatsim_rating == $key ? "selected" : "";
                echo "<option value='{$key}' {$selected} >{$val}</option>";
            }
?>
        </select>
        </td>
<?php else: echo VATROC::$vatsim_rating[ $vatsim_rating ]; ?>
<?php endif; ?>
    </tr>
    <tr>
      <th>
            <label for="position">Position</label>
      </th>
      <td>
<?php if ( $can_edit ) : ?>
        <select name="position" id="position" value="<?php echo get_user_meta( $user->ID, "vatroc_position", true ); ?>" <?php if ( !$can_edit ) echo "disabled"; ?>>
            <option disabled val=""></option>
<?php
            foreach( VATROC::$atc_position as $key=>$val ) {
                $selected = $position == $key ? "selected" : "";
                echo "<option value='{$key}' {$selected} >{$val}</option>";
            }
?>
        </select>
<?php else: echo VATROC::$atc_position[ $position ]; ?>
<?php endif; ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="staff_number">VATROC STAFF NUMBER</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "staff_number", "number" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="staff_role">VATROC STAFF ROLE</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "staff_role", "text" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="solo_valid_until">Solo Valid Until</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "solo_valid_until", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="home_division">Visiting Home Division</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "home_division", "text" ); ?>
        </td>
    </tr>
  </table>
  <h3 id="profile-vatroc-progress-dates">VATROC Progress Dates</h3>
  <table class="form-table">
    <?php foreach ( VATROC::$atc_dates_offline as $key => $label ): ?>
    <tr>
      <th>
            <label for="date_<?php echo $key; ?>"><?php echo $label; ?></label>
      </th>
      <td>
        <?php my_user_profile_maybe_can_edit( $user, "date_$key", "date" ); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php foreach ( VATROC::$atc_dates_in_sess as $key => $label ): ?>
    <tr>
      <th>
            <label for="date_<?php echo $key; ?>"><?php echo $label; ?></label>
      </th>
      <td>
        <?php my_user_profile_maybe_can_edit( $user, "date_$key", "date" ); ?>
        </td>
    </tr>
    <?php endforeach; ?>
  </table>
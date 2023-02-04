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
    <tr>
      <th>
            <label for="date_application">Application Date</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_application", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_exam">Exam Date</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_exam", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_del_sim">Date DEL SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_del_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_del_ojt">Date DEL OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_del_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_del_cpt">Date DEL CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit($user,  "date_del_cpt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_gnd_sim">Date GND SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_gnd_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_gnd_ojt">Date GND OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_gnd_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_gnd_cpt">Date GND CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_gnd_cpt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_twr_sim">Date TWR SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_twr_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_twr_ojt">Date TWR OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_twr_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_twr_cpt">Date TWR CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_twr_cpt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_app_sim">Date APP SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_app_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_app_ojt">Date APP OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_app_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_app_cpt">Date APP CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_app_cpt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_ctr_sim">Date CTR SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_ctr_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_ctr_ojt">Date CTR OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_ctr_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_ctr_cpt">Date CTR CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_ctr_cpt", "date" ); ?>
        </td>
    </tr>
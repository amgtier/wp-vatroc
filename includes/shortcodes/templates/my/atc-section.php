<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
?>

<?php if ( $vatsim_uid != null ): ?>
<div class="btn btn-primary"><?php echo $vatsim_uid; ?> </div>
<div class="btn btn-primary"><?php echo VATROC_Constants::$vatsim_rating[ $vatsim_rating ]; ?> </div>
<?php endif; ?>
<div class="btn btn-primary"><?php echo VATROC_Constants::$atc_position[ $vatroc_position ]; ?> </div>

<?php if ( VATROC_My::get_vatroc_position($uid) > 0 ): ?>
  <?php foreach( VATROC_Event::get_next_events($uid) as $option => $result): ?>
  <div class='flexbox-row flexbox-start'>
    <div class='flexbox-column flexbox-nogap'>
      <?php echo $option . ' ' . $result[ "description" ]; ?>
    </div>
    <div class='flexbox-column flexbox-nogap'>
      <a href="/avail" target="_blank">
        <?php if ($result["user_accept"]): ?>
          <button type="button" class="res btn-default active" value="accept">v</button>
        <?php elseif ( $result["user_tentative"]): ?>
        <button type="button" class="res btn-default active" value="tentative">?</button>
        <?php elseif ( $result["user_reject"]): ?>
        <button type="button" class="res btn-default active" value="reject">x</button>
        <?php else : ?>
        <p>Not responded</p>
        <?php endif; ?>
      </a>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
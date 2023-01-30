<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

$is_admin = VATROC_Shortcode_Poll::is_admin();
?>

<?php if( $is_admin ): ?>
<form id="create-option" method="get" action="#">
    <label for="option">插入日期
        <input type="date" id="create-option" name="name" required />
        <button id="submit-option" hidden>新增選項</button>
    </label>
</form>
<?php endif; ?>

<div class="vatroc-poll flexbox flexbox-column">
  <?php foreach( VATROC_Shortcode_Poll::get_options() as $option => $result): ?>
  <div class="flexbox-row flexbox-start <?php echo $result[ "description" ] == null ?: "flexbox-active"; ?>">
    <div>
      <?php echo isset( $result[ $option ][ "user_accept"] ) ? $result[ $option ][ "user_accept"] : null; ?>
    </div>
    <div class="flexbox-column flexbox-nogap">
      <div class="nowrap">
        <button type="button" class="res btn-default <?php echo $result[ "user_accept"] ? "active" : ""; ?>" value="accept" name="<?php echo $option; ?>">v</button>
        <button type="button" class="res btn-default <?php echo $result[ "user_tentative"] ? "active" : ""; ?>" value="tentative" name="<?php echo $option; ?>">?</button>
        <button type="button" class="res btn-default <?php echo $result[ "user_reject"] ? "active" : ""; ?>" value="reject" name="<?php echo $option; ?>">x</button>
      </div>
      <div>
        <?php if (VATROC_Shortcode_Poll::is_admin()) : ?>
          <textarea placeholder="註記" data-name=<?php echo $option; ?> class="option-description"><?php echo $result[ "description" ]; ?></textarea>
        <?php else:?>
          <p class="text-center">
            <?php echo $result[ "description" ] ?>
          </p>
        <?php endif;?>
      </div>
    </div>
<?php if( $is_admin ): ?>
  <div class="flexbox-column">
    <div>
      <?php echo $option; ?>
    </div>
    <div>
      <a href="#" class="hide-option" data-name=<?php echo $option; ?>><?php echo $result[ "hidden" ] ? "unhide" : "hide"; ?></a>
    </div>
  </div>    
<?php else : ?>
  <div>
      <?php echo $option; ?>
  </div>
<?php endif; ?>
    <div class="result" data-option="<?php echo $option; ?>">
      <span data-value="accept"><?php echo join( "", $result[ "accept" ] ); ?></span>
      &nbsp<span data-value="tentative"><?php echo join( "", $result[ "tentative" ] ); ?></span>
      &nbsp<span data-value="reject"><?php echo join( "", $result[ "reject" ] ); ?></span>
    </div>

  </div>
  <?php endforeach; ?>
  
</div>

<?php if (VATROC::debug_section()): ?>
    <iframe src="https://calendar.google.com/calendar/embed?src=aaa3air%40gmail.com" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>
<?php endif; ?>
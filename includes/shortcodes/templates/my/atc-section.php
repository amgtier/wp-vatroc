<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
?>
<h1>ATC Section 123</h1>
<?php foreach( VATROC_Event::get_next_events() as $option => $result): ?>

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
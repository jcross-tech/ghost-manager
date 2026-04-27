<?php
/**
 * Discord floating button + popup (from Downloads snippet; URLs/strings from settings).
 *
 * @package Ghost_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output widget markup in footer.
 */
function ghost_manager_render_discord_widget() {
	if ( ! ghost_manager_is_feature_enabled( 'discord_widget' ) ) {
		return;
	}

	$invite = ghost_manager_get( 'urls.discord' );
	if ( ! $invite ) {
		return;
	}

	$invite   = esc_url( $invite );
	$tooltip  = esc_html( ghost_manager_get( 'strings.discord_tooltip' ) );
	$title    = esc_html( ghost_manager_get( 'strings.discord_popup_title' ) );
	$body     = esc_html( ghost_manager_get( 'strings.discord_popup_body' ) );
	$cta      = esc_html( ghost_manager_get( 'strings.discord_cta_label' ) );
	?>
<!-- Discord Floating Button -->
<div id="discord-button">
  <svg width="26" height="26" viewBox="0 0 245 240" fill="white" aria-hidden="true">
    <path d="M104.4 104.7c-5.7 0-10.2 5-10.2 11.1 0 6.1 4.6 11.1 10.2 11.1 5.7 0 10.3-5 10.2-11.1 0-6.1-4.6-11.1-10.2-11.1zm36.2 0c-5.7 0-10.2 5-10.2 11.1 0 6.1 4.6 11.1 10.2 11.1 5.7 0 10.3-5 10.2-11.1 0-6.1-4.5-11.1-10.2-11.1z"/>
    <path d="M189.5 20h-134C24.9 20 0 44.9 0 75.5v89C0 195.1 24.9 220 55.5 220h113.9l-5.3-18.5 12.8 11.9 12.1 11.2L216 240V75.5C216 44.9 191.1 20 160.5 20zm-39.3 135s-3.7-4.4-6.8-8.3c13.5-3.8 18.6-12.2 18.6-12.2-4.2 2.8-8.2 4.7-11.8 6-5.1 2.1-10 3.5-14.8 4.3-9.8 1.8-18.8 1.3-26.6-.1-5.8-1.1-10.8-2.6-15-4.3-2.4-.9-5-2.1-7.6-3.6-.3-.2-.7-.4-1-.6-.2-.1-.3-.2-.4-.3-1.8-1-2.8-1.7-2.8-1.7s5 8.2 18.2 12.1c-3.1 3.9-6.9 8.6-6.9 8.6-22.8-.7-31.5-15.6-31.5-15.6 0-33 14.7-59.8 14.7-59.8 14.7-11 28.7-10.7 28.7-10.7l1 1.2c-18.4 5.3-26.9 13.4-26.9 13.4s2.3-1.3 6.2-3.2c11.3-5 20.3-6.3 24-6.6.6-.1 1.1-.2 1.7-.2 6.1-.8 13-.9 20.2-.1 9.5 1.1 19.6 3.9 29.9 9.6 0 0-8.1-7.7-25.6-13l1.4-1.6s14-.3 28.7 10.7c0 0 14.7 26.8 14.7 59.8 0 0-8.8 14.9-31.7 15.6z"/>
  </svg>
</div>

<div id="discord-tooltip"><?php echo $tooltip; ?></div>

<div id="discord-popup">
  <div id="discord-header">
    <span><?php echo $title; ?></span>
    <button type="button" id="discord-close" aria-label="Close chat">×</button>
  </div>

  <div id="discord-body">
    <p><?php echo $body; ?></p>

    <a href="<?php echo $invite; ?>" target="_blank" rel="noopener noreferrer" id="discord-cta">
      <?php echo $cta; ?>
    </a>
  </div>
</div>

<style>
#discord-header button {
  background: rgba(255,255,255,0.15);
  border: none;
  color: white;
  font-size: 18px;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.1s ease;
}
#discord-header button:hover {
  background: rgba(255,255,255,0.25);
  transform: scale(1.05);
}
#discord-header button:focus {
  outline: none;
  box-shadow: none;
}
#discord-button {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #5865F2;
  width: 58px;
  height: 58px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 99999;
  animation: ghost-dc-float 3s ease-in-out infinite, ghost-dc-pulse 2.5s infinite;
}
@keyframes ghost-dc-float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-6px); }
  100% { transform: translateY(0px); }
}
@keyframes ghost-dc-pulse {
  0% { box-shadow: 0 0 0 0 rgba(88,101,242, 0.5); }
  70% { box-shadow: 0 0 0 12px rgba(88,101,242, 0); }
  100% { box-shadow: 0 0 0 0 rgba(88,101,242, 0); }
}
#discord-tooltip {
  position: fixed;
  bottom: 95px;
  right: 20px;
  background: white;
  color: #111;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 500;
  z-index: 99999;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  max-width: 260px;
  line-height: 1.4;
  animation: ghost-dc-fadeSlideIn 0.4s ease;
}
@keyframes ghost-dc-fadeSlideIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
#discord-popup {
  position: fixed;
  bottom: 95px;
  right: 20px;
  width: 320px;
  background: #2b2d31;
  border-radius: 12px;
  display: none;
  flex-direction: column;
  overflow: hidden;
  z-index: 99999;
}
#discord-popup.active {
  display: flex;
}
#discord-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #5865F2;
  color: white;
  padding: 12px;
  font-size: 14px;
}
#discord-body {
  padding: 16px;
  text-align: center;
}
#discord-body p {
  font-size: 14px;
  color: #ccc;
  margin-bottom: 15px;
}
#discord-cta {
  display: block;
  background: #5865F2;
  color: white;
  text-decoration: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: 600;
  transition: 0.2s ease;
}
#discord-cta:hover {
  background: #4752c4;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const btn = document.getElementById("discord-button");
  const popup = document.getElementById("discord-popup");
  const close = document.getElementById("discord-close");
  const tooltip = document.getElementById("discord-tooltip");
  if (!btn || !popup) return;

  btn.onclick = () => {
    popup.classList.toggle("active");
    try { localStorage.setItem("discord_closed", "true"); } catch (e) {}
  };

  if (close) {
    close.onclick = () => {
      popup.classList.remove("active");
      try { localStorage.setItem("discord_closed", "true"); } catch (e) {}
    };
  }

  setTimeout(() => {
    if (tooltip) tooltip.style.display = "none";
  }, 10000);

  if (!localStorage.getItem("discord_closed")) {
    setTimeout(() => {
      popup.classList.add("active");
      setTimeout(() => {
        popup.classList.remove("active");
      }, 10000);
    }, 30000);
  }
});
</script>
	<?php
}
add_action( 'wp_footer', 'ghost_manager_render_discord_widget' );

/**
 * NS Hamburger Menu JavaScript
 * Accessible hamburger menu with keyboard navigation and focus management
 * @version 0.10.0
 */
document.addEventListener('DOMContentLoaded', function(){
  const focusablesSelector = 'a, button, input, textarea, select, details,[tabindex]:not([tabindex="-1"])';

  const setupOne = (btn) => {
    const overlayId = btn.getAttribute('aria-controls');
    if (!overlayId) return;
    const overlay = document.getElementById(overlayId);
    if (!overlay) return;

    // Find the wrapper element with data-open-shape attribute
    const wrapper = btn.closest('[data-open-shape]');

    const hueDefault = (typeof NS_HMB !== 'undefined' ? !!NS_HMB.hueAnimDefault : true);
    if (!hueDefault) overlay.classList.add('ns-hue-off');

    let lastFocused = null;

    const getTransitionMs = () => {
      const dur = getComputedStyle(overlay).transitionDuration; // "0.6s" or "200ms"
      if (!dur) return 600;
      return dur.includes('ms') ? parseFloat(dur) : parseFloat(dur) * 1000;
    };

    const open = () => {
      lastFocused = document.activeElement;
      
      // 1. First show the overlay
      overlay.removeAttribute('hidden');
      
      // 2. Force a reflow to ensure the overlay is rendered
      void overlay.offsetWidth;
      
      // 3. Then add the open class to trigger animation
      if (wrapper) {
        wrapper.classList.add('ns-open');
      } else {
        // Fallback: add to body for backward compatibility
        document.body.classList.add('ns-open');
      }
      
      document.body.classList.add('ns-no-scroll');
      btn.setAttribute('aria-expanded','true');
      btn.setAttribute('aria-label', (typeof NS_HMB !== 'undefined' && NS_HMB.i18n) ? NS_HMB.i18n.closeMenu : 'Close menu');
      
      const first = overlay.querySelector(focusablesSelector);
      if (first) setTimeout(()=>first.focus(), 50);
    };

    const close = () => {
      // 1. Remove open class to start closing animation
      if (wrapper) {
        wrapper.classList.remove('ns-open');
      } else {
        // Fallback: remove from body for backward compatibility
        document.body.classList.remove('ns-open');
      }
      
      btn.setAttribute('aria-expanded','false');
      btn.setAttribute('aria-label', (typeof NS_HMB !== 'undefined' && NS_HMB.i18n) ? NS_HMB.i18n.openMenu : 'Open menu');
      
      // 2. Wait for transition to complete, then hide overlay and remove scroll lock
      const wait = getTransitionMs() + 50;
      setTimeout(() => {
        overlay.setAttribute('hidden','');
        document.body.classList.remove('ns-no-scroll');
      }, wait);
      
      if (lastFocused && typeof lastFocused.focus === 'function') {
        setTimeout(()=>lastFocused.focus(), wait + 10);
      } else {
        setTimeout(()=>btn.focus(), wait + 10);
      }
    };

    // トグル
    btn.addEventListener('click', (e)=>{
      e.preventDefault();
      const isOpen = !overlay.hasAttribute('hidden');
      isOpen ? close() : open();
    });

    // ESCで閉じる & フォーカストラップ
    document.addEventListener('keydown', (e)=>{
      const isOpen = wrapper ? wrapper.classList.contains('ns-open') : document.body.classList.contains('ns-open');
      if(!isOpen) return;

      if(e.key === 'Escape') {
        e.preventDefault();
        close();
        return;
      }
      if(e.key === 'Tab') {
        const nodes = overlay.querySelectorAll(focusablesSelector);
        if(!nodes.length) return;
        const first = nodes[0], last = nodes[nodes.length-1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });

    // 背景クリックで閉じる（ナビ以外）
    overlay.addEventListener('click', (e)=>{
      const nav = e.target.closest('.ns-overlay__nav');
      if(!nav) close();
    });

    // リンク押下で閉じる
    overlay.addEventListener('click', (e)=>{
      const a = e.target.closest('a');
      if(a) setTimeout(close, 50);
    });
  };

  document.querySelectorAll('.ns-hb').forEach(setupOne);
});

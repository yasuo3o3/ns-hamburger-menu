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
    if (!wrapper) return;

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
      wrapper.classList.add('ns-open');
      document.body.classList.add('ns-no-scroll');
      btn.setAttribute('aria-expanded','true');
      btn.setAttribute('aria-label', (typeof NS_HMB !== 'undefined' && NS_HMB.i18n) ? NS_HMB.i18n.closeMenu : 'Close menu');
      overlay.removeAttribute('hidden');
      const first = overlay.querySelector(focusablesSelector);
      if (first) setTimeout(()=>first.focus(), 50);
    };

    const close = () => {
      wrapper.classList.remove('ns-open');
      document.body.classList.remove('ns-no-scroll');
      btn.setAttribute('aria-expanded','false');
      btn.setAttribute('aria-label', (typeof NS_HMB !== 'undefined' && NS_HMB.i18n) ? NS_HMB.i18n.openMenu : 'Open menu');
      const wait = getTransitionMs() + 50;
      setTimeout(()=>overlay.setAttribute('hidden',''), wait);
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
      if(!wrapper.classList.contains('ns-open')) return;

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

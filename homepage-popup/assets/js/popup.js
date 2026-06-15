/**
 * HomePage Pop Up — Frontend Popup JavaScript
 * Author: Sajid Khan
 * Version: 1.0.0
 *
 * Vanilla JS — no dependencies required.
 */

/* global hppConfig */
(function () {
  'use strict';

  /** Merge supplied config with safe defaults. */
  var cfg = Object.assign(
    {
      delay:       1,
      onceSession: true,
      storageKey:  'hpp_shown',
    },
    typeof hppConfig !== 'undefined' ? hppConfig : {}
  );

  /** Convert delay to milliseconds (supplied in seconds). */
  var delayMs = (parseFloat(cfg.delay) || 0) * 1000;

  /**
   * Return true if the popup should be suppressed due to
   * the "show once per session" setting.
   */
  function wasAlreadyShown() {
    if (!cfg.onceSession) return false;
    try {
      return !!sessionStorage.getItem(cfg.storageKey);
    } catch (e) {
      // sessionStorage unavailable (private mode quirks).
      return false;
    }
  }

  /**
   * Mark the popup as shown for the current session.
   */
  function markAsShown() {
    if (!cfg.onceSession) return;
    try {
      sessionStorage.setItem(cfg.storageKey, '1');
    } catch (e) {
      // Silently fail.
    }
  }

  /**
   * Open the popup by toggling the visibility class.
   *
   * @param {HTMLElement} overlay
   */
  function openPopup(overlay) {
    overlay.classList.add('hpp-overlay--visible');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden'; // prevent background scroll
    markAsShown();

    // Move focus to the close button for keyboard/screen reader accessibility.
    var closeBtn = overlay.querySelector('#hpp-close-btn');
    if (closeBtn) {
      closeBtn.focus();
    }
  }

  /**
   * Close the popup.
   *
   * @param {HTMLElement} overlay
   */
  function closePopup(overlay) {
    overlay.classList.remove('hpp-overlay--visible');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  /**
   * Trap keyboard focus inside the popup while it is open.
   *
   * @param {KeyboardEvent} e
   * @param {HTMLElement}   popup
   * @param {HTMLElement}   overlay
   */
  function handleKeydown(e, popup, overlay) {
    // Escape closes the popup.
    if (e.key === 'Escape' || e.key === 'Esc') {
      closePopup(overlay);
      return;
    }

    // Tab focus trapping.
    if (e.key !== 'Tab') return;

    var focusable = popup.querySelectorAll(
      'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );

    if (!focusable.length) return;

    var first = focusable[0];
    var last  = focusable[focusable.length - 1];

    if (e.shiftKey) {
      if (document.activeElement === first) {
        e.preventDefault();
        last.focus();
      }
    } else {
      if (document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  }

  /**
   * Main initialiser — called once the DOM is ready.
   */
  function init() {
    var overlay = document.getElementById('hpp-overlay');
    var popup   = document.getElementById('hpp-popup');
    var closeBtn = document.getElementById('hpp-close-btn');

    if (!overlay || !popup) return; // Popup HTML wasn't rendered.

    // Set initial ARIA state.
    overlay.setAttribute('aria-hidden', 'true');

    // If already shown this session, bail.
    if (wasAlreadyShown()) return;

    // Schedule popup opening after the configured delay.
    setTimeout(function () {
      openPopup(overlay);
    }, delayMs);

    // ── Event Listeners ────────────────────────────────────────────────

    // Close button.
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        closePopup(overlay);
      });
    }

    // Clicking the dark overlay outside the popup card.
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) {
        closePopup(overlay);
      }
    });

    // Keyboard: Escape + focus trap.
    document.addEventListener('keydown', function (e) {
      if (overlay.classList.contains('hpp-overlay--visible')) {
        handleKeydown(e, popup, overlay);
      }
    });
  }

  // Run after the DOM is fully parsed.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

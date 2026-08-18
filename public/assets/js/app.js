// After submit, browser Back can restore a cached editable form (bfcache).
// Force a full reload so the server can redirect to the dashboard.
window.addEventListener('pageshow', function (event) {
  var form = document.querySelector('form.application-step-form, form[data-prevent-bfcache="1"]');
  var path = window.location.pathname || '';
  var isAppForm = !!form || /\/applicant\/application\/step\//.test(path);
  if (!isAppForm) return;

  if (event.persisted) {
    window.location.reload();
    return;
  }
  try {
    var nav = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
    if (nav && nav.type === 'back_forward') {
      window.location.reload();
    }
  } catch (e) {}
});

document.addEventListener('DOMContentLoaded', function () {
  // Block double-submit on application forms; disable controls after first submit
  function clearStaleDateRequired(form) {
    if (!form) return;
    form.querySelectorAll('.flatpickr-input').forEach(function (inp) {
      inp.required = false;
      inp.removeAttribute('required');
      if (typeof syncFlatpickrRequired === 'function') {
        syncFlatpickrRequired(inp, inp._flatpickr);
        return;
      }
      if (inp._flatpickr && inp._flatpickr.altInput) {
        var filled = String(inp.value || inp._flatpickr.altInput.value || '').trim();
        inp._flatpickr.altInput.setCustomValidity(inp._flatpickr.altInput.required && !filled ? 'Please select a date.' : '');
      }
    });
  }

  document.querySelectorAll('form.application-step-form').forEach(function (form) {
    form.addEventListener('click', function (e) {
      var btn = e.target && e.target.closest ? e.target.closest('button[type="submit"], input[type="submit"]') : null;
      if (!btn) return;
      clearStaleDateRequired(form);
    }, true);
    form.addEventListener('submit', function () {
      clearStaleDateRequired(form);
      window.setTimeout(function () {
        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (btn) {
          btn.disabled = true;
        });
      }, 0);
    });
  });

  // Declaration checkboxes must be confirmed on this visit — never restore checked from saved/autofill state
  document.querySelectorAll('input[type="checkbox"][data-reset-on-load="1"]').forEach(function (box) {
    box.checked = false;
    box.removeAttribute('checked');
  });

  // Landline: digits and telephone punctuation only (strip letters as they are typed)
  document.querySelectorAll('input[data-landline="1"], input[name="phone_landline"]').forEach(function (el) {
    function stripLetters() {
      var next = (el.value || '').replace(/[A-Za-z]/g, '');
      if (next !== el.value) {
        el.value = next;
      }
    }
    el.addEventListener('input', stripLetters);
    el.addEventListener('paste', function () {
      window.setTimeout(stripLetters, 0);
    });
    stripLetters();
  });

  // ---------- GIGW accessibility controls ----------
  var root = document.documentElement;
  var body = document.body;
  var FONT_KEY = 'ssa_font_scale';
  var CONTRAST_KEY = 'ssa_contrast';
  var minScale = 90;
  var maxScale = 140;
  var step = 10;

  function applyFontScale(scale) {
    scale = Math.max(minScale, Math.min(maxScale, scale));
    root.style.fontSize = scale + '%';
    try {
      localStorage.setItem(FONT_KEY, String(scale));
    } catch (e) {}
  }

  function applyContrast(on) {
    body.classList.toggle('contrast-high', !!on);
    var btn = document.getElementById('contrastToggle');
    if (btn) {
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    }
    try {
      localStorage.setItem(CONTRAST_KEY, on ? '1' : '0');
    } catch (e) {}
  }

  var savedScale = 100;
  var savedContrast = false;
  try {
    savedScale = parseInt(localStorage.getItem(FONT_KEY) || '100', 10) || 100;
    savedContrast = localStorage.getItem(CONTRAST_KEY) === '1';
  } catch (e) {}
  applyFontScale(savedScale);
  applyContrast(savedContrast);

  var fontDec = document.getElementById('fontDec');
  var fontInc = document.getElementById('fontInc');
  var fontReset = document.getElementById('fontReset');
  var contrastToggle = document.getElementById('contrastToggle');

  if (fontDec) {
    fontDec.addEventListener('click', function () {
      var cur = parseInt(localStorage.getItem(FONT_KEY) || '100', 10) || 100;
      applyFontScale(cur - step);
    });
  }
  if (fontInc) {
    fontInc.addEventListener('click', function () {
      var cur = parseInt(localStorage.getItem(FONT_KEY) || '100', 10) || 100;
      applyFontScale(cur + step);
    });
  }
  if (fontReset) {
    fontReset.addEventListener('click', function () {
      applyFontScale(100);
    });
  }
  if (contrastToggle) {
    contrastToggle.addEventListener('click', function () {
      applyContrast(!body.classList.contains('contrast-high'));
    });
  }

  // Close mobile nav after link click
  var navCollapse = document.getElementById('navMain');
  if (navCollapse && window.bootstrap) {
    navCollapse.querySelectorAll('a.nav-link, a.btn-nav-cta').forEach(function (link) {
      link.addEventListener('click', function () {
        var instance = bootstrap.Collapse.getInstance(navCollapse);
        if (instance && window.getComputedStyle(navCollapse).display !== 'none') {
          var toggler = document.querySelector('.navbar-mhc .navbar-toggler');
          if (toggler && window.getComputedStyle(toggler).display !== 'none') {
            instance.hide();
          }
        }
      });
    });
  }

  // Scroll active step into view on mobile
  var activeStep = document.querySelector('.stepper .step.active');
  if (activeStep && activeStep.scrollIntoView) {
    try {
      activeStep.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
    } catch (e) {
      activeStep.scrollIntoView(false);
    }
  }

  function getRowContainer(target) {
    if (!target) return null;
    // Tables must append rows into tbody, not the table element
    if (target.tagName === 'TABLE') {
      return target.tBodies[0] || target;
    }
    return target;
  }

  function isTemplateRow(row) {
    if (!row) return true;
    if (row.hasAttribute('data-row-template')) return true;
    if (row.getAttribute('aria-hidden') === 'true' && row.hasAttribute('hidden')) return true;
    return false;
  }

  function isRowHidden(row) {
    if (!row) return true;
    if (isTemplateRow(row)) return true;
    if (row.hasAttribute('hidden')) return true;
    if (row.classList.contains('d-none')) return true;
    try {
      if (window.getComputedStyle(row).display === 'none') return true;
    } catch (e) {}
    return false;
  }

  function getVisibleRows(container) {
    // Only real editable rows — never the hidden clone template
    var rows = container.querySelectorAll('.dynamic-row');
    var out = [];
    for (var i = 0; i < rows.length; i++) {
      if (!isRowHidden(rows[i])) {
        out.push(rows[i]);
      }
    }
    return out;
  }

  function clearFields(root) {
    root.querySelectorAll('input, select, textarea').forEach(function (el) {
      // Skip Flatpickr alt display fields (cleared via instance on original)
      if (el.classList.contains('flatpickr-alt-input')) return;
      // Always re-enable after cloning a disabled template
      el.disabled = false;
      el.removeAttribute('disabled');
      el.readOnly = false;
      el.removeAttribute('readonly');
      if (el._flatpickr) {
        el._flatpickr.clear();
        return;
      }
      if (el.type === 'checkbox' || el.type === 'radio') {
        el.checked = false;
      } else if (el.tagName === 'SELECT') {
        el.selectedIndex = 0;
      } else {
        el.value = '';
      }
    });
    // Reset Others free-text groups after clear (re-bind + hide "other" text)
    root.querySelectorAll('[data-others-group]').forEach(function (g) {
      delete g.dataset.othersBound;
      var otherField = g.querySelector('[data-others-field]');
      if (otherField) {
        otherField.setAttribute('hidden', 'hidden');
        otherField.querySelectorAll('input, textarea').forEach(function (inp) {
          inp.disabled = true;
          inp.value = '';
        });
      }
    });
    if (typeof window.ssaSyncPracticeCourtNames === 'function') {
      window.ssaSyncPracticeCourtNames(root);
    }
  }

  function renumberEntryLabels(container) {
    var visible = getVisibleRows(container);
    visible.forEach(function (row, idx) {
      var label = row.querySelector('.entry-card-label');
      if (!label) return;
      var text = (label.textContent || '').replace(/\s*#\d+\s*$/, '').trim();
      if (!text) text = 'Entry';
      label.textContent = text + ' #' + (idx + 1);
    });
  }

  // L-1 / L-2: Madras High Court copies into Court Name; other levels stay empty for typing
  function syncJudgmentCourtName(select) {
    if (!select) return;
    var row = select.closest('.dynamic-row, .entry-card, tr') || select.parentElement;
    if (!row) return;
    var nameInput = row.querySelector('[data-judgment-court-name]');
    if (!nameInput) return;

    var isMadras = select.value === 'madras_hc';
    if (isMadras) {
      var opt = select.options[select.selectedIndex];
      nameInput.value = opt ? String(opt.text || '').trim() : 'Madras High Court';
      nameInput.readOnly = true;
      nameInput.setAttribute('readonly', 'readonly');
      nameInput.setAttribute('tabindex', '-1');
      nameInput.classList.add('bg-light');
      nameInput.setAttribute('title', 'Copied from Court: Madras High Court');
      nameInput.removeAttribute('placeholder');
    } else {
      if (nameInput.readOnly || nameInput.getAttribute('data-auto-court') === '1') {
        nameInput.value = '';
      }
      nameInput.readOnly = false;
      nameInput.removeAttribute('readonly');
      nameInput.removeAttribute('tabindex');
      nameInput.classList.remove('bg-light');
      nameInput.setAttribute('title', '');
      nameInput.setAttribute('placeholder', 'Enter court name');
    }
    nameInput.setAttribute('data-auto-court', isMadras ? '1' : '0');
  }

  function syncJudgmentCourtNames(root) {
    root = root || document;
    root.querySelectorAll('[data-judgment-court-level]').forEach(function (select) {
      syncJudgmentCourtName(select);
    });
  }

  document.addEventListener('change', function (e) {
    var el = e.target;
    if (!el || !el.getAttribute || !el.hasAttribute('data-judgment-court-level')) return;
    syncJudgmentCourtName(el);
  });

  window.ssaSyncJudgmentCourtNames = syncJudgmentCourtNames;
  syncJudgmentCourtNames(document);

  function syncPracticeCourtName(select) {
    if (!select) return;
    var row = select.closest('.dynamic-row');
    if (!row) return;
    var wrap = row.querySelector('[data-practice-court-name]');
    var input = wrap ? wrap.querySelector('input[name="court_name[]"]') : null;
    var show = select.value === 'hc_district_trial';
    if (wrap) {
      if (show) {
        wrap.removeAttribute('hidden');
      } else {
        wrap.setAttribute('hidden', 'hidden');
      }
    }
    if (input && !row.hasAttribute('data-row-template')) {
      input.required = show && !select.disabled;
    }
  }

  function syncPracticeCourtNames(root) {
    root = root || document;
    root.querySelectorAll('[data-practice-court-type]').forEach(function (select) {
      syncPracticeCourtName(select);
    });
  }

  document.addEventListener('change', function (e) {
    var el = e.target;
    if (!el || !el.hasAttribute || !el.hasAttribute('data-practice-court-type')) return;
    syncPracticeCourtName(el);
  });

  window.ssaSyncPracticeCourtNames = syncPracticeCourtNames;
  syncPracticeCourtNames(document);

  // Add dynamic rows
  document.querySelectorAll('[data-add-row]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = document.querySelector(btn.getAttribute('data-add-row'));
      if (!target) return;
      var container = getRowContainer(target);
      var template =
        target.querySelector('[data-row-template]') || container.querySelector('[data-row-template]');
      if (!template) return;

      var clone = template.cloneNode(true);
      clone.removeAttribute('data-row-template');
      clone.removeAttribute('hidden');
      clone.removeAttribute('aria-hidden');
      clone.classList.remove('d-none');
      clearFields(clone);
      // Allow Others toggles to re-bind on the new row
      clone.querySelectorAll('[data-others-group]').forEach(function (g) {
        delete g.dataset.othersBound;
      });
      // Fresh date inputs on clone must re-init Flatpickr
      clone.querySelectorAll('[data-fp-ready]').forEach(function (inp) {
        inp.removeAttribute('data-fp-ready');
        if (inp.classList.contains('flatpickr-input')) {
          inp.classList.remove('flatpickr-input');
        }
        // Restore native types if clone still has text after previous enhancement
        if (inp.getAttribute('name') && !inp.getAttribute('type')) {
          inp.setAttribute('type', 'date');
        }
      });
      container.appendChild(clone);
      renumberEntryLabels(container);
      if (typeof window.ssaInitFlatpickr === 'function') {
        window.ssaInitFlatpickr(clone);
      }
      if (typeof window.ssaSyncJudgmentCourtNames === 'function') {
        window.ssaSyncJudgmentCourtNames(clone);
      }
      if (typeof window.ssaSyncPracticeCourtNames === 'function') {
        window.ssaSyncPracticeCourtNames(clone);
      }
    });
  });

  // Remove dynamic rows — keep exactly ONE visible data row (template stays hidden)
  document.querySelectorAll('[data-rows]').forEach(function (host) {
    host.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('[data-remove-row]');
      if (!removeBtn) return;
      e.preventDefault();

      var container = getRowContainer(host);
      var row = removeBtn.closest('tr.dynamic-row, .dynamic-row');
      // Never act on the hidden clone template
      if (!row || isTemplateRow(row) || row.hasAttribute('data-row-template')) return;

      var visible = getVisibleRows(container);
      // Keep exactly one visible data row: clear fields instead of removing
      if (visible.length <= 1) {
        clearFields(row);
        if (typeof window.ssaSyncJudgmentCourtNames === 'function') {
          window.ssaSyncJudgmentCourtNames(row);
        }
        renumberEntryLabels(container);
        return;
      }
      row.remove();
      renumberEntryLabels(container);
    });
  });

  // Age / practice duration (years + months + days) as on a reference date
  function normalizeDateStr(value) {
    if (!value) return '';
    // Accept YYYY-MM-DD or YYYY-MM-DD HH:MM:SS / ISO
    var m = String(value).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!m) return '';
    return m[1] + '-' + m[2] + '-' + m[3];
  }

  function defaultAgeAsOnDate() {
    // Fallback when data-age-as-on is missing (should normally come from notification date)
    return new Date().getFullYear() + '-01-01';
  }

  function daysInMonth(year, month) {
    // month is 1–12; Date day 0 of next month = last day of this month
    return new Date(year, month, 0).getDate();
  }

  function calcAgeParts(dobStr, asOnStr) {
    dobStr = normalizeDateStr(dobStr);
    asOnStr = normalizeDateStr(asOnStr) || defaultAgeAsOnDate();
    if (!dobStr) return null;

    var dobParts = dobStr.split('-').map(function (p) {
      return parseInt(p, 10);
    });
    var asParts = asOnStr.split('-').map(function (p) {
      return parseInt(p, 10);
    });
    if (dobParts.length !== 3 || asParts.length !== 3) return null;

    var y1 = dobParts[0],
      m1 = dobParts[1],
      d1 = dobParts[2];
    var y2 = asParts[0],
      m2 = asParts[1],
      d2 = asParts[2];

    // Use range checks (not truthiness) so month/day = 1 is valid
    if (
      !isFinite(y1) ||
      !isFinite(m1) ||
      !isFinite(d1) ||
      !isFinite(y2) ||
      !isFinite(m2) ||
      !isFinite(d2) ||
      m1 < 1 ||
      m1 > 12 ||
      d1 < 1 ||
      d1 > 31 ||
      m2 < 1 ||
      m2 > 12 ||
      d2 < 1 ||
      d2 > 31
    ) {
      return null;
    }

    // Reject future DOB relative to as-on date
    if (y1 > y2 || (y1 === y2 && m1 > m2) || (y1 === y2 && m1 === m2 && d1 > d2)) {
      return null;
    }

    var years = y2 - y1;
    var months = m2 - m1;
    var days = d2 - d1;
    if (days < 0) {
      months -= 1;
      // Borrow days from the previous calendar month of the as-on date
      var prevMonth = m2 - 1;
      var prevYear = y2;
      if (prevMonth < 1) {
        prevMonth = 12;
        prevYear -= 1;
      }
      days += daysInMonth(prevYear, prevMonth);
    }
    if (months < 0) {
      years -= 1;
      months += 12;
    }
    if (years < 0) return null;

    // months is always 0–11 after borrow; keep integer
    return { years: years, months: months, days: days };
  }

  /**
   * Force a visible update on readonly display fields.
   * Some browsers (esp. readonly + type=number, or first write after empty)
   * paint years but leave months stale until a later event.
   */
  function setReadonlyDisplayValue(el, value) {
    if (!el) return;
    var next = value === null || value === undefined ? '' : String(value);
    var wasReadonly = el.readOnly;
    var wasDisabled = el.disabled;

    el.readOnly = false;
    el.disabled = false;

    // Clear-then-set forces a repaint when previous value was empty or identical
    if (el.type === 'number') {
      el.value = '';
    }
    if (el.value !== next) {
      el.value = next;
    } else {
      // Same string still needs a kick on some engines after partial date picks
      el.value = '';
      el.value = next;
    }
    el.defaultValue = next;
    el.setAttribute('value', next);

    el.readOnly = wasReadonly || true;
    el.disabled = wasDisabled;
  }

  function resolveAgeTarget(input, attrName) {
    var id = input.getAttribute(attrName);
    if (!id) return null;
    var el = document.getElementById(id);
    if (el) return el;
    var form = input.form || (input.closest && input.closest('form'));
    if (form) {
      try {
        return form.querySelector('#' + CSS.escape(id));
      } catch (e) {
        return form.querySelector('[id="' + id.replace(/"/g, '') + '"]');
      }
    }
    return null;
  }

  function updateAgeDisplays(input) {
    if (!input) return;
    var asOn = input.getAttribute('data-age-as-on') || defaultAgeAsOnDate();
    var yearsEl = resolveAgeTarget(input, 'data-age-years-target');
    var monthsEl = resolveAgeTarget(input, 'data-age-months-target');
    var daysEl = resolveAgeTarget(input, 'data-age-days-target');
    if (!yearsEl && !monthsEl && !daysEl) return;

    // Prefer live value; fall back to attribute if the picker has not committed yet
    var raw = input.value || input.getAttribute('value') || '';
    var parts = calcAgeParts(raw, asOn);

    // Always write all fields in one pass (months/days were lagging on first change)
    setReadonlyDisplayValue(yearsEl, parts ? parts.years : '');
    setReadonlyDisplayValue(monthsEl, parts ? parts.months : '');
    setReadonlyDisplayValue(daysEl, parts ? parts.days : '');
  }

  function scheduleAgeUpdate(input) {
    updateAgeDisplays(input);
    // Date pickers sometimes commit value after the first input/change event
    window.requestAnimationFrame(function () {
      updateAgeDisplays(input);
    });
    window.setTimeout(function () {
      updateAgeDisplays(input);
    }, 0);
  }

  function bindAgeAutoCalc(root) {
    root = root || document;
    root.querySelectorAll('input[data-age-as-on]').forEach(function (input) {
      if (input.dataset.ageBound === '1') return;
      input.dataset.ageBound = '1';

      ['change', 'input', 'blur', 'focusout'].forEach(function (evt) {
        input.addEventListener(evt, function () {
          scheduleAgeUpdate(input);
        });
      });

      // Initial fill when DOB is already present
      scheduleAgeUpdate(input);
    });
  }

  bindAgeAutoCalc();

  // ---------- Flatpickr: consistent date / datetime UI across browsers ----------
  function parseDateValue(value) {
    if (!value) return null;
    var s = String(value).trim();
    if (!s) return null;
    // YYYY-MM-DD or YYYY-MM-DDTHH:MM[:SS] or YYYY-MM-DD HH:MM[:SS]
    var m = s.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T\s](\d{2}):(\d{2})(?::(\d{2}))?)?/);
    if (!m) return null;
    var y = parseInt(m[1], 10);
    var mo = parseInt(m[2], 10) - 1;
    var d = parseInt(m[3], 10);
    var h = m[4] != null ? parseInt(m[4], 10) : 0;
    var mi = m[5] != null ? parseInt(m[5], 10) : 0;
    var se = m[6] != null ? parseInt(m[6], 10) : 0;
    var dt = new Date(y, mo, d, h, mi, se);
    return isNaN(dt.getTime()) ? null : dt;
  }

  function isoDateStr(value) {
    return normalizeDateStr(value);
  }

  function findPeriodPairTo(fromEl) {
    if (!fromEl) return null;
    var row = fromEl.closest('.dynamic-row, tr, .row') || document;
    var named = fromEl.getAttribute('name') || '';
    if (named.indexOf('_from') !== -1) {
      var toName = named.replace('_from', '_to');
      var byName = row.querySelector('input[name="' + toName.replace(/"/g, '') + '"]');
      if (byName) return byName;
    }
    return row.querySelector('input[data-period="to"]');
  }

  function isPeriodFromInput(el) {
    if (!el) return false;
    if (el.getAttribute('data-period') === 'from') return true;
    var name = el.getAttribute('name') || '';
    return name.indexOf('_from') !== -1 && name.indexOf('_from_') === -1;
  }

  function applyPeriodToBounds(fromEl) {
    var toEl = findPeriodPairTo(fromEl);
    if (!toEl) return;

    var rangeMin = isoDateStr(toEl.getAttribute('data-range-min') || fromEl.getAttribute('data-range-min') || '');
    var rangeMax = isoDateStr(toEl.getAttribute('data-range-max') || fromEl.getAttribute('data-range-max') || toEl.getAttribute('max') || '');
    var fromVal  = isoDateStr(fromEl.value);
    var newMin   = fromVal || rangeMin || '';

    if (newMin) {
      toEl.setAttribute('min', newMin);
    } else {
      toEl.removeAttribute('min');
    }
    if (rangeMax) {
      toEl.setAttribute('max', rangeMax);
    }

    var fp = toEl._flatpickr;
    if (fp) {
      fp.set('minDate', newMin || null);
      fp.set('maxDate', rangeMax || null);
      var current = isoDateStr(toEl.value);
      if (current && ((newMin && current < newMin) || (rangeMax && current > rangeMax))) {
        fp.clear();
      }
    } else {
      var currentNative = isoDateStr(toEl.value);
      if (currentNative && ((newMin && currentNative < newMin) || (rangeMax && currentNative > rangeMax))) {
        toEl.value = '';
      }
    }
  }

  function dateFieldHasValue(el, instance) {
    instance = instance || (el && el._flatpickr) || null;
    if (el && String(el.value || '').trim()) return true;
    if (instance && instance.altInput && String(instance.altInput.value || '').trim()) return true;
    if (instance && instance.selectedDates && instance.selectedDates.length) return true;
    return false;
  }

  function syncFlatpickrRequired(el, instance) {
    instance = instance || (el && el._flatpickr) || null;
    if (!el) return;
    // Original (often hidden) input must never block submit
    el.required = false;
    el.removeAttribute('required');
    if (!instance || !instance.altInput) return;
    var needs = !!instance.altInput.required;
    var filled = dateFieldHasValue(el, instance);
    instance.altInput.setCustomValidity(needs && !filled ? 'Please select a date.' : '');
  }

  function initFlatpickrIn(root) {
    if (typeof flatpickr !== 'function') return;
    root = root || document;

    root.querySelectorAll('input[type="date"]:not([data-fp-ready])').forEach(function (el) {
      if (el.disabled) return;
      el.setAttribute('data-fp-ready', '1');
      // Avoid dual native + Flatpickr pickers on mobile / Safari
      try {
        el.type = 'text';
      } catch (e) {}
      el.setAttribute('inputmode', 'numeric');
      el.setAttribute('autocomplete', 'off');
      el.setAttribute('placeholder', el.getAttribute('placeholder') || 'dd/mm/yyyy');

      var wasRequired = el.required;
      var opts = {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        allowInput: true,
        disableMobile: true,
        defaultDate: parseDateValue(el.value) || undefined,
        onChange: function (selectedDates, dateStr, instance) {
          scheduleAgeUpdate(el);
          syncFlatpickrRequired(el, instance);
          if (isPeriodFromInput(el)) {
            applyPeriodToBounds(el);
          }
        },
        onReady: function (selectedDates, dateStr, instance) {
          // Move HTML5 required onto the visible alt input
          if (instance.altInput) {
            instance.altInput.classList.add('form-control');
            if (el.classList.contains('form-control-sm')) {
              instance.altInput.classList.add('form-control-sm');
            }
            if (wasRequired) {
              instance.altInput.required = true;
            }
          }
          syncFlatpickrRequired(el, instance);
          scheduleAgeUpdate(el);
          if (isPeriodFromInput(el)) {
            applyPeriodToBounds(el);
          }
        },
      };
      if (el.getAttribute('min')) opts.minDate = el.getAttribute('min');
      if (el.getAttribute('max')) opts.maxDate = el.getAttribute('max');

      flatpickr(el, opts);
    });

    root.querySelectorAll('input[type="datetime-local"]:not([data-fp-ready])').forEach(function (el) {
      if (el.disabled) return;
      el.setAttribute('data-fp-ready', '1');
      try {
        el.type = 'text';
      } catch (e) {}
      el.setAttribute('inputmode', 'numeric');
      el.setAttribute('autocomplete', 'off');
      el.setAttribute('placeholder', el.getAttribute('placeholder') || 'dd/mm/yyyy hh:mm');

      // Normalise value for Flatpickr (datetime-local uses T separator)
      var raw = (el.value || '').replace('T', ' ');
      if (raw.length >= 19) {
        raw = raw.substring(0, 16);
      }
      if (raw && el.value !== raw) {
        el.value = raw;
      }

      var wasRequired = el.required;
      var opts = {
        enableTime: true,
        time_24hr: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd/m/Y H:i',
        allowInput: true,
        disableMobile: true,
        defaultDate: parseDateValue(el.value) || undefined,
        onChange: function (selectedDates, dateStr, instance) {
          if (instance.altInput && wasRequired) {
            instance.altInput.setCustomValidity(dateStr ? '' : 'Please select date and time.');
          }
        },
        onReady: function (selectedDates, dateStr, instance) {
          if (instance.altInput) {
            instance.altInput.classList.add('form-control');
            if (wasRequired) {
              el.required = false;
              instance.altInput.required = true;
              instance.altInput.setCustomValidity(dateStr || el.value ? '' : 'Please select date and time.');
            }
          }
        },
      };
      if (el.getAttribute('min')) opts.minDate = el.getAttribute('min');
      if (el.getAttribute('max')) opts.maxDate = el.getAttribute('max');

      flatpickr(el, opts);
    });

    root.querySelectorAll('input[type="time"]:not([data-fp-ready])').forEach(function (el) {
      if (el.disabled) return;
      el.setAttribute('data-fp-ready', '1');
      try {
        el.type = 'text';
      } catch (e) {}
      el.setAttribute('inputmode', 'numeric');
      el.setAttribute('autocomplete', 'off');
      el.setAttribute('placeholder', el.getAttribute('placeholder') || 'hh:mm');

      var wasRequired = el.required;
      flatpickr(el, {
        enableTime: true,
        noCalendar: true,
        time_24hr: true,
        dateFormat: 'H:i',
        altInput: true,
        altFormat: 'H:i',
        allowInput: true,
        disableMobile: true,
        defaultDate: el.value || undefined,
        onReady: function (selectedDates, dateStr, instance) {
          if (instance.altInput) {
            instance.altInput.classList.add('form-control');
            if (wasRequired) {
              el.required = false;
              instance.altInput.required = true;
            }
          }
        },
      });
    });
  }

  initFlatpickrIn(document);

  document.addEventListener('change', function (e) {
    var el = e.target;
    if (!el || el.nodeType !== 1) return;
    if (!isPeriodFromInput(el)) return;
    applyPeriodToBounds(el);
  });

  // Expose for dynamic row clones
  window.ssaInitFlatpickr = initFlatpickrIn;

  // CAPTCHA refresh (scope-aware: only refreshes the paired image/input)
  document.querySelectorAll('.captcha-refresh').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var sel = btn.getAttribute('data-captcha-img');
      var img = sel ? document.querySelector(sel) : null;
      if (!img) return;
      var base = (img.getAttribute('src') || '').split('?')[0];
      var scope = btn.getAttribute('data-captcha-scope')
        || img.getAttribute('data-captcha-scope')
        || 'default';
      var qs = 't=' + Date.now() + '&r=' + Math.random().toString(36).slice(2);
      if (scope && scope !== 'default') {
        qs += '&scope=' + encodeURIComponent(scope);
      }
      img.setAttribute('src', base + '?' + qs);

      var inputSel = btn.getAttribute('data-captcha-input');
      var input = inputSel
        ? document.querySelector(inputSel)
        : (img.closest('.captcha-field')
            ? img.closest('.captcha-field').querySelector('input.captcha-input, input[name="captcha"], input[name="lookup_captcha"]')
            : document.querySelector('input[name="captcha"]'));
      if (input) {
        input.value = '';
        input.focus();
      }
    });
  });

  // Photo / signature file preview (upload step)
  document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
      var previewSel = input.getAttribute('data-preview');
      var placeholderSel = input.getAttribute('data-placeholder');
      var metaSel = input.getAttribute('data-meta');
      var img = previewSel ? document.querySelector(previewSel) : null;
      var placeholder = placeholderSel ? document.querySelector(placeholderSel) : null;
      var meta = metaSel ? document.querySelector(metaSel) : null;
      var file = input.files && input.files[0] ? input.files[0] : null;

      if (!file) {
        if (img && !img.getAttribute('data-saved-src')) {
          img.src = '';
          img.classList.add('d-none');
          if (placeholder) placeholder.classList.remove('d-none');
        }
        if (meta) meta.innerHTML = '';
        return;
      }

      var minKb = parseInt(input.getAttribute('data-min-kb') || '0', 10);
      var maxKb = parseInt(input.getAttribute('data-max-kb') || '0', 10);
      var sizeKb = file.size / 1024;
      var typeOk = /^image\/jpeg$/i.test(file.type) || /\.jpe?g$/i.test(file.name);
      var sizeOk = (!minKb || sizeKb >= minKb) && (!maxKb || sizeKb <= maxKb);

      if (meta) {
        var msgs = [];
        msgs.push('<strong>' + file.name + '</strong> · ' + sizeKb.toFixed(1) + ' KB');
        if (!typeOk) {
          msgs.push('<span class="text-danger">JPG/JPEG only</span>');
        } else if (!sizeOk) {
          msgs.push('<span class="text-danger">Size must be ' + minKb + '–' + maxKb + ' KB</span>');
        } else {
          msgs.push('<span class="text-success">Ready to upload</span>');
        }
        meta.innerHTML = msgs.join(' · ');
      }

      if (!typeOk || !img) {
        return;
      }

      // Keep original saved URL so clearing input can restore if needed
      if (img.src && !img.getAttribute('data-saved-src') && img.src.indexOf('blob:') !== 0) {
        img.setAttribute('data-saved-src', img.src);
      }

      var reader = new FileReader();
      reader.onload = function (e) {
        img.src = e.target.result;
        img.classList.remove('d-none');
        if (placeholder) placeholder.classList.add('d-none');
        var frame = img.closest('.upload-preview-frame');
        if (frame) frame.classList.add('has-preview');
      };
      reader.readAsDataURL(file);
    });
  });

  // Toggle detail fields for Yes/No
  document.querySelectorAll('[data-toggle-detail]').forEach(function (el) {
    var targetSel = el.getAttribute('data-toggle-detail');
    var target = document.querySelector(targetSel);
    if (!target) return;
    function clearToggleDetailFields(root) {
      root.querySelectorAll('input, textarea').forEach(function (inp) {
        if (inp.classList.contains('flatpickr-alt-input')) return;
        if (inp._flatpickr) {
          inp._flatpickr.clear();
          return;
        }
        if (inp.type === 'checkbox' || inp.type === 'radio') {
          inp.checked = false;
        } else {
          inp.value = '';
        }
      });
    }

    var update = function (fromUser) {
      var show = el.value === '1' || el.value === 'yes';
      target.classList.toggle('d-none', !show);
      if (!show && fromUser) {
        clearToggleDetailFields(target);
      }
      target.querySelectorAll('label.form-label').forEach(function (lab) {
        lab.classList.toggle('required', show);
      });
      target.querySelectorAll('input, textarea').forEach(function (inp) {
        if (inp.classList.contains('flatpickr-alt-input')) {
          inp.required = show;
          return;
        }
        if (inp._flatpickr) {
          inp.required = false;
          inp.removeAttribute('required');
          if (inp._flatpickr.altInput) {
            inp._flatpickr.altInput.required = show;
          }
          if (typeof syncFlatpickrRequired === 'function') {
            syncFlatpickrRequired(inp, inp._flatpickr);
          }
          return;
        }
        inp.required = show;
      });
    };
    el.addEventListener('change', function () {
      update(true);
    });
    update(false);
  });

  // Dropdown / multi-select "Others" free-text capture
  function syncOthersGroup(group) {
    if (!group) return;
    var trigger = group.querySelector('[data-others-trigger]');
    var fieldWrap = group.querySelector('[data-others-field]');
    if (!trigger || !fieldWrap) return;

    var show = false;
    if (trigger.tagName === 'SELECT') {
      show = trigger.value === '__others__' || String(trigger.value).toLowerCase() === 'others';
    } else if (trigger.type === 'checkbox') {
      show = !!trigger.checked;
    }

    if (show) {
      fieldWrap.removeAttribute('hidden');
    } else {
      fieldWrap.setAttribute('hidden', 'hidden');
    }

    fieldWrap.querySelectorAll('input, textarea').forEach(function (inp) {
      inp.disabled = !show;
      if (!show) {
        // Keep value for re-open; only clear if never used is not needed —
        // disabled fields are not posted, which is correct.
      }
    });
  }

  function bindOthersGroup(group) {
    if (!group || group.dataset.othersBound === '1') return;
    group.dataset.othersBound = '1';
    var trigger = group.querySelector('[data-others-trigger]');
    if (!trigger) return;
    trigger.addEventListener('change', function () {
      syncOthersGroup(group);
    });
    syncOthersGroup(group);
  }

  document.querySelectorAll('[data-others-group]').forEach(bindOthersGroup);

  // Re-bind Others handlers when dynamic rows are added
  document.querySelectorAll('[data-add-row]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      setTimeout(function () {
        var target = document.querySelector(btn.getAttribute('data-add-row'));
        if (!target) return;
        var container = getRowContainer(target);
        if (!container) return;
        // Re-bind on the newest *visible* row only (skip hidden template)
        var visible = getVisibleRows(container);
        if (!visible.length) return;
        var last = visible[visible.length - 1];
        // Ensure all controls on the new row are enabled (cloned from disabled template)
        last.querySelectorAll('input, select, textarea').forEach(function (el) {
          if (el.closest('[data-others-field]')) return; // leave "other" text gated
          el.disabled = false;
          el.removeAttribute('disabled');
          el.readOnly = false;
        });
        last.querySelectorAll('[data-others-group]').forEach(function (group) {
          delete group.dataset.othersBound;
          bindOthersGroup(group);
          syncOthersGroup(group);
        });
      }, 0);
    });
  });

  // ---------- Password show / hide ----------
  function setPasswordToggleState(input, btn, showPlain) {
    input.type = showPlain ? 'text' : 'password';
    btn.setAttribute('aria-pressed', showPlain ? 'true' : 'false');
    btn.setAttribute('aria-label', showPlain ? 'Hide password' : 'Show password');
    btn.setAttribute('title', showPlain ? 'Hide password' : 'Show password');
    btn.innerHTML = showPlain
      ? '<i class="bi bi-eye-slash" aria-hidden="true"></i><span class="visually-hidden">Hide password</span>'
      : '<i class="bi bi-eye" aria-hidden="true"></i><span class="visually-hidden">Show password</span>';
  }

  function bindPasswordToggle(input, btn) {
    if (!input || !btn || btn.dataset.passwordToggleBound === '1') return;
    btn.dataset.passwordToggleBound = '1';
    btn.addEventListener('click', function () {
      setPasswordToggleState(input, btn, input.type === 'password');
    });
  }

  function enhancePasswordField(input) {
    if (!input || input.dataset.passwordToggleReady === '1') return;
    if (input.getAttribute('data-no-password-toggle') === '1') return;
    input.dataset.passwordToggleReady = '1';

    var group = input.closest('.input-group');
    if (!group) {
      group = document.createElement('div');
      group.className = 'input-group password-toggle-group';
      input.parentNode.insertBefore(group, input);
      group.appendChild(input);
    } else {
      group.classList.add('password-toggle-group');
    }

    var btn = group.querySelector('[data-password-toggle]');
    if (!btn) {
      btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-outline-secondary password-toggle-btn';
      btn.setAttribute('data-password-toggle', '1');
      setPasswordToggleState(input, btn, false);
      group.appendChild(btn);
    }

    bindPasswordToggle(input, btn);
  }

  document.querySelectorAll('input[type="password"]').forEach(enhancePasswordField);
});

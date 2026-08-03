document.addEventListener('DOMContentLoaded', function () {
  // ---------- GIGW accessibility controls ----------
  var root = document.documentElement;
  var body = document.body;
  var FONT_KEY = 'sad_font_scale';
  var CONTRAST_KEY = 'sad_contrast';
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

  function getVisibleRows(container) {
    return container.querySelectorAll('.dynamic-row:not([data-row-template]):not(.d-none), tr.dynamic-row:not([data-row-template])');
  }

  function clearFields(root) {
    root.querySelectorAll('input, select, textarea').forEach(function (el) {
      el.disabled = false;
      if (el.type === 'checkbox' || el.type === 'radio') {
        el.checked = false;
      } else {
        el.value = '';
      }
    });
  }

  // Add dynamic rows
  document.querySelectorAll('[data-add-row]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = document.querySelector(btn.getAttribute('data-add-row'));
      if (!target) return;
      var container = getRowContainer(target);
      var template = target.querySelector('[data-row-template]') || container.querySelector('[data-row-template]');
      if (!template) return;

      var clone = template.cloneNode(true);
      clone.removeAttribute('data-row-template');
      clone.classList.remove('d-none');
      clearFields(clone);
      // Allow Others toggles to re-bind on the new row
      clone.querySelectorAll('[data-others-group]').forEach(function (g) {
        delete g.dataset.othersBound;
      });
      container.appendChild(clone);
    });
  });

  // Remove dynamic rows
  document.querySelectorAll('[data-rows]').forEach(function (host) {
    host.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('[data-remove-row]');
      if (!removeBtn) return;

      var container = getRowContainer(host);
      var row = removeBtn.closest('tr.dynamic-row, .dynamic-row');
      if (!row || row.hasAttribute('data-row-template')) return;

      var visible = getVisibleRows(container);
      // Keep at least one visible row
      if (visible.length <= 1) {
        clearFields(row);
        return;
      }
      row.remove();
    });
  });

  // Age (years + months) as on a reference date — personal details step
  function normalizeDateStr(value) {
    if (!value) return '';
    // Accept YYYY-MM-DD or YYYY-MM-DD HH:MM:SS / ISO
    var m = String(value).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!m) return '';
    return m[1] + '-' + m[2] + '-' + m[3];
  }

  function calcAgeParts(dobStr, asOnStr) {
    dobStr = normalizeDateStr(dobStr);
    asOnStr = normalizeDateStr(asOnStr) || '2026-01-01';
    if (!dobStr) return null;

    var dobParts = dobStr.split('-').map(Number);
    var asParts = asOnStr.split('-').map(Number);
    if (dobParts.length !== 3 || asParts.length !== 3) return null;

    var y1 = dobParts[0],
      m1 = dobParts[1],
      d1 = dobParts[2];
    var y2 = asParts[0],
      m2 = asParts[1],
      d2 = asParts[2];
    if (!y1 || !m1 || !d1 || !y2 || !m2 || !d2) return null;

    // Reject future DOB relative to as-on date
    if (y1 > y2 || (y1 === y2 && m1 > m2) || (y1 === y2 && m1 === m2 && d1 > d2)) {
      return null;
    }

    var years = y2 - y1;
    var months = m2 - m1;
    var days = d2 - d1;
    if (days < 0) {
      months -= 1;
    }
    if (months < 0) {
      years -= 1;
      months += 12;
    }
    if (years < 0) return null;
    return { years: years, months: months };
  }

  function updateAgeDisplays(input) {
    if (!input) return;
    var asOn = input.getAttribute('data-age-as-on') || '2026-01-01';
    var yearsId = input.getAttribute('data-age-years-target');
    var monthsId = input.getAttribute('data-age-months-target');
    var yearsEl = yearsId ? document.getElementById(yearsId) : null;
    var monthsEl = monthsId ? document.getElementById(monthsId) : null;
    if (!yearsEl && !monthsEl) return;

    var parts = calcAgeParts(input.value, asOn);
    if (yearsEl) {
      yearsEl.value = parts ? String(parts.years) : '';
      yearsEl.readOnly = true;
    }
    if (monthsEl) {
      monthsEl.value = parts ? String(parts.months) : '';
      monthsEl.readOnly = true;
    }
  }

  function bindAgeAutoCalc() {
    document.querySelectorAll('input[data-age-as-on]').forEach(function (input) {
      ['change', 'input', 'blur'].forEach(function (evt) {
        input.addEventListener(evt, function () {
          updateAgeDisplays(input);
        });
      });
      // Initial fill when DOB is already present
      updateAgeDisplays(input);
    });
  }

  bindAgeAutoCalc();

  // CAPTCHA refresh
  document.querySelectorAll('.captcha-refresh').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var sel = btn.getAttribute('data-captcha-img');
      var img = sel ? document.querySelector(sel) : null;
      if (!img) return;
      var base = img.getAttribute('src').split('?')[0];
      img.setAttribute('src', base + '?t=' + Date.now());
      var input = document.querySelector('input[name="captcha"]');
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
    var update = function () {
      var show = el.value === '1' || el.value === 'yes';
      target.classList.toggle('d-none', !show);
      target.querySelectorAll('input, textarea').forEach(function (inp) {
        inp.required = show;
      });
    };
    el.addEventListener('change', update);
    update();
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
        document.querySelectorAll('[data-others-group]').forEach(function (group) {
          // New clones need a fresh bind flag
          if (!group.dataset.othersBound) {
            bindOthersGroup(group);
          }
        });
        // Clones copy data-others-bound — rebind uncloned groups without handlers
        var target = document.querySelector(btn.getAttribute('data-add-row'));
        if (!target) return;
        var container = getRowContainer(target);
        if (!container) return;
        var rows = container.querySelectorAll('.dynamic-row');
        if (!rows.length) return;
        var last = rows[rows.length - 1];
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

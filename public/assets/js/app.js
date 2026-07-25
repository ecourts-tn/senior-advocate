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
});

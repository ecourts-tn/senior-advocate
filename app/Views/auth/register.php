<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$prefill = $prefill ?? [];
$val = static function (string $key, $default = '') use ($prefill) {
    $old = old($key);
    if ($old !== null && $old !== '') {
        return $old;
    }
    if (isset($prefill[$key]) && $prefill[$key] !== '') {
        return $prefill[$key];
    }

    return $default;
};
$lookupOk  = $lookupOk ?? null;
$lookupMsg = $lookupMsg ?? null;
?>

<div class="auth-wrap auth-wide">
    <div class="card card-mhc auth-card">
        <div class="card-header">
            <div class="auth-icon"><i class="bi bi-person-plus"></i></div>
            <h2 class="auth-title">Advocate Registration</h2>
            <p class="auth-sub">Create an account to apply for Senior Advocate designation</p>
        </div>
        <div class="card-body">
            <div class="alert alert-warning py-2 small mb-3">
                <i class="bi bi-exclamation-circle me-1"></i>
                Register with the <strong>same full name</strong> as on your Enrolment Certificate. Abbreviated names will not be accepted.
            </div>

            <?= form_open('register', [
                'id'                     => 'registerForm',
                'data-mail-loader'       => '1',
                'data-mail-loader-text'  => 'Creating your account and sending the verification email…',
            ]) ?>

            <!-- Step 1: Enrolment lookup (own CAPTCHA + rate-limited POST) -->
            <div class="border rounded p-3 mb-4 bg-light">
                <h3 class="h6 mb-2">
                    <i class="bi bi-search me-1"></i>
                    Search advocate details by enrolment number
                </h3>
                <p class="small text-muted mb-3">
                    Enter your Bar Council enrolment number and complete the <strong>search</strong> security check, then search.
                    If a match is found, your name and mobile (when available) will be filled automatically.
                    Otherwise you may enter details manually.
                </p>
                <div class="row g-2 align-items-end">
                    <div class="col-md-7">
                        <label class="form-label required" for="enrolment_number">Enrolment Number</label>
                        <input type="text" name="enrolment_number" id="enrolment_number" class="form-control"
                               value="<?= esc($val('enrolment_number')) ?>"
                               required maxlength="40"
                               placeholder="e.g. 1234/2010" autocomplete="off"
                               title="Unique by serial number and year (e.g. 1234/2010)">
                    </div>
                    <div class="col-md-5">
                        <button type="button" class="btn btn-outline-primary w-100" id="btnLookupAdvocate">
                            <i class="bi bi-search me-1"></i> Search Database
                        </button>
                    </div>
                </div>
                <div class="mt-3">
                    <?= view('partials/captcha_field', [
                        'fieldId'   => 'lookupCaptcha',
                        'inputName' => 'lookup_captcha',
                        'scope'     => \App\Libraries\CaptchaService::SCOPE_LOOKUP,
                        'label'     => 'Search security check (CAPTCHA)',
                        'help'      => 'Required only for searching the advocate database. Independent of the registration CAPTCHA below.',
                        'required'  => false, // validated on Search click; not needed for Create Account
                    ]) ?>
                </div>
                <div id="lookupStatus" class="mt-2 small" role="status" aria-live="polite"
                     <?php if ($lookupMsg): ?>
                        data-server-msg="<?= esc($lookupMsg) ?>"
                        data-server-ok="<?= $lookupOk ? '1' : '0' ?>"
                     <?php endif; ?>
                ></div>
            </div>

            <div class="mb-3">
                <label class="form-label required" for="name">Full Name (as per Enrolment Certificate)</label>
                <input type="text" name="name" id="name" class="form-control"
                       value="<?= esc($val('name')) ?>" required autocomplete="name"
                       placeholder="As printed on enrolment certificate">
            </div>
            <div class="mb-3">
                <label class="form-label required" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control"
                       value="<?= esc($val('email')) ?>" required autocomplete="email"
                       placeholder="name@example.com">
            </div>
            <div class="mb-3">
                <label class="form-label required" for="mobile">Mobile</label>
                <input type="text" name="mobile" id="mobile" class="form-control"
                       value="<?= esc($val('mobile')) ?>" required maxlength="15"
                       autocomplete="tel" placeholder="10-digit mobile number">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control"
                           required minlength="8" autocomplete="new-password"
                           placeholder="Min. 8 characters">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required" for="password_confirm">Confirm Password</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                           required autocomplete="new-password" placeholder="Re-enter password">
                </div>
            </div>

            <div class="border rounded p-3 mb-3">
                <p class="small text-muted mb-2 mb-md-3">
                    Complete this <strong>registration</strong> CAPTCHA to create your account.
                    It is separate from the search CAPTCHA above — searching does not invalidate it.
                </p>
                <?= view('partials/captcha_field', [
                    'fieldId'   => 'registerCaptcha',
                    'inputName' => 'captcha',
                    'scope'     => \App\Libraries\CaptchaService::SCOPE_REGISTER,
                    'label'     => 'Registration security check (CAPTCHA)',
                    'help'      => 'Required to create your account. Case-insensitive. Click Refresh if the image is hard to read.',
                ]) ?>
            </div>

            <button type="submit" class="btn btn-mhc w-100 py-2">
                <i class="bi bi-check2-circle me-1"></i> Create Account
            </button>
            <?= form_close() ?>

            <p class="mt-3 mb-0 small text-center">
                Already registered? <a href="<?= base_url('login') ?>" class="fw-semibold">Login</a>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var form = document.getElementById('registerForm');
  var lookupBtn = document.getElementById('btnLookupAdvocate');
  var enrolInput = document.getElementById('enrolment_number');
  var statusEl = document.getElementById('lookupStatus');
  var nameEl = document.getElementById('name');
  var mobileEl = document.getElementById('mobile');
  var lookupCaptchaInput = document.getElementById('lookupCaptcha');
  var lookupCaptchaImg = document.getElementById('lookupCaptchaImg');
  var lookupUrl = <?= json_encode(base_url('register/lookup')) ?>;
  var captchaImageBase = <?= json_encode(base_url('captcha/image')) ?>;
  var csrfName = <?= json_encode(csrf_token()) ?>;
  var csrfHash = <?= json_encode(csrf_hash()) ?>;

  function setStatus(msg, ok) {
    if (!statusEl) return;
    statusEl.textContent = msg || '';
    statusEl.className = 'mt-2 small ' + (ok === true ? 'text-success' : (ok === false ? 'text-danger' : 'text-muted'));
  }

  /** Refresh only the search CAPTCHA (does not touch registration CAPTCHA). */
  function refreshLookupCaptcha() {
    if (lookupCaptchaImg) {
      var bust = captchaImageBase + '?scope=lookup&t=' + Date.now() + '&r=' + Math.random().toString(36).slice(2);
      lookupCaptchaImg.src = bust;
    }
    if (lookupCaptchaInput) {
      lookupCaptchaInput.value = '';
    }
  }

  function currentCsrf() {
    // Prefer live form hidden field (stays in sync if token ever rotates).
    if (form) {
      var el = form.querySelector('input[name="' + csrfName + '"]');
      if (el && el.value) return el.value;
    }
    return csrfHash;
  }

  // Show flash from server-side lookup redirect
  if (statusEl && statusEl.dataset.serverMsg) {
    setStatus(statusEl.dataset.serverMsg, statusEl.dataset.serverOk === '1');
  }

  function doLookup() {
    var en = (enrolInput.value || '').trim();
    if (!en) {
      setStatus('Please enter an enrolment number.', false);
      enrolInput.focus();
      return;
    }
    var captchaVal = lookupCaptchaInput ? (lookupCaptchaInput.value || '').trim() : '';
    if (!captchaVal || captchaVal.length < 4) {
      setStatus('Please complete the search CAPTCHA before searching.', false);
      if (lookupCaptchaInput) lookupCaptchaInput.focus();
      return;
    }

    setStatus('Searching…', null);
    lookupBtn.disabled = true;

    var body = new FormData();
    body.append('enrolment_number', en);
    body.append('lookup_captcha', captchaVal);
    body.append('format', 'json');
    body.append(csrfName, currentCsrf());

    fetch(lookupUrl, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': currentCsrf()
      },
      body: body,
      credentials: 'same-origin'
    })
      .then(function (r) {
        return r.json().then(function (j) {
          return { ok: r.ok, status: r.status, data: j };
        }).catch(function () {
          return { ok: r.ok, status: r.status, data: {} };
        });
      })
      .then(function (res) {
        var d = res.data || {};
        // Search captcha is one-time — refresh only the lookup challenge.
        refreshLookupCaptcha();

        if (res.status === 429) {
          setStatus(d.message || 'Too many lookups. Please wait and try again.', false);
          return;
        }
        if (d.captcha_required || res.status === 422 && (d.message || '').toLowerCase().indexOf('captcha') !== -1) {
          setStatus(d.message || 'Invalid search CAPTCHA. Please try again.', false);
          return;
        }
        if (d.already_registered) {
          setStatus(d.message || 'Already registered. Please log in.', false);
          return;
        }
        if (d.enrolment_number) {
          enrolInput.value = d.enrolment_number;
        }
        if (d.found) {
          if (d.name && nameEl) nameEl.value = d.name;
          if (d.mobile && mobileEl) mobileEl.value = d.mobile || mobileEl.value;
          setStatus(d.message || 'Advocate details found. Please verify and complete the form.', true);
        } else {
          setStatus(d.message || 'Not found. Please enter details manually.', false);
        }
      })
      .catch(function () {
        refreshLookupCaptcha();
        setStatus('Lookup failed. You may still register by entering details manually.', false);
      })
      .finally(function () {
        lookupBtn.disabled = false;
      });
  }

  if (lookupBtn) {
    lookupBtn.addEventListener('click', doLookup);
  }
  if (enrolInput) {
    enrolInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        doLookup();
      }
    });
  }
})();
</script>
<?= $this->endSection() ?>

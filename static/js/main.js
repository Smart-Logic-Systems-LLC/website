/* Smart Logic Systems — site behaviour (no dependencies). */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ----- Header: scrolled state + mobile menu ----------------------------- */
  function initHeader() {
    var header = document.querySelector('[data-header]');
    if (!header) return;
    var toggle = header.querySelector('[data-nav-toggle]');
    var nav = header.querySelector('#site-nav');

    function onScroll() {
      header.classList.toggle('is-scrolled', window.scrollY > 24);
    }
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    if (!toggle || !nav) return;

    function setOpen(open) {
      header.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }

    toggle.addEventListener('click', function () {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && header.classList.contains('is-open')) {
        setOpen(false);
        toggle.focus();
      }
    });

    document.addEventListener('click', function (e) {
      if (header.classList.contains('is-open') && !header.contains(e.target)) setOpen(false);
    });

    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) setOpen(false);
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', function (mq) {
      if (mq.matches) setOpen(false);
    });
  }

  /* ----- Placeholder links (destinations still to be confirmed) ----------- */
  function initPlaceholderLinks() {
    document.addEventListener('click', function (e) {
      var link = e.target.closest('a[href="#"]');
      if (link) e.preventDefault();
    });
  }

  /* ----- FAQ accordion ----------------------------------------------------- */
  function initAccordion() {
    document.querySelectorAll('[data-accordion]').forEach(function (root) {
      root.addEventListener('click', function (e) {
        var trigger = e.target.closest('.faq__trigger');
        if (!trigger || !root.contains(trigger)) return;
        var item = trigger.closest('.faq__item');
        var panel = document.getElementById(trigger.getAttribute('aria-controls'));
        var open = trigger.getAttribute('aria-expanded') !== 'true';
        trigger.setAttribute('aria-expanded', String(open));
        item.classList.toggle('is-open', open);
        if (panel) panel.inert = !open;
      });
    });
  }

  /* ----- Case study tabs (Industries) -------------------------------------- */
  function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach(function (root) {
      var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));

      function select(tab, focus) {
        tabs.forEach(function (t) {
          var active = t === tab;
          t.classList.toggle('is-active', active);
          t.setAttribute('aria-selected', String(active));
          t.tabIndex = active ? 0 : -1;
          var panel = document.getElementById(t.getAttribute('aria-controls'));
          if (panel) panel.hidden = !active;
        });
        if (focus) tab.focus();
      }

      tabs.forEach(function (tab, i) {
        tab.addEventListener('click', function () {
          select(tab, false);
        });
        tab.addEventListener('keydown', function (e) {
          var next = null;
          if (e.key === 'ArrowDown' || e.key === 'ArrowRight') next = tabs[(i + 1) % tabs.length];
          if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') next = tabs[(i - 1 + tabs.length) % tabs.length];
          if (e.key === 'Home') next = tabs[0];
          if (e.key === 'End') next = tabs[tabs.length - 1];
          if (next) {
            e.preventDefault();
            select(next, true);
          }
        });
      });
    });
  }

  /* ----- Solutions directory: search, filters, pagination ----------------- */
  function initDirectory() {
    var root = document.querySelector('[data-directory]');
    if (!root) return;

    var PAGE_SIZE = 6;
    var search = root.querySelector('[data-directory-search]');
    var filters = Array.prototype.slice.call(root.querySelectorAll('[data-filter]'));
    var body = root.querySelector('[data-directory-rows]');
    var rows = Array.prototype.slice.call(body.querySelectorAll('tr[data-type]'));
    var empty = root.querySelector('[data-directory-empty]');
    var pager = root.querySelector('[data-pagination]');
    var prev = pager.querySelector('[data-page-prev]');
    var next = pager.querySelector('[data-page-next]');
    var state = { query: '', type: 'all', page: 1 };

    rows.forEach(function (row) {
      row.dataset.text = row.textContent.replace(/\s+/g, ' ').trim().toLowerCase();
    });

    function matches(row) {
      var typeOk = state.type === 'all' || row.dataset.type === state.type;
      if (!typeOk) return false;
      if (!state.query) return true;
      return state.query.split(/\s+/).every(function (word) {
        return row.dataset.text.indexOf(word) !== -1;
      });
    }

    function renderPager(pages) {
      pager.querySelectorAll('[data-page]').forEach(function (b) {
        b.remove();
      });
      for (var p = 1; p <= pages; p++) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pagination__btn';
        btn.dataset.page = String(p);
        btn.textContent = String(p);
        btn.setAttribute('aria-label', 'Page ' + p);
        if (p === state.page) btn.setAttribute('aria-current', 'page');
        pager.insertBefore(btn, next);
      }
      prev.disabled = state.page <= 1;
      next.disabled = state.page >= pages;
    }

    function render() {
      var visible = rows.filter(matches);
      var pages = Math.max(1, Math.ceil(visible.length / PAGE_SIZE));
      state.page = Math.min(state.page, pages);
      var start = (state.page - 1) * PAGE_SIZE;
      rows.forEach(function (row) {
        var idx = visible.indexOf(row);
        row.classList.toggle('is-hidden', idx === -1 || idx < start || idx >= start + PAGE_SIZE);
      });
      empty.hidden = visible.length !== 0;
      renderPager(pages);
    }

    function setQuery(q) {
      state.query = q.trim().toLowerCase();
      state.page = 1;
      if (search.value !== q) search.value = q;
      render();
    }

    function setType(type) {
      state.type = type;
      state.page = 1;
      filters.forEach(function (f) {
        f.setAttribute('aria-pressed', String(f.dataset.filter === type));
      });
      render();
    }

    search.addEventListener('input', function () {
      setQuery(search.value);
    });

    filters.forEach(function (f) {
      f.addEventListener('click', function () {
        setType(f.dataset.filter);
      });
    });

    pager.addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn || btn.disabled) return;
      if (btn.hasAttribute('data-page-prev')) state.page -= 1;
      else if (btn.hasAttribute('data-page-next')) state.page += 1;
      else if (btn.dataset.page) state.page = Number(btn.dataset.page);
      render();
    });

    function goToDirectory() {
      root.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    }

    // Hero "Solution finder" shortcuts
    var finder = document.querySelector('[data-finder-form]');
    if (finder) {
      finder.addEventListener('submit', function (e) {
        e.preventDefault();
        setType('all');
        setQuery(finder.querySelector('input').value);
        goToDirectory();
      });
    }
    document.querySelectorAll('[data-directory-query]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        setType('all');
        setQuery(link.dataset.directoryQuery);
        goToDirectory();
      });
    });
    document.querySelectorAll('[data-directory-reset]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        setType('all');
        setQuery('');
        goToDirectory();
      });
    });

    var initial = new URLSearchParams(window.location.search).get('q');
    if (initial) setQuery(initial);
    else render();
  }

  /* ----- Forms ------------------------------------------------------------- */
  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  function setStatus(el, message, kind) {
    if (!el) return;
    el.textContent = message;
    el.classList.toggle('is-error', kind === 'error');
    el.classList.toggle('is-success', kind === 'success');
  }

  function fieldError(field, message) {
    var err = document.getElementById(field.id + '-error');
    field.setAttribute('aria-invalid', message ? 'true' : 'false');
    if (err) {
      err.textContent = message || '';
      if (message) field.setAttribute('aria-describedby', err.id);
      else field.removeAttribute('aria-describedby');
    }
    return !message;
  }

  function submitTo(endpoint, form) {
    return fetch(endpoint, {
      method: 'POST',
      body: new FormData(form),
      headers: { Accept: 'application/json' }
    }).then(function (res) {
      if (!res.ok) throw new Error('Request failed');
    });
  }

  function initContactForm() {
    var form = document.querySelector('[data-contact-form]');
    if (!form) return;
    var status = form.querySelector('.form-status');
    var reason = form.querySelector('#cf-reason');

    // Pre-select a reason from ?reason=sales|support|general
    var wanted = new URLSearchParams(window.location.search).get('reason');
    if (wanted && reason.querySelector('option[value="' + wanted + '"]')) reason.value = wanted;

    function validate() {
      var ok = true;
      var name = form.elements.name;
      var email = form.elements.email;
      var message = form.elements.message;
      ok = fieldError(name, name.value.trim() ? '' : 'Please enter your name.') && ok;
      ok = fieldError(email, EMAIL_RE.test(email.value.trim()) ? '' : 'Please enter a valid email address.') && ok;
      ok = fieldError(reason, reason.value ? '' : 'Please choose a reason for your inquiry.') && ok;
      ok = fieldError(message, message.value.trim() ? '' : 'Please enter a message.') && ok;
      return ok;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      setStatus(status, '');
      if (!validate()) {
        var firstBad = form.querySelector('[aria-invalid="true"]');
        if (firstBad) firstBad.focus();
        return;
      }
      var endpoint = form.dataset.endpoint;
      if (endpoint) {
        submitTo(endpoint, form).then(function () {
          form.reset();
          setStatus(status, 'Thanks — your message has been sent.', 'success');
        }).catch(function () {
          setStatus(status, 'Sorry, something went wrong. Please email us directly.', 'error');
        });
        return;
      }
      // No form backend configured yet: hand the message to the visitor's email app.
      var option = reason.options[reason.selectedIndex];
      var to = option.dataset.email || 'hello@smartlogicsystems.com';
      var subject = form.elements.subject.value.trim() || option.textContent;
      var bodyText = form.elements.message.value.trim() + '\n\n— ' + form.elements.name.value.trim() +
        ' (' + form.elements.email.value.trim() + ')';
      window.location.href = 'mailto:' + to + '?subject=' + encodeURIComponent(subject) +
        '&body=' + encodeURIComponent(bodyText);
      setStatus(status, 'Opening your email app to send this message to ' + to + '.', 'success');
    });

    form.addEventListener('input', function (e) {
      if (e.target.getAttribute('aria-invalid') === 'true') fieldError(e.target, '');
    });
  }

  function initSubscribeForm() {
    var form = document.querySelector('[data-subscribe-form]');
    if (!form) return;
    var status = form.querySelector('.form-status');
    var email = form.elements.email;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!EMAIL_RE.test(email.value.trim())) {
        email.setAttribute('aria-invalid', 'true');
        setStatus(status, 'Please enter a valid email address.', 'error');
        email.focus();
        return;
      }
      email.setAttribute('aria-invalid', 'false');
      var endpoint = form.dataset.endpoint;
      if (!endpoint) {
        // TODO: connect to the newsletter provider once it is chosen.
        setStatus(status, 'Subscriptions are not connected yet.', 'error');
        return;
      }
      submitTo(endpoint, form).then(function () {
        form.reset();
        setStatus(status, 'Thanks! You’re subscribed.', 'success');
      }).catch(function () {
        setStatus(status, 'Sorry, something went wrong. Please try again later.', 'error');
      });
    });
  }

  function init() {
    initHeader();
    initPlaceholderLinks();
    initAccordion();
    initTabs();
    initDirectory();
    initContactForm();
    initSubscribeForm();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();

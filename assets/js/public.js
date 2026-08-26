/* ============================================================
   Public Portfolio JS
   Amanuel Yohannes Â· Graphic Designer
   ============================================================ */

(function () {
  'use strict';

  /* -- Dark mode toggle ----------------------------------- */
  const html         = document.documentElement;
  const toggleBtns   = document.querySelectorAll('#themeToggle, #themeToggleDesktop');
  const savedTheme   = localStorage.getItem('theme') || 'dark';

  function applyTheme(theme) {
    html.classList.add('theme-transitioning');
    html.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    setTimeout(() => html.classList.remove('theme-transitioning'), 350);
  }

  applyTheme(savedTheme);

  toggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
  });

  /* -- Hero side images + stats side image scroll-in ------- */
  (function () {
    const heroSection  = document.querySelector('.hero');
    const sideImgLeft  = document.querySelector('.hero-side-img--left');
    const sideImgRight = document.querySelector('.hero-side-img--right');
    const statsSection = document.querySelector('.ap-stats');
    const statsSideImg = document.querySelector('.stats-side-img');
    const onScroll = () => {
      if (heroSection && sideImgLeft && sideImgRight) {
        const r = heroSection.getBoundingClientRect();
        const v = r.top < window.innerHeight && r.bottom > 0;
        sideImgLeft.classList.toggle('hero-side-img--visible', v);
        sideImgRight.classList.toggle('hero-side-img--visible', v);
      }
      if (statsSection && statsSideImg) {
        const r = statsSection.getBoundingClientRect();
        statsSideImg.classList.toggle('stats-side-img--visible', r.top < window.innerHeight && r.bottom > 0);
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  })();

  /* -- Navbar scroll state --------------------------------- */
  const nav = document.getElementById('siteNav');
  if (nav) {
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 40);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* -- Mobile menu ----------------------------------------- */
  const burger     = document.getElementById('navBurger');
  const mobileMenu = document.getElementById('mobileMenu');
  if (burger && mobileMenu) {
    burger.addEventListener('click', () => {
      const isOpen = mobileMenu.classList.toggle('open');
      burger.classList.toggle('open', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });
    mobileMenu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        burger.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
  }

  /* -- Fade-up intersection observer ----------------------- */
  const fadeEls = document.querySelectorAll('.fade-up');
  if (fadeEls.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
    fadeEls.forEach(el => io.observe(el));
  }

  /* -- Gallery page-turn observer -------------------------- */
  const galleryItems = document.querySelectorAll('.gallery-item');
  if (galleryItems.length) {
    let galleryDelay = 0;
    const galleryIO = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          setTimeout(() => el.classList.add('visible'), galleryDelay);
          galleryDelay = (galleryDelay + 80) % 320;
          galleryIO.unobserve(el);
        }
      });
    }, { threshold: 0.05, rootMargin: '0px 0px -30px 0px' });
    galleryItems.forEach(el => galleryIO.observe(el));
  }

  /* -- Section title scroll fill (Projects / Services / Clients) -- */
  document.querySelectorAll('.pf-section-title').forEach(title => {
    title.classList.add('pf-title--filling');
    const update = () => {
      const rect     = title.getBoundingClientRect();
      const winH     = window.innerHeight;
      const start    = winH * 0.85;
      const end      = winH * 0.2;
      const progress = Math.min(Math.max((start - rect.top) / (start - end), 0), 1);
      const pct      = ((1 - progress) * 100).toFixed(1);
      title.style.setProperty('--pf-fill', `inset(0 ${pct}% 0 0)`);
    };
    window.addEventListener('scroll', update, { passive: true });
    update();
  });

  /* -- Project card stack --------------------------------- */
  const pfStack    = document.getElementById('pfStack');
  const pfCtrl     = document.getElementById('pfStackCtrl');
  const filterItems = document.querySelectorAll('.filter-dropdown-item');
  const filterBtn   = document.getElementById('filterDropdownBtn');
  const filterLabel = document.getElementById('filterDropdownLabel');
  const filterDrop  = document.getElementById('filterDropdown');
  const filterList  = document.getElementById('filterDropdownList');
  const BATCH       = 5;
  const NAV_H       = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h')) || 68;
  const CARD_OFFSET = 28;

  // Dropdown open/close
  if (filterBtn && filterDrop) {
    filterBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      filterDrop.classList.toggle('open');
      filterBtn.setAttribute('aria-expanded', filterDrop.classList.contains('open'));
    });
    document.addEventListener('click', () => {
      filterDrop.classList.remove('open');
      filterBtn.setAttribute('aria-expanded', 'false');
    });
    filterList && filterList.addEventListener('click', e => e.stopPropagation());
  }

  if (pfStack) {
    const allCards = Array.from(pfStack.querySelectorAll('.pf-card'));
    let activeCat  = 'all';
    let batchStart = 0;
    let stepH      = window.innerHeight * 0.85;

    // Hide every card with a hard inline style — no class dependency
    function hideAll() {
      allCards.forEach(c => {
        c.style.cssText = 'display:none;';
        c.classList.remove('pf-card--visible', 'pf-card--buried', 'pf-card--hidden');
      });
    }

    function getFiltered() {
      return allCards.filter(c => activeCat === 'all' || c.dataset.cat === activeCat);
    }

    function setHeight(batch) {
      const cardH    = batch[0].offsetHeight || 560;
      const isMobile = window.innerWidth <= 768;
      const refH     = isMobile ? Math.max(window.innerHeight, screen.height * 0.6) : window.innerHeight;
      stepH = Math.round(refH * (isMobile ? 0.4 : 0.85));
      // On mobile, add extra height so the last sticky card fully scrolls off
      // before the Show More button enters the viewport (card bottom + 30px gap)
      const extraH = isMobile ? cardH + 30 : 0;
      pfStack.style.height = ((batch.length - 1) * stepH + cardH + CARD_OFFSET * batch.length + extraH) + 'px';
      pfCtrl.style.paddingTop = '';
    }

    function renderBatch(filtered, start) {
      hideAll();
      pfCtrl.innerHTML = '';
      pfStack.parentNode.querySelectorAll('.pf-prev-strip, .pf-stack-btn').forEach(s => s.remove());
      pfCtrl.parentNode.querySelectorAll('.pf-stack-btn').forEach(s => s.remove());

      const batch = filtered.slice(start, start + BATCH);
      if (!batch.length) {
        pfStack.style.height = '0';
        pfCtrl.innerHTML = '<p style="text-align:center;color:var(--muted);font-size:0.88rem;padding:2rem 0;">No projects in this category.</p>';
        return;
      }

      // Show Less button — above stack
      if (start > 0) {
        const btn = document.createElement('button');
        btn.className = 'pf-stack-btn';
        btn.style.cssText = 'display:flex;margin:0 auto 2rem;';
        btn.innerHTML = '<i class="bi bi-arrow-up"></i> Show Less';
        btn.addEventListener('click', () => {
          batchStart = start - BATCH;
          renderBatch(filtered, batchStart);
          window.scrollTo({ top: pfStack.getBoundingClientRect().top + window.scrollY - NAV_H - 8, behavior: 'instant' });
        });
        pfStack.parentNode.insertBefore(btn, pfStack);
      }

      // Show batch cards
      batch.forEach((card, i) => {
        card.style.cssText = '';
        card.style.display  = 'block';
        card.style.top      = (NAV_H + 24 + i * CARD_OFFSET) + 'px';
        card.style.zIndex   = 10 + i;
        card.classList.add('pf-card--visible');
        if (i < batch.length - 1) card.classList.add('pf-card--buried');
      });

      setHeight(batch);

      // Show More button — appended after pfCtrl, with z-index above cards
      if (start + BATCH < filtered.length) {
        const btn = document.createElement('button');
        btn.className = 'pf-stack-btn';
        btn.style.cssText = 'position:relative;z-index:100;display:inline-flex;';
        btn.innerHTML = 'Show More <i class="bi bi-arrow-down"></i>';
        btn.addEventListener('click', () => {
          batchStart = start + BATCH;
          renderBatch(filtered, batchStart);
          window.scrollTo({ top: pfStack.getBoundingClientRect().top + window.scrollY - NAV_H - 8, behavior: 'instant' });
        });
        pfCtrl.appendChild(btn);
      }
    }

    function init(cat) {
      activeCat  = cat;
      batchStart = 0;
      renderBatch(getFiltered(), 0);
    }

    init('all');

    window.addEventListener('resize', () => {
      const batch = getFiltered().slice(batchStart, batchStart + BATCH);
      if (batch.length) setHeight(batch);
    }, { passive: true });

    filterItems.forEach(item => {
      item.addEventListener('click', () => {
        filterItems.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
        item.classList.add('active');
        item.setAttribute('aria-selected', 'true');
        if (filterLabel) filterLabel.textContent = item.textContent.trim();
        if (filterDrop)  { filterDrop.classList.remove('open'); filterBtn.setAttribute('aria-expanded', 'false'); }
        init(item.dataset.cat || 'all');
      });
    });

    window.addEventListener('scroll', () => {
      const batch = getFiltered().slice(batchStart, batchStart + BATCH);
      if (!batch.length) return;
      const scrolled = window.scrollY - (pfStack.getBoundingClientRect().top + window.scrollY);
      const idx = Math.min(Math.max(Math.floor(scrolled / stepH), -1), batch.length - 1);
      batch.forEach((card, i) => card.classList.toggle('pf-card--buried', i < idx));
    }, { passive: true });
  }

  /* -- Testimonials dual-row marquee ----------------------- */
  document.querySelectorAll('.tcard-row').forEach(row => {
    const cards = Array.from(row.querySelectorAll('.tcard'));
    if (!cards.length) return;
    const track = document.createElement('div');
    track.className = 'tcard-track';
    // Fill one set wide enough to exceed screen, then duplicate for seamless -50% loop
    const cardW  = 340;
    const needed = Math.max(cards.length, Math.ceil((window.innerWidth * 1.5) / cardW));
    const times  = Math.ceil(needed / cards.length);
    const oneSet = [];
    for (let i = 0; i < times; i++) {
      cards.forEach(c => oneSet.push(i === 0 ? c : c.cloneNode(true)));
    }
    oneSet.forEach(c => track.appendChild(c));
    oneSet.forEach(c => track.appendChild(c.cloneNode(true)));
    row.appendChild(track);
    // Pause only on horizontal swipe, not vertical scroll
    let _tx = 0, _ty = 0;
    row.addEventListener('touchstart', e => { _tx = e.touches[0].clientX; _ty = e.touches[0].clientY; }, { passive: true });
    row.addEventListener('touchmove', e => {
      if (Math.abs(e.touches[0].clientX - _tx) > Math.abs(e.touches[0].clientY - _ty))
        track.style.animationPlayState = 'paused';
    }, { passive: true });
    row.addEventListener('touchend', () => { track.style.animationPlayState = ''; }, { passive: true });
  });

  /* -- Stats count-up -------------------------------------- */
  const statNums = document.querySelectorAll('.ap-stat-num[data-count]');
  if (statNums.length) {
    const countIO = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el       = entry.target;
        const target   = +el.dataset.count;
        const suffix   = el.dataset.suffix || '';
        const duration = 2600;
        const start    = performance.now();
        const tick = (now) => {
          const progress = Math.min((now - start) / duration, 1);
          const ease     = 1 - Math.pow(1 - progress, 4);
          el.textContent = Math.floor(ease * target) + suffix;
          if (progress < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
        countIO.unobserve(el);
      });
    }, { threshold: 0.5 });
    statNums.forEach(el => countIO.observe(el));
  }

  /* -- About page hero â€” trigger immediately on load ------- */
  const apImg     = document.getElementById('apImg');
  const apContent = document.getElementById('apContent');
  if (apImg || apContent) {
    requestAnimationFrame(() => setTimeout(() => {
      if (apImg) apImg.classList.add('ap-visible');
      if (apContent) {
        apContent.classList.add('ap-visible');
        apContent.querySelectorAll('.ap-bio p').forEach((p, i) => {
          setTimeout(() => p.classList.add('ap-bio-visible'), 400 + i * 150);
        });
      }
    }, 80));
  }

  /* -- Homepage about section scroll + bio stagger --------- */
  const aboutImg      = document.getElementById('aboutImg');
  const aboutContent  = document.getElementById('aboutContent');
  const bioParagraphs = document.querySelectorAll('.about-bio p');

  if (aboutImg || aboutContent) {
    const aboutIO = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('about-visible');
          if (entry.target === aboutContent) {
            bioParagraphs.forEach((p, i) => {
              setTimeout(() => p.classList.add('about-visible'), 300 + i * 130);
            });
          }
          aboutIO.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    if (aboutImg)     aboutIO.observe(aboutImg);
    if (aboutContent) aboutIO.observe(aboutContent);
  }

  /* -- Contact page entrance ------------------------------- */
  const cpHero = document.getElementById('cpHero');
  const cpInfo = document.getElementById('cpInfo');
  const cpForm = document.getElementById('cpForm');
  if (cpHero || cpInfo || cpForm) {
    requestAnimationFrame(() => setTimeout(() => {
      if (cpHero) cpHero.classList.add('cp-visible');
      if (cpInfo) cpInfo.classList.add('cp-visible');
      if (cpForm) cpForm.classList.add('cp-visible');
    }, 80));
  }

  /* -- Contact form AJAX submit --------------------------- */
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const btn = contactForm.querySelector('.cp-submit');
      const existingErr = contactForm.querySelector('.cp-errors');
      if (existingErr) existingErr.remove();
      if (btn) { btn.disabled = true; btn.innerHTML = '<span>Sending…</span> <i class="bi bi-arrow-repeat"></i>'; }

      try {
        const res  = await fetch(contactForm.action, { method: 'POST', body: new FormData(contactForm) });
        const html = await res.text();
        const doc  = new DOMParser().parseFromString(html, 'text/html');
        const sent = doc.getElementById('contactForm') && doc.getElementById('contactForm').dataset.sent === '1';
        const errs = Array.from(doc.querySelectorAll('.cp-errors p')).map(p => p.textContent);

        if (sent) {
          contactForm.reset();
          showToast('Message sent — I\'ll be in touch soon.');
        } else if (errs.length) {
          const box = document.createElement('div');
          box.className = 'cp-errors';
          box.innerHTML = errs.map(msg => '<p>' + msg + '</p>').join('');
          contactForm.prepend(box);
        }
      } catch (_) {
        showToast('Something went wrong. Please try again.');
      }

      if (btn) { btn.disabled = false; btn.innerHTML = '<span>Send Message</span> <i class="bi bi-arrow-right"></i>'; }
    });
  }

  function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'cp-toast';
    t.innerHTML = '<i class="bi bi-check-circle"></i> ' + msg;
    document.body.appendChild(t);
    requestAnimationFrame(() => { requestAnimationFrame(() => { t.classList.add('cp-toast--in'); }); });
    setTimeout(() => {
      t.classList.remove('cp-toast--in');
      t.addEventListener('transitionend', () => t.remove(), { once: true });
    }, 4000);
  }

  /* -- GSAP animations ------------------------------------ */
  if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);

    /* Hero entrance */
    const heroTitle = document.querySelector('.hero-title');
    const heroSub   = document.querySelector('.hero-sub');
    const heroCta   = document.querySelector('.hero-cta');
    const heroLabel = document.querySelector('.hero-label');
    if (heroTitle) {
      gsap.set([heroLabel, heroSub, heroCta], { opacity: 0, y: 30 });
      gsap.to(heroLabel, { opacity: 1, y: 0, duration: 1,   ease: 'expo.out', delay: 0.3  });
      gsap.to(heroSub,   { opacity: 1, y: 0, duration: 1,   ease: 'expo.out', delay: 0.75 });
      gsap.to(heroCta,   { opacity: 1, y: 0, duration: 0.9, ease: 'expo.out', delay: 0.95 });
      /* Lock height before typewriter so page never shifts */
      heroTitle.style.minHeight = heroTitle.offsetHeight + 'px';
      heroTitle.style.opacity = '1';
      heroTitle.style.transform = 'none';
      startTypewriter(heroTitle);
    }

    /* Card image subtle parallax on scroll */
    document.querySelectorAll('.pf-card-img img').forEach(img => {
      gsap.fromTo(img,
        { scale: 1.08 },
        {
          scale: 1,
          ease: 'none',
          scrollTrigger: {
            trigger: img.closest('.pf-card'),
            start: 'top bottom',
            end:   'top 20%',
            scrub: true,
            invalidateOnRefresh: true,
          }
        }
      );
    });

    /* Services stagger */
    const serviceItems = document.querySelectorAll('.service-item');
    if (serviceItems.length) {
      gsap.from(serviceItems, {
        scrollTrigger: { trigger: '.services-grid', start: 'top 80%' },
        opacity: 0, x: -40, duration: 0.8, stagger: 0.08, ease: 'expo.out',
      });
    }

    /* Contact heading */
    const contactHeading = document.querySelector('.contact-heading');
    if (contactHeading) {
      gsap.from(contactHeading, {
        scrollTrigger: { trigger: '.contact-section', start: 'top 75%' },
        opacity: 0, y: 50, duration: 1.2, ease: 'expo.out',
      });
    }

    window.addEventListener('resize', () => ScrollTrigger.refresh(), { passive: true });
  }

  /* -- GLightbox ------------------------------------------- */
  if (typeof GLightbox !== 'undefined') {
    GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true, autoplayVideos: true, zoomable: false });
  }

  /* -- Auto-dismiss flash alerts --------------------------- */
  document.querySelectorAll('.public-flash .alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 4500);
  });




  /* -- Pinterest gallery lightbox ------------------------- */
  (function () {
    const images  = window.PIN_IMAGES;
    if (!images || !images.length) return;

    const lightbox = document.getElementById('pinLightbox');
    const lbImg    = document.getElementById('pinLightboxImg');
    const lbVideo  = document.getElementById('pinLightboxVideo');
    const lbClose  = document.getElementById('pinLightboxClose');
    const lbPrev   = document.getElementById('pinLightboxPrev');
    const lbNext   = document.getElementById('pinLightboxNext');
    if (!lightbox) return;

    let current = 0;

    function open(idx) {
      current = idx;
      const item = images[idx];
      if (item.type === 'video') {
        lbImg.style.display   = 'none';
        lbVideo.style.display = 'block';
        lbVideo.src = item.src;
        lbVideo.play();
      } else {
        lbVideo.pause();
        lbVideo.src = '';
        lbVideo.style.display = 'none';
        lbImg.style.display   = 'block';
        lbImg.style.opacity   = '0';
        lbImg.src = item.src;
        lbImg.alt = item.alt || '';
        lbImg.onload = () => { lbImg.style.opacity = '1'; };
      }
      lightbox.classList.add('open');
      document.body.style.overflow = 'hidden';
      const multi = images.length > 1;
      lbPrev.style.display = multi ? '' : 'none';
      lbNext.style.display = multi ? '' : 'none';
    }
    function close() {
      lightbox.classList.remove('open');
      document.body.style.overflow = '';
      if (lbVideo) { lbVideo.pause(); lbVideo.src = ''; }
    }
    function prev() { open((current - 1 + images.length) % images.length); }
    function next() { open((current + 1) % images.length); }

    // Image zoom buttons
    document.querySelectorAll('.pin-zoom:not(.pin-video-play)').forEach((btn, idx) => {
      btn.addEventListener('click', (e) => { e.stopPropagation(); open(idx); });
    });
    // Video play buttons — index offset by image count
    const imgCount = images.filter(i => i.type === 'image').length;
    document.querySelectorAll('.pin-video-play').forEach((btn, idx) => {
      btn.addEventListener('click', (e) => { e.stopPropagation(); open(imgCount + idx); });
    });
    // Click on pin-frame (images)
    document.querySelectorAll('.pin-frame:not(.pin-frame--video)').forEach((frame, idx) => {
      frame.addEventListener('click', () => open(idx));
    });

    // Hover preview for video frames
    document.querySelectorAll('.pin-frame--video').forEach(frame => {
      const vid = frame.querySelector('.pin-video-el');
      const poster = frame.querySelector('.pin-video-poster');
      if (!vid) return;
      frame.addEventListener('mouseenter', () => { vid.play(); if (poster) poster.style.opacity = '0'; });
      frame.addEventListener('mouseleave', () => { vid.pause(); vid.currentTime = 0; if (poster) poster.style.opacity = '1'; });
    });

    lbClose.addEventListener('click', close);
    lbPrev.addEventListener('click', prev);
    lbNext.addEventListener('click', next);
    lightbox.addEventListener('click', e => { if (e.target === lightbox) close(); });
    document.addEventListener('keydown', e => {
      if (!lightbox.classList.contains('open')) return;
      if (e.key === 'Escape')     close();
      if (e.key === 'ArrowLeft')  prev();
      if (e.key === 'ArrowRight') next();
    });

    let tx = 0;
    lightbox.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
    lightbox.addEventListener('touchend', e => {
      const dx = e.changedTouches[0].clientX - tx;
      if (Math.abs(dx) > 45) dx < 0 ? next() : prev();
    }, { passive: true });
  })();

  /* -- Typewriter for hero title -------------------------- */
  function startTypewriter(el) {
    const fullText         = el.textContent.trim();
    const typeSpeed        = 70;
    const delSpeed         = 35;
    const pauseAfterType   = 2400;
    const pauseAfterDelete = 700;
    let   charIndex        = 0;
    let   deleting         = false;

    el.innerHTML = '<span class="tw-text"></span><span class="tw-cursor">|</span>';
    const textSpan   = el.querySelector('.tw-text');
    const cursorSpan = el.querySelector('.tw-cursor');

    function tick() {
      if (!deleting) {
        charIndex++;
        textSpan.textContent = fullText.slice(0, charIndex);
        if (charIndex === fullText.length) {
          cursorSpan.classList.add('tw-cursor--blink');
          setTimeout(() => {
            cursorSpan.classList.remove('tw-cursor--blink');
            deleting = true;
            tick();
          }, pauseAfterType);
        } else {
          setTimeout(tick, typeSpeed);
        }
      } else {
        charIndex--;
        textSpan.textContent = fullText.slice(0, charIndex);
        if (charIndex === 0) {
          deleting = false;
          setTimeout(tick, pauseAfterDelete);
        } else {
          setTimeout(tick, delSpeed);
        }
      }
    }

    setTimeout(tick, 600);
  }

})();

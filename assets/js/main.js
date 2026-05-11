/**
 * CST Department Website - Main Frontend JavaScript
 */

(function () {
  'use strict';

  /* ─── Sticky Header ─── */
  const header = document.querySelector('header');

  function handleScroll() {
    if (!header) return;
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll(); // run once on load

  /* ─── Mobile Menu Toggle ─── */
  const mobileToggle = document.querySelector('.mobile-toggle');
  const navLinks = document.querySelector('.nav-links');

  if (mobileToggle && navLinks) {
    mobileToggle.addEventListener('click', function () {
      navLinks.classList.toggle('open');
      mobileToggle.setAttribute(
        'aria-expanded',
        navLinks.classList.contains('open')
      );
    });

    // Close menu when a nav link is clicked
    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navLinks.classList.remove('open');
        mobileToggle.setAttribute('aria-expanded', 'false');
      });
    });

    // Close menu on outside click
    document.addEventListener('click', function (e) {
      if (
        !navLinks.contains(e.target) &&
        !mobileToggle.contains(e.target) &&
        navLinks.classList.contains('open')
      ) {
        navLinks.classList.remove('open');
        mobileToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ─── Smooth Scroll for Anchor Links ─── */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var targetId = this.getAttribute('href');
      if (targetId === '#') return;

      var target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        var headerOffset = header ? header.offsetHeight : 0;
        var elementPosition = target.getBoundingClientRect().top;
        var offsetPosition = elementPosition + window.scrollY - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth',
        });
      }
    });
  });

  /* ─── Lottie Fallback ─── */
  if (typeof customElements === 'undefined' || !customElements.get('lottie-player')) {
    document.querySelectorAll('lottie-player').forEach(function (el) {
      el.style.display = 'none';
    });
  }

  /* ─── Gallery Filter Tabs ─── */
  var filterTabs = document.querySelectorAll('.filter-tab');
  var galleryCards = document.querySelectorAll('.gallery-card');

  if (filterTabs.length && galleryCards.length) {
    filterTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        // Update active tab
        filterTabs.forEach(function (t) {
          t.classList.remove('active');
        });
        this.classList.add('active');

        var category = this.getAttribute('data-filter');

        galleryCards.forEach(function (card) {
          var cardCategory = card.getAttribute('data-category');
          if (category === 'all' || cardCategory === category) {
            card.style.display = '';
            card.classList.remove('hidden');
          } else {
            card.style.display = 'none';
            card.classList.add('hidden');
          }
        });
      });
    });
  }

  /* ─── Semester Journey Mind-Map Lines ─── */
  var milestoneTabs = document.querySelectorAll('.sem-milestone-tab');
  var journeyPanels = document.querySelectorAll('.sem-journey-panel');

  // Function to draw SVG lines from folder to each outcome card
  function drawMindMapLines(semesterNum) {
    var mindmap = document.getElementById('semMindmap' + semesterNum);
    var subjectsCard = document.getElementById('semSubjects' + semesterNum);
    var folderCenter = document.getElementById('semFolder' + semesterNum);
    var outcomesCol = document.getElementById('semOutcomes' + semesterNum);
    var svgEl = document.getElementById('semLinesSvg' + semesterNum);
    if (!mindmap || !subjectsCard || !folderCenter || !outcomesCol || !svgEl) return;

    var mmRect = mindmap.getBoundingClientRect();
    svgEl.setAttribute('width', mmRect.width);
    svgEl.setAttribute('height', mmRect.height);

    var sRect = subjectsCard.getBoundingClientRect();
    var fRect = folderCenter.getBoundingClientRect();
    var oRect = outcomesCol.getBoundingClientRect();

    // Coordinates relative to mindmap container
    var subjectsRight = sRect.right - mmRect.left;
    var subjectsCenterY = sRect.top + sRect.height / 2 - mmRect.top;
    var folderLeft = fRect.left - mmRect.left;
    var folderRight = fRect.right - mmRect.left;
    var folderCenterY = fRect.top + fRect.height / 2 - mmRect.top;
    var outcomesLeft = oRect.left - mmRect.left;

    // Draw main line: Subjects card → Folder (curved)
    var mainLine = document.getElementById('semMainLine' + semesterNum);
    if (mainLine) {
      var mx1 = subjectsRight + 4;
      var my1 = subjectsCenterY;
      var mx2 = folderLeft - 4;
      var my2 = folderCenterY;
      var mcx = (mx1 + mx2) / 2;
      mainLine.setAttribute('d', 'M ' + mx1 + ' ' + my1 + ' C ' + mcx + ' ' + my1 + ', ' + mcx + ' ' + my2 + ', ' + mx2 + ' ' + my2);
      var mainLen = mainLine.getTotalLength();
      mainLine.style.strokeDasharray = mainLen;
      mainLine.style.strokeDashoffset = mainLen;
    }

    // Draw branch lines: Folder → Each Outcome Card
    var outcomeCards = outcomesCol.querySelectorAll('.sem-outcome-card');
    outcomeCards.forEach(function (card, idx) {
      var branchPath = document.getElementById('semBranch' + semesterNum + '_' + idx);
      var dot = document.getElementById('semDot' + semesterNum + '_' + idx);
      if (!branchPath) return;

      var cRect = card.getBoundingClientRect();
      var cy = cRect.top + cRect.height / 2 - mmRect.top;
      var cx = outcomesLeft - 4;

      // Cubic bezier from folder right to card left with organic curves
      var fx = folderRight + 4;
      var fy = folderCenterY;
      var cpOffset1 = (cx - fx) * 0.4;
      var cpOffset2 = (cx - fx) * 0.55;
      var d = 'M ' + fx + ' ' + fy + ' C ' + (fx + cpOffset1) + ' ' + fy + ', ' + (cx - cpOffset2) + ' ' + cy + ', ' + cx + ' ' + cy;
      branchPath.setAttribute('d', d);

      var bLen = branchPath.getTotalLength();
      branchPath.style.strokeDasharray = bLen;
      branchPath.style.strokeDashoffset = bLen;
      branchPath.style.animationDelay = (0.6 + idx * 0.13) + 's';

      // Position dot at end of line
      if (dot) {
        dot.setAttribute('cx', cx);
        dot.setAttribute('cy', cy);
        dot.style.animationDelay = (0.9 + idx * 0.13) + 's';
      }

      // Stagger outcome card appearance
      card.style.transitionDelay = (0.5 + idx * 0.1) + 's';
    });
  }

  // Function to trigger line drawing animation
  function animateLines(semesterNum) {
    var mainLine = document.getElementById('semMainLine' + semesterNum);
    if (mainLine) {
      var len = mainLine.getTotalLength();
      mainLine.style.transition = 'none';
      mainLine.style.strokeDashoffset = len;
      requestAnimationFrame(function () {
        mainLine.style.transition = 'stroke-dashoffset 0.9s cubic-bezier(0.4, 0, 0.2, 1) 0.3s';
        mainLine.style.strokeDashoffset = '0';
      });
    }

    var branchLines = document.querySelectorAll('[id^="semBranch' + semesterNum + '_"]');
    branchLines.forEach(function (line, idx) {
      var len = line.getTotalLength();
      line.style.transition = 'none';
      line.style.strokeDashoffset = len;
      line.style.opacity = '0';
      requestAnimationFrame(function () {
        line.style.transition = 'stroke-dashoffset 0.7s cubic-bezier(0.4, 0, 0.2, 1) ' + (0.6 + idx * 0.13) + 's, opacity 0.2s ease ' + (0.5 + idx * 0.13) + 's';
        line.style.strokeDashoffset = '0';
        line.style.opacity = '1';
      });
    });

    var dots = document.querySelectorAll('[id^="semDot' + semesterNum + '_"]');
    dots.forEach(function (dot, idx) {
      dot.style.transition = 'none';
      dot.setAttribute('opacity', '0');
      requestAnimationFrame(function () {
        dot.style.transition = 'opacity 0.4s ease ' + (1.0 + idx * 0.13) + 's';
        dot.setAttribute('opacity', '0.6');
      });
    });
  }

  if (milestoneTabs.length && journeyPanels.length) {
    milestoneTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var semesterNum = this.getAttribute('data-semester');

        milestoneTabs.forEach(function (t) { t.classList.remove('active'); });
        this.classList.add('active');

        journeyPanels.forEach(function (panel) { panel.classList.remove('active'); });

        setTimeout(function () {
          var targetPanel = document.getElementById('semPanel' + semesterNum);
          if (targetPanel) {
            targetPanel.classList.add('active');

            // Re-trigger title animation
            var title = targetPanel.querySelector('.sem-panel-title');
            if (title) {
              title.style.animation = 'none';
              title.offsetHeight;
              title.style.animation = 'panelTitleFade 0.5s ease forwards';
            }

            // Draw lines after DOM renders
            requestAnimationFrame(function () {
              setTimeout(function () {
                drawMindMapLines(semesterNum);
                animateLines(semesterNum);
              }, 50);
            });
          }
        }, 150);
      });
    });

    // Initialize first panel
    setTimeout(function () {
      var firstPanel = document.querySelector('.sem-journey-panel.active');
      if (firstPanel) {
        var semNum = firstPanel.getAttribute('data-semester');
        requestAnimationFrame(function () {
          setTimeout(function () {
            drawMindMapLines(semNum);
            animateLines(semNum);
          }, 150);
        });
      }
    }, 400);

    // Redraw on resize
    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        var activePanel = document.querySelector('.sem-journey-panel.active');
        if (activePanel) {
          drawMindMapLines(activePanel.getAttribute('data-semester'));
        }
      }, 200);
    });
  }

  /* ─── Contact Form Validation ─── */
  var contactForm = document.querySelector('#contact-form, form.contact-form');

  if (contactForm) {
    var alertError = contactForm.querySelector('.alert-error');

    function showError(message) {
      if (alertError) {
        alertError.textContent = message;
        alertError.style.display = '';
      }
    }

    function clearError() {
      if (alertError) {
        alertError.textContent = '';
        alertError.style.display = 'none';
      }
    }

    // Clear error on input
    contactForm.querySelectorAll('input, textarea').forEach(function (field) {
      field.addEventListener('input', clearError);
    });

    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      clearError();

      var nameField = contactForm.querySelector('[name="name"], input[name="name"]');
      var emailField = contactForm.querySelector('[name="email"], input[name="email"]');
      var messageField = contactForm.querySelector('[name="message"], textarea[name="message"]');

      // Validate name
      if (!nameField || nameField.value.trim() === '') {
        showError('Name is required.');
        if (nameField) nameField.focus();
        return;
      }

      // Validate email
      if (!emailField || emailField.value.trim() === '') {
        showError('Email is required.');
        if (emailField) emailField.focus();
        return;
      }

      var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(emailField.value.trim())) {
        showError('Please enter a valid email address.');
        if (emailField) emailField.focus();
        return;
      }

      // Validate message
      if (!messageField || messageField.value.trim().length < 10) {
        showError('Message must be at least 10 characters long.');
        if (messageField) messageField.focus();
        return;
      }

      // All valid — submit the form
      contactForm.submit();
    });
  }
})();

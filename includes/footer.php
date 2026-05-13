<?php
// ============================================
// CST Department - Footer
// ============================================

// Ensure settings are available
if (!defined('SETTINGS')) {
    getSettings();
}
$settings = SETTINGS;

// Fetch active sponsors
$sponsors = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM sponsors WHERE status = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $sponsors = $stmt->fetchAll();
} catch (Exception $e) {
    // Table may not exist yet
}

// Fetch active credits
$credits = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM credits WHERE status = 1 ORDER BY section ASC, sort_order ASC");
    $stmt->execute();
    $credits = $stmt->fetchAll();
} catch (Exception $e) {
    // Table may not exist yet
}

// Group credits by section
$creditsBySection = [];
if (!empty($credits)) {
    foreach ($credits as $credit) {
        $section = !empty($credit['section']) ? $credit['section'] : 'General';
        $creditsBySection[$section][] = $credit;
    }
}
?>

    <!-- ============================================
         CTA Section
         ============================================ -->
    <section class="cta-section" style="background-color: #2563EB; color: #fff; padding: 64px 0; text-align: center;">
        <div class="container" style="position: relative; z-index: 2;">
            <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 12px; color: #fff;">
                অন্বেষণ করতে প্রস্তুত?
            </h2>
            <p style="font-size: 1.1rem; opacity: 0.9; max-width: 560px; margin: 0 auto 32px; color: #fff; line-height: 1.6;">
                আমাদের প্রোগ্রাম, আমাদের শিক্ষকমণ্ডলীর সাথে পরিচিত হন এবং কম্পিউটার সায়েন্স অ্যান্ড টেকনোলজিতে সফল হওয়ার জন্য প্রয়োজনীয় রিসোর্স খুঁজে নিন।
            </p>
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= SITE_URL ?>/notice.php" class="btn btn-primary" style="background: #fff; color: #2563EB; border: 2px solid #fff; padding: 12px 28px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                    <?= icon('bell', 16) ?> নোটিশ দেখুন
                </a>
                <a href="<?= SITE_URL ?>/contact.php" class="btn btn-secondary" style="background: transparent; color: #fff; border: 2px solid #fff; padding: 12px 28px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                    <?= icon('send', 16) ?> যোগাযোগ করুন
                </a>
            </div>
        </div>
        <!-- Lottie Animation Placeholder -->
        <div class="lottie-cta" data-animation-url="" style="position: absolute; top: 50%; right: 5%; transform: translateY(-50%); width: 220px; height: 220px; opacity: 0.25; pointer-events: none;"></div>
    </section>

    <!-- ============================================
         Sponsored By Section
         ============================================ -->
    <?php if (!empty($sponsors)): ?>
    <section class="sponsors-section" style="background-color: #F8FAFC; padding: 48px 0;">
        <div class="container">
            <h2 style="text-align: center; font-size: 1.5rem; font-weight: 700; margin-bottom: 32px; color: #1F2937;">
                <?= sanitize($settings['sponsored_title'] ?? 'স্পন্সর') ?>
            </h2>
            <div class="sponsors-row" style="display: flex; align-items: center; justify-content: center; gap: 48px; flex-wrap: wrap;">
                <?php foreach ($sponsors as $sponsor): ?>
                    <?php if (!empty($sponsor['url'])): ?>
                        <a href="<?= sanitize($sponsor['url']) ?>" target="_blank" rel="noopener" class="sponsor-item" style="display: inline-block; max-height: 60px; filter: grayscale(100%); opacity: 0.7; transition: all 0.3s ease; text-decoration: none;">
                            <img src="<?= UPLOAD_URL . sanitize($sponsor['logo']) ?>" alt="<?= sanitize($sponsor['name']) ?>" style="max-height: 60px; width: auto; object-fit: contain;">
                        </a>
                    <?php else: ?>
                        <span class="sponsor-item" style="display: inline-block; max-height: 60px; filter: grayscale(100%); opacity: 0.7; transition: all 0.3s ease;">
                            <img src="<?= UPLOAD_URL . sanitize($sponsor['logo']) ?>" alt="<?= sanitize($sponsor['name']) ?>" style="max-height: 60px; width: auto; object-fit: contain;">
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============================================
         Credits & Acknowledgements Section
         ============================================ -->
    <?php if (!empty($creditsBySection)): ?>
    <section class="credits-section" style="background-color: #FFFFFF; padding: 48px 0;">
        <div class="container">
            <h2 style="text-align: center; font-size: 1.5rem; font-weight: 700; margin-bottom: 40px; color: #1F2937;">
                <?= sanitize($settings['credits_title'] ?? 'কৃতজ্ঞতা ও স্বীকৃতি') ?>
            </h2>

            <?php foreach ($creditsBySection as $sectionName => $sectionCredits): ?>
                <?php if (count($creditsBySection) > 1): ?>
                    <h3 style="font-size: 1.1rem; font-weight: 600; color: #4B5563; margin-bottom: 16px; margin-top: 32px; padding-bottom: 8px; border-bottom: 2px solid #E5E7EB;">
                        <?= sanitize($sectionName) ?>
                    </h3>
                <?php endif; ?>
                <div class="credits-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px;">
                    <?php foreach ($sectionCredits as $credit): ?>
                        <div class="credit-card" style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 10px; padding: 20px; display: flex; align-items: center; gap: 14px; transition: box-shadow 0.2s;">
                            <?php if (!empty($credit['icon'])): ?>
                                <div class="credit-icon" style="flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; background: #EFF6FF; display: flex; align-items: center; justify-content: center; color: #2563EB;">
                                    <img src="<?= UPLOAD_URL . sanitize($credit['icon']) ?>" alt="" style="width: 28px; height: 28px; object-fit: contain; border-radius: 50%;">
                                </div>
                            <?php else: ?>
                                <div class="credit-icon" style="flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; background: #EFF6FF; display: flex; align-items: center; justify-content: center; color: #2563EB;">
                                    <?= icon('award', 22) ?>
                                </div>
                            <?php endif; ?>
                            <div class="credit-info" style="min-width: 0;">
                                <?php if (!empty($credit['url'])): ?>
                                    <a href="<?= sanitize($credit['url']) ?>" target="_blank" rel="noopener" class="credit-name" style="font-weight: 600; color: #1F2937; text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= sanitize($credit['name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="credit-name" style="font-weight: 600; color: #1F2937; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= sanitize($credit['name']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($credit['role'])): ?>
                                    <span class="credit-role" style="font-size: 0.875rem; color: #6B7280; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= sanitize($credit['role']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($credit['url'])): ?>
                                <a href="<?= sanitize($credit['url']) ?>" target="_blank" rel="noopener" style="flex-shrink: 0; color: #9CA3AF; transition: color 0.2s; margin-left: auto;" aria-label="Visit external link">
                                    <?= icon('link', 16) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============================================
         Main Footer
         ============================================ -->
    <footer class="footer" style="background-color: #1F2937; color: #D1D5DB; padding: 64px 0 0;">
        <div class="container">
            <div class="footer-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px;">

                <!-- Column 1: Brand -->
                <div class="footer-col footer-brand-col">
                    <a href="<?= SITE_URL ?>/" class="footer-logo" style="display: flex; align-items: center; gap: 10px; text-decoration: none; margin-bottom: 16px;">
                        <?php if (($logo = siteLogo())): ?>
                            <img src="<?= sanitize($logo) ?>" alt="<?= sanitize(siteName()) ?>" style="height: 40px; width: auto; object-fit: contain;">
                        <?php else: ?>
                            <span style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: #2563EB; border-radius: 8px; color: #fff;">
                                <?= icon('code', 22) ?>
                            </span>
                        <?php endif; ?>
                        <span style="font-size: 1.25rem; font-weight: 700; color: #F9FAFB;"><?= sanitize(siteName()) ?></span>
                    </a>
                    <?php if (($tagline = siteTagline())): ?>
                        <p style="font-size: 0.9rem; color: #9CA3AF; line-height: 1.6; margin-bottom: 20px;">
                            <?= sanitize($tagline) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Social Media Links -->
                    <div class="footer-social" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if (!empty($settings['facebook'])): ?>
                            <a href="<?= sanitize($settings['facebook']) ?>" target="_blank" rel="noopener" aria-label="Facebook" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #D1D5DB; text-decoration: none; transition: all 0.2s;">
                                <?= icon('facebook', 18) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['youtube'])): ?>
                            <a href="<?= sanitize($settings['youtube']) ?>" target="_blank" rel="noopener" aria-label="YouTube" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #D1D5DB; text-decoration: none; transition: all 0.2s;">
                                <?= icon('youtube', 18) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['linkedin'])): ?>
                            <a href="<?= sanitize($settings['linkedin']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #D1D5DB; text-decoration: none; transition: all 0.2s;">
                                <?= icon('linkedin', 18) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['twitter'])): ?>
                            <a href="<?= sanitize($settings['twitter']) ?>" target="_blank" rel="noopener" aria-label="Twitter" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #D1D5DB; text-decoration: none; transition: all 0.2s;">
                                <?= icon('twitter', 18) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['instagram'])): ?>
                            <a href="<?= sanitize($settings['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #D1D5DB; text-decoration: none; transition: all 0.2s;">
                                <?= icon('instagram', 18) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="footer-col">
                    <h4 style="font-size: 1rem; font-weight: 600; color: #F9FAFB; margin-bottom: 20px;">দ্রুত লিংক</h4>
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('home', 14) ?> হোম
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/about.php" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('info', 14) ?> সম্পর্কে
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/faculty.php" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('users', 14) ?> শিক্ষকমণ্ডলী
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/notice.php" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('bell', 14) ?> নোটিশ
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/resources.php" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('book', 14) ?> রিসোর্স
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/gallery.php" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('image', 14) ?> গ্যালারি
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/contact.php" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('mail', 14) ?> যোগাযোগ
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 3: Resources -->
                <div class="footer-col">
                    <h4 style="font-size: 1rem; font-weight: 600; color: #F9FAFB; margin-bottom: 20px;">রিসোর্স</h4>
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/resources.php?type=study_materials" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('file-text', 14) ?> অধ্যয়ন সামগ্রী
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/resources.php?type=previous_questions" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('file-text', 14) ?> বিগত প্রশ্ন
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/resources.php?type=syllabus" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('file-text', 14) ?> সিলেবাস
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/resources.php?type=class_routine" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('calendar', 14) ?> ক্লাস রুটিন
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="<?= SITE_URL ?>/resources.php?type=lab_manuals" style="color: #9CA3AF; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;">
                                <?= icon('monitor', 14) ?> ল্যাব ম্যানুয়াল
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 4: Contact Info -->
                <div class="footer-col">
                    <h4 style="font-size: 1rem; font-weight: 600; color: #F9FAFB; margin-bottom: 20px;">যোগাযোগের তথ্য</h4>

                    <?php if (!empty($settings['phone'])): ?>
                    <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px;">
                        <span style="flex-shrink: 0; color: #2563EB; margin-top: 2px;">
                            <?= icon('phone', 16) ?>
                        </span>
                        <div>
                            <span style="font-size: 0.8rem; color: #6B7280; display: block; margin-bottom: 2px;">ফোন</span>
                            <a href="tel:<?= sanitize($settings['phone']) ?>" style="color: #D1D5DB; text-decoration: none; font-size: 0.9rem; transition: color 0.2s;">
                                <?= sanitize($settings['phone']) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($settings['email'])): ?>
                    <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px;">
                        <span style="flex-shrink: 0; color: #2563EB; margin-top: 2px;">
                            <?= icon('mail', 16) ?>
                        </span>
                        <div>
                            <span style="font-size: 0.8rem; color: #6B7280; display: block; margin-bottom: 2px;">ইমেইল</span>
                            <a href="mailto:<?= sanitize($settings['email']) ?>" style="color: #D1D5DB; text-decoration: none; font-size: 0.9rem; transition: color 0.2s;">
                                <?= sanitize($settings['email']) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($settings['address'])): ?>
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <span style="flex-shrink: 0; color: #2563EB; margin-top: 2px;">
                            <?= icon('map-pin', 16) ?>
                        </span>
                        <div>
                            <span style="font-size: 0.8rem; color: #6B7280; display: block; margin-bottom: 2px;">ঠিকানা</span>
                            <p style="color: #D1D5DB; font-size: 0.9rem; line-height: 1.5; margin: 0;">
                                <?= sanitize($settings['address']) ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="footer-bottom" style="border-top: 1px solid rgba(255,255,255,0.1); padding: 20px 0; margin-top: 48px; text-align: center;">
                <p style="font-size: 0.85rem; color: #6B7280; margin: 0;">
                    &copy; <?= date('Y') ?> <?= sanitize(!empty($settings['copyright_text']) ? $settings['copyright_text'] : siteName()) ?>. সর্বস্বত্ব সংরক্ষিত।
                </p>
            </div>
        </div>
    </footer>

    <!-- ============================================
         Lightbox (Gallery)
         ============================================ -->
    <div class="lightbox" id="lightbox" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.92); align-items: center; justify-content: center;" role="dialog" aria-modal="true" aria-label="Image lightbox">
        <!-- Close Button -->
        <button class="lightbox-close" id="lightboxClose" aria-label="Close lightbox" style="position: absolute; top: 16px; right: 16px; background: none; border: none; color: #fff; font-size: 2rem; cursor: pointer; z-index: 10; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: background 0.2s;">
            <?= icon('x', 28) ?>
        </button>
        <!-- Previous Navigation -->
        <button class="lightbox-nav lightbox-prev" id="lightboxPrev" aria-label="Previous image" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: none; color: #fff; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
            <?= icon('chevron-left', 24) ?>
        </button>
        <!-- Next Navigation -->
        <button class="lightbox-nav lightbox-next" id="lightboxNext" aria-label="Next image" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: none; color: #fff; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
            <?= icon('chevron-right', 24) ?>
        </button>
        <!-- Image Display -->
        <img class="lightbox-image" id="lightboxImage" src="" alt="" style="max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 4px;">
        <!-- Caption -->
        <p class="lightbox-caption" id="lightboxCaption" style="position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 0.95rem; text-align: center; max-width: 80vw; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></p>
    </div>

    <!-- ============================================
         Scroll to Top Button
         ============================================ -->
    <button class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top" style="display: none; position: fixed; bottom: 24px; right: 24px; z-index: 9998; width: 44px; height: 44px; border-radius: 50%; background: #2563EB; color: #fff; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(37,99,235,0.3); align-items: center; justify-content: center; transition: all 0.3s;">
        <?= icon('arrow-up', 20) ?>
    </button>

    <!-- ============================================
         Scripts
         ============================================ -->
    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>

</body>
</html>

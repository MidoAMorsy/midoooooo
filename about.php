<?php
/**
 * Ahmed Ashraf Portfolio - About Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();

// SEO Options
$seoOptions = [
    'title' => __('about_me'),
    'description' => $lang === 'ar'
        ? 'تعرف على أحمد أشرف - محترف تسويق بخلفية تقنية قوية مع 6+ سنوات خبرة'
        : 'Learn about Ahmed Ashraf - Marketing professional with strong IT background and 6+ years experience',
];

// Work Experience
$experiences = [
    [
        'title_en' => 'Technical Support Engineer',
        'title_ar' => 'مهندس دعم تقني',
        'company_en' => 'P.B.A',
        'company_ar' => 'P.B.A',
        'period' => 'Aug 2020 - Present',
        'period_ar' => 'أغسطس 2020 - حتى الآن',
        'description_en' => 'Providing technical support and IT solutions for business operations.',
        'description_ar' => 'تقديم الدعم التقني وحلول تقنية المعلومات لعمليات الأعمال.',
    ],
    [
        'title_en' => 'IT Operation Manager',
        'title_ar' => 'مدير عمليات تقنية المعلومات',
        'company_en' => 'Mediance Training Academy',
        'company_ar' => 'أكاديمية ميديانس للتدريب',
        'period' => 'Aug 2019 - May 2020',
        'period_ar' => 'أغسطس 2019 - مايو 2020',
        'description_en' => 'Managed IT operations and team coordination for training programs.',
        'description_ar' => 'إدارة عمليات تقنية المعلومات وتنسيق الفريق لبرامج التدريب.',
    ],
    [
        'title_en' => 'IT Engineer',
        'title_ar' => 'مهندس تقنية معلومات',
        'company_en' => 'GMS For Medical Training',
        'company_ar' => 'GMS للتدريب الطبي',
        'period' => 'Dec 2018 - Sept 2019',
        'period_ar' => 'ديسمبر 2018 - سبتمبر 2019',
        'description_en' => 'IT infrastructure management and technical support.',
        'description_ar' => 'إدارة البنية التحتية لتقنية المعلومات والدعم الفني.',
    ],
    [
        'title_en' => 'IT Engineer',
        'title_ar' => 'مهندس تقنية معلومات',
        'company_en' => 'SPC Training Academy',
        'company_ar' => 'أكاديمية SPC للتدريب',
        'period' => 'Dec 2017 - Dec 2018',
        'period_ar' => 'ديسمبر 2017 - ديسمبر 2018',
        'description_en' => 'Technical support and network administration.',
        'description_ar' => 'الدعم الفني وإدارة الشبكات.',
    ],
    [
        'title_en' => 'Call Center Agent',
        'title_ar' => 'موظف خدمة عملاء',
        'company_en' => 'Etisalat Misr',
        'company_ar' => 'اتصالات مصر',
        'period' => 'Dec 2016 - Dec 2017',
        'period_ar' => 'ديسمبر 2016 - ديسمبر 2017',
        'description_en' => 'Customer service and technical support for telecommunications.',
        'description_ar' => 'خدمة العملاء والدعم الفني للاتصالات.',
    ],
];

// Skills
$skills = [
    ['name_en' => 'Digital Marketing', 'name_ar' => 'التسويق الرقمي', 'level' => 90, 'icon' => 'fas fa-bullseye'],
    ['name_en' => 'Social Media', 'name_ar' => 'وسائل التواصل', 'level' => 85, 'icon' => 'fas fa-share-alt'],
    ['name_en' => 'Content Creation', 'name_ar' => 'إنشاء المحتوى', 'level' => 80, 'icon' => 'fas fa-pen-fancy'],
    ['name_en' => 'SEO/SEM', 'name_ar' => 'تحسين محركات البحث', 'level' => 75, 'icon' => 'fas fa-search'],
    ['name_en' => 'Network Admin', 'name_ar' => 'إدارة الشبكات', 'level' => 85, 'icon' => 'fas fa-network-wired'],
    ['name_en' => 'Technical Support', 'name_ar' => 'الدعم الفني', 'level' => 90, 'icon' => 'fas fa-headset'],
    ['name_en' => 'Microsoft Office', 'name_ar' => 'مايكروسوفت أوفيس', 'level' => 95, 'icon' => 'fab fa-microsoft'],
    ['name_en' => 'Leadership', 'name_ar' => 'القيادة', 'level' => 80, 'icon' => 'fas fa-users'],
];

// Certificates
$certificates = [
    ['name' => 'CCNA Basics', 'icon' => 'fas fa-network-wired'],
    ['name' => 'Kali Linux', 'icon' => 'fab fa-linux'],
    ['name' => 'E-Marketing', 'icon' => 'fas fa-bullhorn'],
    ['name' => 'Photoshop', 'icon' => 'fas fa-image'],
    ['name' => 'Ethical Hacking Basics', 'icon' => 'fas fa-user-secret'],
    ['name' => 'PHP Basics', 'icon' => 'fab fa-php'],
    ['name' => 'C++ Basics', 'icon' => 'fas fa-code'],
    ['name' => 'WordPress Development', 'icon' => 'fab fa-wordpress'],
];

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="min-height:50vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <h1 class="hero-title"><?php echo __('about_me'); ?></h1>
            <p class="hero-subtitle" style="max-width:600px;margin:0 auto;">
                <?php echo $lang === 'ar'
                    ? 'محترف تسويق شغوف بخلفية تقنية قوية، أجمع بين الإبداع والتقنية لتحقيق نتائج استثنائية'
                    : 'A passionate marketing professional with a strong technical background, combining creativity and technology for exceptional results'; ?>
            </p>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([['title' => __('about'), 'url' => '']]); ?>
</div>

<!-- About Content -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="gap:4rem;align-items:center;">
            <!-- Image -->
            <div data-aos="fade-right">
                <div style="position:relative;">
                    <img src="<?php echo getUploadedUrl(getSetting('profile_picture'), 'profile', 'profile-placeholder.jpg'); ?>"
                        alt="Ahmed Ashraf" style="border-radius:1.5rem;box-shadow:var(--shadow-xl);"
                        onerror="this.src='https://ui-avatars.com/api/?name=Ahmed+Ashraf&size=600&background=0066cc&color=fff&bold=true'">
                    <div
                        style="position:absolute;bottom:-20px;<?php echo $lang === 'ar' ? 'left' : 'right'; ?>:-20px;background:var(--gradient-primary);padding:1.5rem 2rem;border-radius:1rem;color:#fff;box-shadow:var(--shadow-lg);">
                        <div style="font-size:2.5rem;font-weight:800;">6+</div>
                        <div><?php echo __('years_experience'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div data-aos="fade-left">
                <h2><?php echo __('my_story'); ?></h2>
                <p style="font-size:1.1rem;margin-bottom:1.5rem;">
                    <?php echo $lang === 'ar'
                        ? 'أنا أحمد أشرف، خريج كلية الحقوق جامعة عين شمس (2016-2020)، لكن شغفي الحقيقي كان دائماً في مجال التكنولوجيا والتسويق الرقمي.'
                        : 'I am Ahmed Ashraf, a graduate of the Faculty of Law at Ain Shams University (2016-2020), but my true passion has always been in technology and digital marketing.'; ?>
                </p>
                <p style="margin-bottom:1.5rem;">
                    <?php echo $lang === 'ar'
                        ? 'بدأت مسيرتي المهنية في مجال تقنية المعلومات منذ أكثر من 6 سنوات، حيث عملت في عدة شركات ومؤسسات تدريبية، مما أكسبني خبرة واسعة في الدعم الفني وإدارة الشبكات وقيادة الفرق.'
                        : 'I started my career in IT over 6 years ago, working in various companies and training institutions, gaining extensive experience in technical support, network administration, and team leadership.'; ?>
                </p>
                <p style="margin-bottom:2rem;">
                    <?php echo $lang === 'ar'
                        ? 'اليوم، أركز على التسويق الرقمي مع الاستفادة من خلفيتي التقنية لتقديم حلول متكاملة تجمع بين الإبداع والتقنية.'
                        : 'Today, I focus on digital marketing while leveraging my technical background to deliver comprehensive solutions that combine creativity and technology.'; ?>
                </p>

                <!-- Info Cards -->
                <div class="grid grid-2" style="gap:1rem;">
                    <div style="padding:1rem;background:var(--gray-50);border-radius:0.75rem;">
                        <i class="fas fa-graduation-cap text-primary"
                            style="font-size:1.5rem;margin-bottom:0.5rem;"></i>
                        <h5 style="font-size:1rem;margin-bottom:0.25rem;"><?php echo __('education'); ?></h5>
                        <p style="margin:0;font-size:0.9rem;color:var(--gray-600);">
                            <?php echo $lang === 'ar' ? 'كلية الحقوق - جامعة عين شمس' : 'Faculty of Law - Ain Shams University'; ?>
                        </p>
                    </div>
                    <div style="padding:1rem;background:var(--gray-50);border-radius:0.75rem;">
                        <i class="fas fa-map-marker-alt text-primary"
                            style="font-size:1.5rem;margin-bottom:0.5rem;"></i>
                        <h5 style="font-size:1rem;margin-bottom:0.25rem;"><?php echo __('location'); ?></h5>
                        <p style="margin:0;font-size:0.9rem;color:var(--gray-600);">
                            <?php echo $lang === 'ar' ? 'القاهرة، مصر' : 'Cairo, Egypt'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Skills Section -->
<section class="section bg-gray-50">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo __('skills'); ?></h2>
            <p><?php echo $lang === 'ar'
                ? 'مهاراتي في التسويق والتقنية'
                : 'My skills in marketing and technology'; ?>
            </p>
        </div>

        <div class="grid grid-4">
            <?php foreach ($skills as $index => $skill): ?>
                <div class="card" style="padding:2rem;text-align:center;" data-aos="fade-up"
                    data-aos-delay="<?php echo $index * 50; ?>">
                    <div
                        style="width:80px;height:80px;margin:0 auto 1rem;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="<?php echo $skill['icon']; ?>" style="font-size:2rem;color:#fff;"></i>
                    </div>
                    <h4 style="font-size:1.1rem;margin-bottom:1rem;">
                        <?php echo $lang === 'ar' ? $skill['name_ar'] : $skill['name_en']; ?>
                    </h4>
                    <div style="height:8px;background:var(--gray-200);border-radius:4px;overflow:hidden;">
                        <div
                            style="width:<?php echo $skill['level']; ?>%;height:100%;background:var(--gradient-primary);border-radius:4px;">
                        </div>
                    </div>
                    <span
                        style="font-size:0.9rem;color:var(--gray-500);margin-top:0.5rem;display:block;"><?php echo $skill['level']; ?>%</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Experience Timeline -->
<section class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo __('career_journey'); ?></h2>
            <p><?php echo $lang === 'ar'
                ? 'مسيرتي المهنية عبر السنين'
                : 'My professional journey through the years'; ?>
            </p>
        </div>

        <div style="max-width:800px;margin:0 auto;">
            <?php foreach ($experiences as $index => $exp): ?>
                <div class="card"
                    style="padding:2rem;margin-bottom:1.5rem;position:relative;<?php echo $lang === 'ar' ? 'border-right' : 'border-left'; ?>:4px solid var(--primary);"
                    data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div
                        style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
                        <div>
                            <h3 style="font-size:1.25rem;margin-bottom:0.25rem;">
                                <?php echo $lang === 'ar' ? $exp['title_ar'] : $exp['title_en']; ?>
                            </h3>
                            <p style="margin:0;color:var(--primary);font-weight:600;">
                                <?php echo $lang === 'ar' ? $exp['company_ar'] : $exp['company_en']; ?>
                            </p>
                        </div>
                        <span class="tag" style="background:var(--primary);color:#fff;">
                            <i class="far fa-calendar"></i>
                            <?php echo $lang === 'ar' ? $exp['period_ar'] : $exp['period']; ?>
                        </span>
                    </div>
                    <p style="margin:0;color:var(--gray-600);">
                        <?php echo $lang === 'ar' ? $exp['description_ar'] : $exp['description_en']; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Certificates Section -->
<section class="section bg-gradient" style="color:#fff;">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 style="color:#fff;"><?php echo __('certificates'); ?></h2>
            <p style="color:rgba(255,255,255,0.8);"><?php echo $lang === 'ar'
                ? 'الشهادات والدورات التي حصلت عليها'
                : 'Certifications and courses I have obtained'; ?>
            </p>
        </div>

        <div class="grid grid-4">
            <?php foreach ($certificates as $index => $cert): ?>
                <div style="background:rgba(255,255,255,0.1);padding:2rem;border-radius:1rem;text-align:center;"
                    data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                    <div
                        style="width:60px;height:60px;margin:0 auto 1rem;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="<?php echo $cert['icon']; ?>" style="font-size:1.5rem;"></i>
                    </div>
                    <h5 style="margin:0;font-size:1rem;"><?php echo $cert['name']; ?></h5>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Download CV -->
<section class="section">
    <div class="container text-center">
        <div data-aos="fade-up">
            <h2><?php echo $lang === 'ar' ? 'هل تريد معرفة المزيد?' : 'Want to know more?'; ?></h2>
            <p style="max-width:600px;margin:0 auto 2rem;">
                <?php echo $lang === 'ar'
                    ? 'قم بتحميل سيرتي الذاتية الكاملة للاطلاع على المزيد من التفاصيل عن خبراتي ومهاراتي.'
                    : 'Download my full CV to learn more about my experience and skills.'; ?>
            </p>
            <a href="<?php echo getUploadedUrl(getSetting('cv_file'), 'cv', '#'); ?>" class="btn btn-primary btn-lg"
                download>
                <i class="fas fa-download"></i>
                <?php echo __('download_cv'); ?>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<footer <?= isset($home) && $home ? ' class="home-footer"' : ''; ?>>
    <?php if (isset($footer['banners']) && count($footer['banners'])) : ?>
        <div class="container-fluid banners-wrapper">
            <?php foreach ($footer['banners'] as $banner) : ?>
                <a href="<?= $this->e($url($banner->url)); ?>" title="<?= $this->e($banner->title); ?>">
                    <img src="<?= $this->e($url('upload/' . $banner->image, [ 'w' => 265, 'h' => 141 ])); ?>" alt="<?= $this->e($banner->title); ?>" />
                </a>
            <?Php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="container-fluid contacts-wrapper">
        <div class="row">
            <div class="col-md-4 col-lg address">
                <p><?= $this->e($intl('home.quick.address')); ?></p>
                <?= $content($footer['footer']['address'] ?? null); ?>
            </div>
            <div class="col-md-4 col-lg phones">
                <p><?= $this->e($intl('home.quick.phone')); ?></p>
                <?= $content($footer['footer']['phone'] ?? null); ?>
            </div>
            <div class="col-md-4 col-lg fax">
                <p><?= $this->e($intl('home.quick.fax')); ?></p>
                <?= $content($footer['footer']['fax'] ?? null); ?>
            </div>
            <div class="col-md-4 col-lg mail">
                <p><?= $this->e($intl('home.quick.mail')); ?></p>
                <?= $content($footer['footer']['mail'] ?? null); ?>
            </div>
            <div class="col-md-4 col-lg social">
                <p><?= $this->e($intl('home.quick.social')); ?></p>
                <?= $content($footer['footer']['social'] ?? null); ?>
            </div>
        </div>
        <a href="#" class="to-top-wrapper"><p class="to-top"><?= $this->e($intl('home.totop')); ?></p></a>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <p class="links-inner">
                <a href="https://portal.justice.bg" target="_blank"><?= $this->e($intl('footer.portal')); ?></a>
                <a class="middle-link" href="<?= $this->e($url($langCode . '/cookiepolicy')); ?>"><?= $this->e($intl('footer.terms')); ?></a>
                <a href="<?= $this->e($url($langCode . '/dataprotection')); ?>"><?= $this->e($intl('footer.personal')); ?></a>
            </p>
        </div>
        <div class="row">
            <p class="common-info"><?= $this->e($intl('footer.info')); ?></p>
        </div>
    </div>
    <div class="container logos-wrapper">
        <div class="row">
            <div class="col-lg ">
                <a href="#" class="eu"></a>
            </div>
            <div class="col-lg">
                <a href="#" class="opdu"></a>
            </div>
        </div>
        <div class="row justify-content-center">
            <p class="justice-council">&copy; <?= $this->e(date('Y') . ' ' . $intl('footer.rights')); ?></p>
        </div>
    </div>
</footer>
<!DOCTYPE html>
<html lang="<?= $this->e($lang ?? $page->getLanguageCode()) ?>" class="<?= isset($_COOKIE['accessibility']) ? $this->e($_COOKIE['accessibility']) : '' ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="<?= $url('assets/js/dtpckr/dtpckr.js'); ?>"></script>
        <link rel="stylesheet" href="<?= $url('assets/styles/style.css'); ?>">
        <link rel="stylesheet" href="<?= $url('assets/js/dtpckr/dtpckr.css'); ?>">
        <title><?= $this->e($title ?? $page->title) ?></title>
        <?php if ($req->getQuery('print')) : ?>
            <style>
                @media all {
                    .menu-wrapper,
                    .accessability-wrapper,
                    .search-wrapper,
                    img,
                    .inner-print,
                    .to-top-wrapper,
                    .sub-gallery,
                    .sub-gallery-row  { display: none !important; }
                    .logo, footer { display: none !important; }
                }
                @media screen {
                    body { max-width: 21cm; margin: 2cm auto }
                    .logo { margin-top: 50px; padding: 0;  }
                }
            </style>
            <script>
                window.print();
            </script>
        <?php endif; ?>
        <?php if (isset($site['analytics_code']) && strlen($site['analytics_code']) && isset($_COOKIE['cookies']) && $_COOKIE['cookies'] == 1 && $req->getHeaderLine('DNT') != '1') :
            ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?= $this->e($site['analytics_code']); ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '<?= $this->e($site['analytics_code']); ?>');
            </script>
        <?php endif ?>
    </head>
    <body>
        <?= $this->section('content'); ?>
        <?php if (!isset($_COOKIE['cookies'])) : ?>
            <div id="cookies" data-expire="<?= date('r', time()+(3 * 30 * 24 * 60 * 60)); ?>">
                <p><?= $this->e($intl('cookies.message')); ?></p>
                <a href="#" class="btn btn-primary btn-accept"><?= $this->e($intl('cookies.btn.accept')); ?></a>
                <a href="#" class="btn btn-default btn-refuse"><?= $this->e($intl('cookies.btn.refuse')); ?></a>
                <a href="<?= $this->e($url(($lang ?? ($page->lang == 1 ? 'bg' : 'en')).'/cookiepolicy')); ?>" class="btn btn-default"><?= $this->e($intl('cookies.btn.more')); ?></a>
            </div>
        <?php endif ?>
        <script src="<?= $url('assets/js/script.js'); ?>"></script>
    </body>
</html>
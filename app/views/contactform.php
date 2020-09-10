<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container contact-form-wrapper">  
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
            </div>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <?php if ($isPost && count($errors)) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?= $this->e($intl($error)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6 person-info">
                    <label for="name"><?= $this->e($intl('contactform.name')); ?>*</label>
                    <input id="name" name="name" type="text" value="<?= $this->e($params['name'] ?? null); ?>">
                    <label for="phone"><?= $this->e($intl('contactform.phone')); ?>*</label>
                    <input id="phone" name="phone" type="text" value="<?= $this->e($params['phone'] ?? null); ?>">
                    <label for="email"><?= $this->e($intl('contactform.mail')); ?>*</label>
                    <input id="email" name="mail" type="email" value="<?= $this->e($params['mail'] ?? null); ?>">
                    <label for="addr"><?= $this->e($intl('contactform.addr')); ?></label>
                    <input id="addr" name="addr" type="checkbox"<?= isset($params['addr']) ? ' checked="checked"' : '' ;?>>
                    <label for="address"><?= $this->e($intl('contactform.address')); ?></label>
                    <input id="address" name="address" type="text" value="<?= $this->e($params['address'] ?? null); ?>">
                    <label for="consent"><?= $this->e($intl('contactform.consent')); ?>*</label>
                    <input id="consent" name="consent" type="checkbox"<?= isset($params['consent']) ? ' checked="checked"' : '' ;?>>
                    <p class="consent-info"><?= $this->e($intl('contactform.consent.text')); ?></p>
                    <input type="file" name="file" />
                    <p class="consent-info"><?= $this->e($intl('contactform.files.ext')); ?></p>
                </div>
                <div class="col-md-6">
                    <label for="message"><?= $this->e($intl('contactform.message')); ?>*</label>
                    <textarea name="message" id="message"><?= $this->e($params['message'] ?? null); ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 person-info">
                    <button type="submit"><?= $this->e($intl('contactform.submit')); ?></button>
                </div>
            </div>
            <script>
                $('[name=addr]').on('change', function () {
                    if ($(this).prop('checked')) {
                        $('[name=address]').show().prev('label').show();
                    }
                    else {
                        $('[name=address]').hide().prev('label').hide();
                    }
                }).change();
            </script>
        </form>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>
<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container contacts-container">
        <h2><?= $this->e($page->title); ?></h2>
        <?php if (isset($departments) && count($departments)) : ?>
            <?php foreach ($departments as $department) : ?>
                <div class="row contacts-item">
                    <h3><?= $this->e($department->name); ?></h3>
                    <div class="contacts-item-content col-md-12">
                        <?php if (count($department->hours)) : ?>
                            <p class="office-hours-title"><?= $this->e($intl('contacts.hours')); ?></p>
                            <ul>
                                <?php foreach ($department->hours as $hour) : ?>
                                    <li>
                                        <span class="period"><?= $this->e($hour[0] ?? null); ?></span>
                                        <span class="office-hours"><?= $this->e($hour[1] ?? null); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (strlen($department->description)) : ?>
                            <div>
                                <?= $content($department->description); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($employees[$department->department]) && count($employees[$department->department])) : ?>
                            <?php foreach ($employees[$department->department] as $row) : ?>
                                <div class="row contact-row">
                                    <?php foreach ($row as $employee) : ?>
                                        <div class="col-md-6 single-contact">
                                            <div class="contact-info">
                                                <div class="row">
                                                    <?php if ($employee->image) : ?>
                                                        <div class="col-md-5">
                                                            <img src="<?= $this->e($url('upload/' . $employee->image)); ?>" alt="">
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="col-md-7">
                                                        <p class="contact-name"><?= $this->e($employee->name ?? null); ?></p>
                                                        <p class="contact-position"><?= $this->e($employee->position ?? null); ?></p>
                                                        <p class="contact-address"><?= $this->e($employee->office ?? null); ?></p>
                                                        <p class="contact-phone"><?= $this->e($employee->phone ?? null); ?></p>
                                                        <?php if (isset($employee->mail) && strlen($employee->mail)) : ?>
                                                            <p class="contact-mail"><a href="mailto:<?= $this->e($employee->mail); ?>"><?= $this->e($employee->mail); ?></a></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <script>
                $('.contacts-item > h3').on('click', function () {
                    $(this).closest('.contacts-item').siblings().find('.contacts-item-content').slideUp();
                    $(this).closest('.contacts-item').find('.contacts-item-content').slideToggle();
                });
            </script>
        <?php endif; ?>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>

<?php if (CLI) : ?>
    <?php
        $search = [];
        $fields = [ 'name', 'position', 'office', 'mail', 'phone', 'description' ];
    foreach ($employees as $values) {
        foreach ($values as $value) {
            foreach ($value as $key => $employee) {
                if (is_object($employee)) {
                    $employee = (array) $employee;
                }
                foreach ($employee as $k => $v) {
                    if (in_array($k, $fields) && strlen($v)) {
                            $search[] = $v;
                    }
                }
            }
        }
    }
        $search = implode(', ', $search);
    ?>
    <script type="index/json">
    <?= json_encode([
    'title'   => htmlspecialchars_decode(strip_tags($page->title)),
    'data'    => $search ?? null,
    'module'  => 'contacts',
    'id'      => $page->id
]) ?>
</script>
<?php endif ?>
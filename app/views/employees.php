<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container employees-container">
        <h2><?= $this->e($page->title); ?></h2>
        <?php if (isset($employees) && count($employees)) : ?>
            <?php foreach ($employees as $employee) : ?>
                <div class="row">
                    <?php if ($employee->image) : ?>
                        <div class="col-md-3">
                            <img class="employee-image" src="<?= $this->e($url('upload/' . $employee->image)); ?>" />
                        </div>
                    <?php endif; ?>
                    <div class="col-md-<?= ($employee->image ? 6 : 9); ?>">
                        <h3><?= $this->e($employee->name); ?></h3>
                        <h4><?= $this->e($employee->position); ?></h4>
                        <?php if (strlen(trim($employee->phone))) : ?>
                            <p class="employee-data"><?= $this->e($intl('employees.phone')); ?>: <a href="tel:<?= $this->e($employee->phone); ?>"><?= $this->e($employee->phone); ?></a></p>
                        <?php endif; ?>
                        <?php if (strlen(trim($employee->mail))) : ?>
                            <p class="employee-data"><?= $this->e($intl('employees.mail')); ?>: <a href="mailto:<?= $this->e($employee->mail); ?>"><?= $this->e($employee->mail); ?></a></p>
                        <?php endif; ?>
                        <?php if (strlen(trim($employee->office))) : ?>
                            <p class="employee-data"><?= $this->e($intl('employees.office')); ?>: <?= $this->e($employee->office); ?></p>
                        <?php endif; ?>
                        <?php foreach ($employee->description as $descr) : ?>
                            <p><?= $this->e($descr); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>

<?php if (CLI) : ?>
    <script type="index/json">
    <?= json_encode([
    'title'   => htmlspecialchars_decode(strip_tags($page->title)),
    'data'    => is_array($employees) ? implode(';', array_map(function ($item) {
        return implode(', ', array_filter(get_object_vars($item), function ($v) {
            return (!is_numeric($v) && !is_array($v) && strlen($v));
        }));
    }, $employees)) : null,
    'module'  => 'court',
    'id'      => $page->id
]) ?>
</script>
<?php endif ?>
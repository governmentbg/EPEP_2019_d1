<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container trial-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
            </div>
        </div>
        <form action="?" method="GET" class="wide-search-wrapper">
            <?php if (isset($error) && $error) : ?>
                <div class="alert alert-danger"><?= $this->e($intl($error)); ?></div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6">
                    <label><?= $this->e($intl('acts.from')); ?></label>
                    <input name="from" autocomplete="off" type="text" value="<?= isset($params['from']) && strtotime($params['from']) > 0 ? date('d.m.Y', strtotime($params['from'])) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label><?= $this->e($intl('acts.to')); ?></label>
                    <input name="to" autocomplete="off" type="text" value="<?= isset($params['to']) && strtotime($params['to']) > 0 ? date('d.m.Y', strtotime($params['to'])) : ''; ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label><?= $this->e($intl('acts.actkindcode')); ?></label>
                    <select name="actkindcode">
                        <option value=""><?= $this->e($intl('acts.actkindcode.all')); ?></option>
                        <?php if (isset($actCodes) && count($actCodes)) : ?>
                            <?php foreach ($actCodes as $code => $type) : ?>
                                <option value="<?= $this->e($code); ?>"<?= isset($params['actkindcode']) && $params['actkindcode'] == $code ? ' selected="selected"' : ''; ?>><?= $this->e($type); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label><?= $this->e($intl('acts.casenumber')); ?></label>
                    <input name="casenumber" type="text" value="<?= $this->e($params['casenumber'] ?? null); ?>">
                </div>
                <div class="col-md-3">
                    <label><?= $this->e($intl('acts.caseyear')); ?></label>
                    <input name="caseyear" type="text" value="<?= $this->e($params['caseyear'] ?? null); ?>">
                </div>
                <div class="col-md-3">
                    <label><?= $this->e($intl('acts.casetype')); ?></label>
                    <select name="casetype">
                        <option value=""><?= $this->e($intl('acts.casetype.all')); ?></option>
                        <?php if (isset($caseTypes) && is_array($caseTypes)) : ?>
                            <?php foreach ($caseTypes as $caseType => $caseTypeLabel) : ?>
                                <option value="<?= $this->e($caseType); ?>"<?= isset($params['casetype']) && $params['casetype'] == $caseType ? ' selected="selected"' : ''; ?>><?= $this->e($caseTypeLabel); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <button class="btn btn-info col-md-3 btn-lg"><?= $this->e($intl('acts.submit')); ?></button>
            </div>
        </form>
        <?php if (isset($data)) : ?>
            <div class="row">
                <div class="col-md-12">
                    <h3 class="trial-search-title"><?= $this->e($intl('acts.result')); ?></h3>
                </div>
            </div>
            <?php if (is_array($data) && count($data)) : ?>
                <?php $c = 1; ?>
                <div class="row">
                    <div class="col-md-12 table-container">
                        <table class="results-table">
                            <tr class="table-titles">
                                <th>№</th>
                                <th>Вид дело</th>
                                <th>Номер / година</th>
                                <!-- <th>Предмет</th> -->
                                <th>Обвинител, ищец, жалбо - подател</th>
                                <th>Обвиняем, ответник или ответник на жалбата</th>
                                <th>Съдия - докладчик</th>
                                <th>Вид акт</th>
                                <th>Дата</th>
                                <th>Файл</th>
                                <th>Мотиви</th>
                            </tr>
                            <?php foreach ($data as $key => $row) : ?>
                                <tr class="table-data">
                                    <td><?= $this->e($c++); ?></td>
                                    <td><?= $this->e($row['Case']['CaseKindName'] ?? null); ?></td>
                                    <td><?= $this->e(implode(' / ', array_filter([ ($row['Case']['Number'] ?? null), ($row['Case']['CaseYear'] ?? null) ]))); ?></td>
                                    <!-- <td><?= $this->e($row['Case']['LegalSubject']); ?></td> -->
                                    <td><?= $this->e(implode(', ', array_unique(array_filter(array_map(function ($item) use ($plaintiffs) {
                                        return in_array($item['InvolvmentKind'], $plaintiffs) ? $item['Name'] : null;
                                        }, $row['Case']['Sides']))))); ?></td>
                                    <td><?= $this->e(implode(', ', array_unique(array_filter(array_map(function ($item) use ($defendants) {
                                        return in_array($item['InvolvmentKind'], $defendants) ? $item['Name'] : null;
                                        }, $row['Case']['Sides']))))); ?></td>
                                    <?php $judge = null; ?>
                                    <?php foreach (($row['ActPreparators'] ?? []) as $j) : ?>
                                        <?php if (isset($j['Role']) && isset($j['JudgeName']) && $j['Role'] === 'докладчик') : ?>
                                            <?php
                                                $judge = $j['JudgeName'];
                                                break;
                                            ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if (!$judge) : ?>
                                        <?php foreach (($row['ActPreparators'] ?? []) as $j) : ?>
                                            <?php if (isset($j['Role']) && isset($j['JudgeName']) && $j['Role'] === 'председател') : ?>
                                                <?php
                                                    $judge = $j['JudgeName'];
                                                    break;
                                                ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <td><?= $this->e(($judge ?? ($row['Case']['Reporter'] ?? null))); ?></td>
                                    <td><?= $this->e($row['ActKindName'] ?? null); ?></td>
                                    <td><?= $this->e(isset($row['DateSigned']) && strtotime($row['DateSigned']) > 0 ? date('d.m.Y', strtotime($row['DateSigned'])) : ''); ?></td>
                                    <td>
                                        <?php if (isset($row['AttachmentAvailable']) && $row['AttachmentAvailable']) : ?>
                                            <a href="https://portalextensions.justice.bg/api/public/downloadact?guid=<?= $this->e($row['ActId']) ?>">Свали</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($row['MotiveAttachmentAvailable']) && $row['MotiveAttachmentAvailable']) : ?>
                                            <a href="https://portalextensions.justice.bg/api/public/downloadactmotive?guid=<?= $this->e($row['ActId']) ?>">Мотиви</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php else : ?>
                <h3><?= $this->e($intl('acts.result.none')); ?></h3>
            <?php endif; ?>
        <?php endif; ?>
        <script>
            $('[name=from], [name=to]').dtpckr({ maxDate: "<?= date('d.m.Y'); ?>" });
        </script>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>
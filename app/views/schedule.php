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
                    <label><?= $this->e($intl('schedule.from')); ?></label>
                    <input name="from" autocomplete="off" type="text" value="<?= isset($params['from']) && strtotime($params['from']) > 0 ? date('d.m.Y', strtotime($params['from'])) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label><?= $this->e($intl('schedule.to')); ?></label>
                    <input name="to" autocomplete="off" type="text" value="<?= isset($params['to']) && strtotime($params['to']) > 0 ? date('d.m.Y', strtotime($params['to'])) : ''; ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label><?= $this->e($intl('schedule.casenumber')); ?></label>
                    <input name="casenumber" type="text" value="<?= $this->e($params['casenumber'] ?? null); ?>">
                </div>
                <div class="col-md-6">
                    <label><?= $this->e($intl('schedule.caseyear')); ?></label>
                    <input name="caseyear" type="text" value="<?= $this->e($params['caseyear'] ?? null); ?>">
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <button class="btn btn-info col-md-3 btn-lg"><?= $this->e($intl('schedule.submit')); ?></button>
            </div>
        </form>
        <?php if (isset($data)) : ?>
            <div class="row">
                <div class="col-md-12">
                    <h3 class="trial-search-title"><?= $this->e($intl('schedule.result')); ?></h3>
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
                                <th>Статус на заседанието</th>
                                <th>Насрочване на делото</th>
                                <th>Зала</th>
                            </tr>
                            <?php foreach ($data as $key => $row) : ?>
                                <tr class="table-data">
                                    <td><?= $this->e($c++); ?></td>
                                    <td><?= $this->e($row['Case']['CaseKindName'] ?? null); ?></td>
                                    <td><?= $this->e(implode(' / ', array_filter([ ($row['Case']['Number'] ?? null), ($row['Case']['CaseYear'] ?? null) ]))); ?></td>
                                    <!-- <td><?= $this->e($row['Case']['LegalSubject'] ?? null); ?></td> -->
                                    <td><?= $this->e(implode(', ', array_unique(array_filter(array_map(function ($item) use ($plaintiffs) {
                                        return in_array($item['InvolvmentKind'], $plaintiffs) ? $item['Name'] : null;
                                        }, $row['Case']['Sides']))))); ?></td>
                                    <td><?= $this->e(implode(', ', array_unique(array_filter(array_map(function ($item) use ($defendants) {
                                        return in_array($item['InvolvmentKind'], $defendants) ? $item['Name'] : null;
                                        }, $row['Case']['Sides']))))); ?></td>
                                    <?php foreach (($row['HearingParticipants'] ?? []) as $j) : ?>
                                        <?php if (isset($j['Role']) && isset($j['JudgeName']) && $j['Role'] === 'докладчик') : ?>
                                            <?php
                                                $judge = $j['JudgeName'];
                                                break;
                                            ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if (!$judge) : ?>
                                        <?php foreach (($row['HearingParticipants'] ?? []) as $j) : ?>
                                            <?php if (isset($j['Role']) && isset($j['JudgeName']) && $j['Role'] === 'председател') : ?>
                                                <?php
                                                    $judge = $j['JudgeName'];
                                                    break;
                                                ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <td><?= $this->e(($judge ?? ($row['Case']['Reporter'] ?? null))); ?></td>
                                    <td><?= $this->e($row['HearingResult'] ?? null); ?></td>
                                    <?php
                                    $date = isset($row['Date']) ? date('d.m.Y H:i', strtotime($row['Date'])) : null;
                                    if ($date) {
                                        $temp = explode(' ', $date, 2);
                                        $date = isset($temp[1]) && $temp[1] == '00:00' ? $temp[0] : $date;
                                    }
                                    ?>
                                    <td><?= $this->e($date); ?></td>
                                    <td><?= $this->e($row['CourtRoom'] ?? null); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php else : ?>
                <h3><?= $this->e($intl('schedule.result.none')); ?></h3>
            <?php endif; ?>
        <?php endif; ?>
        <script>
            $('[name=from], [name=to]').dtpckr();
        </script>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>
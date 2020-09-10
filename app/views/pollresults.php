<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container poll-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($intl('poll.results') . ' ' . $poll->title); ?></h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 poll-questions">
                <?php foreach ($poll->questions as $question) : ?>
                    <div class="question-wrapper">
                        <?php if ($question['type'] != 'open') : ?>
                            <p><?= $this->e($question['title']); ?></p>
                            <ul class="poll-answers">
                                <?php foreach ($question['answers'] as $answer) : ?>
                                    <li><?= $this->e($answer['title']); ?></p>
                                        <div class="progress">
                                        <?php
                                            $width = $results['count'] ? round(((int) ($results['results'][$question['question']][$answer['answer']] ?? 0) * 100) / $results['count']) : 0;
                                        ?>
                                            <div class="progress-bar" style="width: <?= $width ?>%; <?= $width == 0 ? 'color: #000; text-indent: 10px' : ''; ?>" role="progressbar" > <?= $width ?>%</div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>
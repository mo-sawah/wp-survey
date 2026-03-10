<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap wps-results-wrap">

    <!-- ── Header ─────────────────────────────────────────────── -->
    <div class="wps-results-header">
        <div class="wps-results-title-row">
            <h1>📋 <?php _e('Survey Results', 'wp-survey'); ?></h1>
            <?php if ($results): ?>
            <div class="wps-results-header-actions">
                <a href="<?php echo admin_url('admin.php?page=wp-survey-add&id=' . $selected_id); ?>" class="button button-secondary">
                    ✏️ <?php _e('Edit Survey', 'wp-survey'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=wp-survey-analytics&survey_id=' . $selected_id); ?>" class="button button-secondary">
                    📊 <?php _e('Analytics', 'wp-survey'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($surveys)): ?>
        <div class="wps-results-selector-bar">
            <form method="get" action="" class="wps-results-selector-form">
                <input type="hidden" name="page" value="wp-survey-results">
                <div class="wps-results-selector-inner">
                    <label class="wps-results-selector-label"><?php _e('Survey:', 'wp-survey'); ?></label>
                    <select name="survey_id" class="wps-results-select" onchange="this.form.submit()">
                        <?php foreach ($surveys as $s): ?>
                        <option value="<?php echo $s->id; ?>" <?php selected($selected_id, $s->id); ?>>
                            #<?php echo $s->id; ?> — <?php echo esc_html($s->title); ?>
                            (<?php echo $s->survey_type === 'multi-question' ? __('Multi-Q', 'wp-survey') : __('Simple', 'wp-survey'); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($surveys)): ?>
    <!-- ── No surveys ─────────────────────────────────────────── -->
    <div class="wps-results-empty">
        <div class="wps-results-empty-icon">📋</div>
        <h2><?php _e('No surveys found', 'wp-survey'); ?></h2>
        <p><?php _e('Create a survey first to see results.', 'wp-survey'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=wp-survey-add'); ?>" class="button button-primary button-hero"><?php _e('Create Survey', 'wp-survey'); ?></a>
    </div>

    <?php elseif (!$results || $results['total_votes'] == 0): ?>
    <!-- ── No votes yet ───────────────────────────────────────── -->
    <div class="wps-results-empty">
        <div class="wps-results-empty-icon">🗳️</div>
        <h2><?php _e('No votes yet', 'wp-survey'); ?></h2>
        <p><?php _e('This survey has not received any votes yet.', 'wp-survey'); ?></p>
        <?php if ($results): ?>
        <p><strong><?php _e('Shortcode:', 'wp-survey'); ?></strong>
            <code>[wp_survey id="<?php echo $selected_id; ?>"]</code>
        </p>
        <?php endif; ?>
    </div>

    <?php else:
        $survey      = $results['survey'];
        $multi_votes = !empty($survey->allow_multiple_votes);
        $colors      = ['#6366f1','#10b981','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#a855f7'];
    ?>

    <!-- ── Voting mode notice ─────────────────────────────────── -->
    <?php if ($multi_votes): ?>
    <div class="wps-results-notice warning">
        ⚠️ <?php _e('This survey has <strong>Multiple Votes</strong> enabled — one person can vote many times.', 'wp-survey'); ?>
    </div>
    <?php endif; ?>

    <!-- ── Summary bar ───────────────────────────────────────── -->
    <div class="wps-results-summary">
        <div class="wps-results-summary-item">
            <span class="wps-results-summary-number"><?php echo number_format($results['total_votes']); ?></span>
            <span class="wps-results-summary-label"><?php _e('Total Votes', 'wp-survey'); ?></span>
        </div>
        <div class="wps-results-summary-divider"></div>
        <div class="wps-results-summary-item">
            <span class="wps-results-summary-number"><?php echo number_format($results['unique_voters']); ?></span>
            <span class="wps-results-summary-label"><?php _e('Unique Voters', 'wp-survey'); ?></span>
        </div>
        <div class="wps-results-summary-divider"></div>
        <div class="wps-results-summary-item">
            <span class="wps-results-summary-number"><?php echo count($results['questions']); ?></span>
            <span class="wps-results-summary-label"><?php _e('Question(s)', 'wp-survey'); ?></span>
        </div>
        <div class="wps-results-summary-divider"></div>
        <div class="wps-results-summary-item">
            <span class="wps-results-summary-number" style="font-size:14px; font-weight:500;">
                <?php echo $multi_votes
                    ? '<span style="color:#d97706">⚠️ ' . __('Unlimited', 'wp-survey') . '</span>'
                    : '<span style="color:#059669">🔒 ' . __('One per person', 'wp-survey') . '</span>'; ?>
            </span>
            <span class="wps-results-summary-label"><?php _e('Voting Mode', 'wp-survey'); ?></span>
        </div>
    </div>

    <!-- ── Per-question results ───────────────────────────────── -->
    <?php foreach ($results['questions'] as $qi => $qdata):
        $q_colors = array_slice($colors, 0, count($qdata['choices']));
        $winner   = !empty($qdata['choices']) ? $qdata['choices'][0] : null;
    ?>
    <div class="wps-results-question-card">

        <!-- Question header -->
        <div class="wps-results-question-head">
            <?php if ($survey->survey_type === 'multi-question'): ?>
            <span class="wps-results-q-badge"><?php _e('Q', 'wp-survey'); ?><?php echo $qi + 1; ?></span>
            <?php endif; ?>
            <div class="wps-results-q-text"><?php echo esc_html($qdata['question']); ?></div>
            <div class="wps-results-q-meta">
                <span class="wps-results-q-total">
                    <?php echo number_format($qdata['total_votes']); ?> <?php _e('votes', 'wp-survey'); ?>
                </span>
                <?php if ($winner): ?>
                <span class="wps-results-q-leader">
                    🏆 <?php echo esc_html($winner['title']); ?> (<?php echo $winner['percentage']; ?>%)
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Results body: chart + rows -->
        <div class="wps-results-question-body">

            <!-- Doughnut chart -->
            <?php if (!empty($qdata['choices'])): ?>
            <div class="wps-results-donut-wrap">
                <canvas id="donut-<?php echo $qdata['id'] . '-' . $qi; ?>" width="220" height="220"></canvas>
                <div class="wps-results-donut-legend">
                    <?php foreach ($qdata['choices'] as $ci => $c): ?>
                    <div class="wps-results-legend-item">
                        <span class="wps-results-legend-dot" style="background:<?php echo $colors[$ci % count($colors)]; ?>"></span>
                        <span class="wps-results-legend-name"><?php echo esc_html(wp_trim_words($c['title'], 5, '…')); ?></span>
                        <span class="wps-results-legend-pct"><?php echo $c['percentage']; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ranked rows -->
            <div class="wps-results-rows">
                <?php foreach ($qdata['choices'] as $ci => $choice):
                    $color     = $colors[$ci % count($colors)];
                    $is_winner = ($ci === 0);
                ?>
                <div class="wps-results-row <?php echo $is_winner ? 'is-winner' : ''; ?>">
                    <?php if ($choice['image_url']): ?>
                    <div class="wps-results-row-img">
                        <img src="<?php echo esc_url($choice['image_url']); ?>" alt="<?php echo esc_attr($choice['title']); ?>">
                    </div>
                    <?php endif; ?>

                    <div class="wps-results-row-body">
                        <div class="wps-results-row-title-row">
                            <span class="wps-results-row-rank" style="background:<?php echo $color; ?>">
                                <?php echo $is_winner ? '🏆' : ($ci + 1); ?>
                            </span>
                            <span class="wps-results-row-title"><?php echo esc_html($choice['title']); ?></span>
                            <?php if ($is_winner): ?>
                            <span class="wps-results-winner-badge"><?php _e('Leading', 'wp-survey'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="wps-results-bar-row">
                            <div class="wps-results-bar-track">
                                <div class="wps-results-bar-fill"
                                     style="width:<?php echo $choice['percentage']; ?>%; background:<?php echo $color; ?>;"
                                     data-pct="<?php echo $choice['percentage']; ?>">
                                </div>
                            </div>
                            <span class="wps-results-bar-pct" style="color:<?php echo $color; ?>">
                                <?php echo $choice['percentage']; ?>%
                            </span>
                        </div>
                    </div>

                    <div class="wps-results-row-count" style="border-color:<?php echo $color; ?>">
                        <strong><?php echo number_format($choice['votes']); ?></strong>
                        <span><?php _e('votes', 'wp-survey'); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($qdata['choices'])): ?>
                <p style="color:#9ca3af; text-align:center; padding:20px;"><?php _e('No choices found.', 'wp-survey'); ?></p>
                <?php endif; ?>
            </div>

        </div><!-- .wps-results-question-body -->
    </div><!-- .wps-results-question-card -->
    <?php endforeach; ?>

    <?php endif; // end has votes ?>

</div><!-- .wps-results-wrap -->

<?php if (!empty($results) && $results['total_votes'] > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var COLORS = ['#6366f1','#10b981','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#a855f7'];

    <?php foreach ($results['questions'] as $qi => $qdata):
        if (empty($qdata['choices'])) continue;
        $chart_id = 'donut-' . $qdata['id'] . '-' . $qi;
        $labels   = wp_json_encode(array_column($qdata['choices'], 'title'));
        $votes    = wp_json_encode(array_column($qdata['choices'], 'votes'));
        $count    = count($qdata['choices']);
    ?>
    (function() {
        var el = document.getElementById('<?php echo $chart_id; ?>');
        if (!el) return;
        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    data: <?php echo $votes; ?>,
                    backgroundColor: COLORS.slice(0, <?php echo $count; ?>),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderWidth: 4,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var total = ctx.dataset.data.reduce(function(a,b){return a+b;}, 0);
                                var pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    })();
    <?php endforeach; ?>

    // Animate progress bars
    document.querySelectorAll('.wps-results-bar-fill').forEach(function(bar) {
        var target = bar.dataset.pct + '%';
        bar.style.width = '0%';
        requestAnimationFrame(function() {
            setTimeout(function() { bar.style.width = target; }, 100);
        });
    });
});
</script>
<?php endif; ?>

<style>
/* ══════════════════════════════════════════════
   WP Survey — Results Page
   ══════════════════════════════════════════════ */
.wps-results-wrap { max-width: 1100px; }

/* Header */
.wps-results-header { margin: 20px 0 24px; }
.wps-results-title-row {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 16px;
}
.wps-results-title-row h1 { margin: 0; font-size: 24px; }
.wps-results-header-actions { display: flex; gap: 8px; }

/* Selector bar */
.wps-results-selector-bar {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.wps-results-selector-inner { display: flex; align-items: center; gap: 12px; }
.wps-results-selector-label { font-weight: 600; font-size: 13px; color: #374151; white-space: nowrap; }
.wps-results-select {
    flex: 1; max-width: 500px; height: 38px;
    padding: 4px 12px; border-radius: 6px;
    border: 1px solid #d1d5db; font-size: 14px;
}

/* Notice */
.wps-results-notice {
    padding: 12px 16px; border-radius: 8px;
    font-size: 13px; margin-bottom: 20px;
}
.wps-results-notice.warning { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; }

/* Summary bar */
.wps-results-summary {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px 28px;
    display: flex; align-items: center; gap: 0;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    flex-wrap: wrap;
}
.wps-results-summary-item {
    flex: 1; min-width: 120px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 8px 0;
}
.wps-results-summary-number { font-size: 28px; font-weight: 700; color: #111827; line-height: 1; }
.wps-results-summary-label  { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 500; }
.wps-results-summary-divider { width: 1px; background: #e5e7eb; align-self: stretch; margin: 0 4px; }

/* Question card */
.wps-results-question-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    margin-bottom: 24px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    overflow: hidden;
}

/* Question head */
.wps-results-question-head {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    padding: 18px 22px;
    display: flex; align-items: center; gap: 12px;
    flex-wrap: wrap;
}
.wps-results-q-badge {
    background: rgba(255,255,255,0.2);
    color: #fff;
    font-size: 12px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px;
    white-space: nowrap;
}
.wps-results-q-text {
    flex: 1; font-size: 16px; font-weight: 600;
    color: #fff; line-height: 1.4;
}
.wps-results-q-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.wps-results-q-total {
    background: rgba(255,255,255,0.15);
    color: #e0e7ff; font-size: 12px; font-weight: 500;
    padding: 4px 10px; border-radius: 20px;
}
.wps-results-q-leader {
    background: rgba(245,158,11,0.25);
    color: #fde68a; font-size: 12px; font-weight: 600;
    padding: 4px 10px; border-radius: 20px;
}

/* Question body */
.wps-results-question-body {
    display: flex; gap: 0;
    align-items: flex-start;
}

/* Donut chart */
.wps-results-donut-wrap {
    padding: 24px 20px;
    border-right: 1px solid #e5e7eb;
    display: flex; flex-direction: column; align-items: center; gap: 16px;
    min-width: 260px; flex-shrink: 0;
}
.wps-results-donut-legend { width: 100%; }
.wps-results-legend-item {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 6px;
}
.wps-results-legend-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.wps-results-legend-name { flex: 1; font-size: 12px; color: #374151; }
.wps-results-legend-pct  { font-size: 12px; font-weight: 700; color: #6b7280; min-width: 36px; text-align: right; }

/* Rows */
.wps-results-rows { flex: 1; padding: 16px 20px; display: flex; flex-direction: column; gap: 12px; }

.wps-results-row {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 14px;
    border-radius: 10px;
    background: #f9fafb;
    border: 1px solid #f3f4f6;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.wps-results-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-color: #e5e7eb; }
.wps-results-row.is-winner {
    background: linear-gradient(to right, #fefce8, #f9fafb);
    border-color: #fde68a;
}

/* Choice image */
.wps-results-row-img {
    width: 56px; height: 56px;
    border-radius: 8px; overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.wps-results-row-img img { width: 100%; height: 100%; object-fit: cover; display: block; }

/* Row body */
.wps-results-row-body { flex: 1; min-width: 0; }
.wps-results-row-title-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.wps-results-row-rank {
    width: 26px; height: 26px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.wps-results-row-title { font-size: 14px; font-weight: 600; color: #111827; flex: 1; }
.wps-results-winner-badge {
    background: #fef08a; color: #713f12;
    font-size: 11px; font-weight: 700;
    padding: 2px 8px; border-radius: 20px;
    white-space: nowrap;
}
.wps-results-bar-row { display: flex; align-items: center; gap: 10px; }
.wps-results-bar-track {
    flex: 1; height: 8px; background: #e5e7eb;
    border-radius: 10px; overflow: hidden;
}
.wps-results-bar-fill {
    height: 100%; border-radius: 10px;
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
.wps-results-bar-pct { font-size: 13px; font-weight: 700; min-width: 40px; text-align: right; }

/* Vote count box */
.wps-results-row-count {
    text-align: center; min-width: 64px;
    padding: 8px 10px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    flex-shrink: 0;
}
.wps-results-row-count strong { display: block; font-size: 20px; color: #111827; line-height: 1; }
.wps-results-row-count span   { font-size: 11px; color: #9ca3af; }

/* Empty state */
.wps-results-empty {
    text-align: center; padding: 80px 20px;
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 12px; margin-top: 20px;
}
.wps-results-empty-icon { font-size: 80px; margin-bottom: 16px; }
.wps-results-empty h2 { font-size: 22px; margin-bottom: 8px; }
.wps-results-empty p  { color: #6b7280; font-size: 15px; margin-bottom: 16px; }

/* Responsive */
@media (max-width: 820px) {
    .wps-results-question-body { flex-direction: column; }
    .wps-results-donut-wrap { border-right: none; border-bottom: 1px solid #e5e7eb; flex-direction: row; flex-wrap: wrap; justify-content: center; }
    .wps-results-summary { gap: 12px; }
    .wps-results-summary-divider { display: none; }
}
@media (max-width: 540px) {
    .wps-results-row-img { width: 44px; height: 44px; }
    .wps-results-row-count strong { font-size: 16px; }
}
</style>

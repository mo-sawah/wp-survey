<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap wp-survey-analytics-wrap">

    <!-- Header -->
    <div class="wp-survey-analytics-header">
        <h1>📊 <?php _e('Survey Analytics', 'wp-survey'); ?></h1>
        
        <?php if (!empty($surveys)): ?>
        <form method="get" action="" class="wp-survey-analytics-selector">
            <input type="hidden" name="page" value="wp-survey-analytics">
            <select name="survey_id" onchange="this.form.submit()" class="wp-survey-analytics-select">
                <?php foreach ($surveys as $s): ?>
                <option value="<?php echo $s->id; ?>" <?php selected($selected_id, $s->id); ?>>
                    <?php echo esc_html($s->title); ?>
                    (<?php echo $s->survey_type === 'multi-question' ? __('Multi-Q', 'wp-survey') : __('Simple', 'wp-survey'); ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($analytics): ?>
            <a href="<?php echo admin_url('admin.php?page=wp-survey-add&id=' . $selected_id); ?>" class="button button-secondary">
                ✏️ <?php _e('Edit Survey', 'wp-survey'); ?>
            </a>
            <?php endif; ?>
        </form>
        <?php endif; ?>
    </div>

    <?php if (empty($surveys)): ?>
    <div class="wp-survey-analytics-empty">
        <div class="wp-survey-analytics-empty-icon">📊</div>
        <h2><?php _e('No surveys found', 'wp-survey'); ?></h2>
        <p><?php _e('Create a survey first to start viewing analytics.', 'wp-survey'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=wp-survey-add'); ?>" class="button button-primary button-hero"><?php _e('Create Survey', 'wp-survey'); ?></a>
    </div>
    
    <?php elseif (!$analytics || $analytics['total_votes'] == 0): ?>
    <div class="wp-survey-analytics-empty">
        <div class="wp-survey-analytics-empty-icon">🗳️</div>
        <h2><?php _e('No votes yet', 'wp-survey'); ?></h2>
        <p><?php _e('This survey has not received any votes yet. Share the shortcode on your website to start collecting responses.', 'wp-survey'); ?></p>
        <?php if ($analytics): ?>
        <code style="font-size: 16px; padding: 10px 20px; background: #f3f4f6; border-radius: 6px;">[wp_survey id="<?php echo $selected_id; ?>"]</code>
        <?php endif; ?>
    </div>
    
    <?php else:
        $data = $analytics;
        $survey = $data['survey'];
        
        // Voting mode indicator
        $multi_votes = !empty($survey->allow_multiple_votes);
        
        // Calculate trend (today vs yesterday)
        $yesterday_votes = 0;
        $today_idx = count($data['votes_by_day']) - 1;
        if ($today_idx >= 1) {
            $yesterday_votes = $data['votes_by_day'][$today_idx - 1]['votes'];
        }
        $today_votes = $data['today_votes'];
        $trend = $yesterday_votes > 0 ? round((($today_votes - $yesterday_votes) / $yesterday_votes) * 100) : ($today_votes > 0 ? 100 : 0);
    ?>

    <!-- Voting mode banner -->
    <?php if ($multi_votes): ?>
    <div class="wp-survey-analytics-mode-banner warning">
        ⚠️ <?php _e('This survey has <strong>Multiple Votes</strong> enabled — one person can vote many times. Vote counts may not represent unique users.', 'wp-survey'); ?>
    </div>
    <?php else: ?>
    <div class="wp-survey-analytics-mode-banner success">
        🔒 <?php _e('This survey uses <strong>One Vote Per Person</strong> mode — cookie-based duplicate protection is active.', 'wp-survey'); ?>
    </div>
    <?php endif; ?>

    <!-- Stats Cards Row -->
    <div class="wp-survey-analytics-stats-row">
        <div class="wp-survey-analytics-stat-card">
            <div class="wp-survey-analytics-stat-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">🗳️</div>
            <div class="wp-survey-analytics-stat-body">
                <div class="wp-survey-analytics-stat-number"><?php echo number_format($data['total_votes']); ?></div>
                <div class="wp-survey-analytics-stat-label"><?php _e('Answeres', 'wp-survey'); ?></div>
            </div>
        </div>
        
        <div class="wp-survey-analytics-stat-card">
            <div class="wp-survey-analytics-stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">👥</div>
            <div class="wp-survey-analytics-stat-body">
                <div class="wp-survey-analytics-stat-number"><?php echo number_format($data['unique_voters']); ?></div>
                <div class="wp-survey-analytics-stat-label"><?php _e('Unique Voters', 'wp-survey'); ?></div>
            </div>
        </div>
        
        <div class="wp-survey-analytics-stat-card">
            <div class="wp-survey-analytics-stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">📅</div>
            <div class="wp-survey-analytics-stat-body">
                <div class="wp-survey-analytics-stat-number">
                    <?php echo number_format($today_votes); ?>
                    <?php if ($trend !== 0): ?>
                    <span class="wp-survey-analytics-trend <?php echo $trend > 0 ? 'up' : 'down'; ?>">
                        <?php echo $trend > 0 ? '↑' : '↓'; ?><?php echo abs($trend); ?>%
                    </span>
                    <?php endif; ?>
                </div>
                <div class="wp-survey-analytics-stat-label"><?php _e('Votes Today', 'wp-survey'); ?></div>
            </div>
        </div>
        
        <div class="wp-survey-analytics-stat-card">
            <div class="wp-survey-analytics-stat-icon" style="background: linear-gradient(135deg, #ec4899, #db2777);">📋</div>
            <div class="wp-survey-analytics-stat-body">
                <div class="wp-survey-analytics-stat-number"><?php echo count($data['questions']); ?></div>
                <div class="wp-survey-analytics-stat-label"><?php _e('Questions', 'wp-survey'); ?></div>
            </div>
        </div>
        
        <?php if ($data['first_vote']): ?>
        <div class="wp-survey-analytics-stat-card">
            <div class="wp-survey-analytics-stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">🕐</div>
            <div class="wp-survey-analytics-stat-body">
                <div class="wp-survey-analytics-stat-number" style="font-size: 16px;">
                    <?php echo date_i18n(get_option('date_format'), strtotime($data['last_vote'])); ?>
                </div>
                <div class="wp-survey-analytics-stat-label"><?php _e('Last Vote', 'wp-survey'); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Charts Row -->
    <div class="wp-survey-analytics-charts-row">
        
        <!-- Votes Over Time -->
        <div class="wp-survey-analytics-chart-card wide">
            <div class="wp-survey-analytics-chart-header">
                <h3>📈 <?php _e('Votes Over Time', 'wp-survey'); ?></h3>
                <span class="wp-survey-analytics-chart-sub"><?php _e('Last 30 days', 'wp-survey'); ?></span>
            </div>
            <div class="wp-survey-analytics-chart-body">
                <canvas id="chart-votes-time" height="120"></canvas>
            </div>
        </div>
        
        <!-- Hourly Distribution -->
        <div class="wp-survey-analytics-chart-card">
            <div class="wp-survey-analytics-chart-header">
                <h3>🕐 <?php _e('Activity by Hour', 'wp-survey'); ?></h3>
                <span class="wp-survey-analytics-chart-sub"><?php _e('All time', 'wp-survey'); ?></span>
            </div>
            <div class="wp-survey-analytics-chart-body">
                <canvas id="chart-hourly" height="200"></canvas>
            </div>
        </div>
        
    </div>

    <!-- Per-Question Results -->
    <?php foreach ($data['questions'] as $qi => $qdata): ?>
    <div class="wp-survey-analytics-question-card">
        <div class="wp-survey-analytics-question-header">
            <?php if ($survey->survey_type === 'multi-question'): ?>
            <span class="wp-survey-analytics-question-badge"><?php _e('Question', 'wp-survey'); ?> <?php echo ($qi + 1); ?></span>
            <?php endif; ?>
            <h3 class="wp-survey-analytics-question-text"><?php echo esc_html($qdata['question']); ?></h3>
            <span class="wp-survey-analytics-question-total">
                <?php echo number_format($qdata['total_votes']); ?> <?php _e('votes', 'wp-survey'); ?>
            </span>
        </div>
        
        <?php if (!empty($qdata['choices'])): ?>
        <div class="wp-survey-analytics-question-body">
            <!-- Bar Chart -->
            <div class="wp-survey-analytics-barchart-wrap">
                <canvas id="chart-q-<?php echo $qdata['id'] . '-' . $qi; ?>" height="<?php echo min(300, count($qdata['choices']) * 50 + 60); ?>"></canvas>
            </div>
            
            <!-- Results Table -->
            <div class="wp-survey-analytics-results-table">
                <?php
                $colors = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316', '#14b8a6', '#a855f7'];
                $color_idx = 0;
                foreach ($qdata['choices'] as $choice):
                    $color = $colors[$color_idx % count($colors)];
                    $color_idx++;
                ?>
                <div class="wp-survey-analytics-result-row">
                    <div class="wp-survey-analytics-result-rank" style="background: <?php echo $color; ?>">
                        <?php echo $color_idx; ?>
                    </div>
                    <div class="wp-survey-analytics-result-info">
                        <div class="wp-survey-analytics-result-title"><?php echo esc_html($choice['title']); ?></div>
                        <div class="wp-survey-analytics-result-bar-wrap">
                            <div class="wp-survey-analytics-result-bar">
                                <div class="wp-survey-analytics-result-fill"
                                     style="width: <?php echo $choice['percentage']; ?>%; background: <?php echo $color; ?>;">
                                </div>
                            </div>
                            <span class="wp-survey-analytics-result-pct"><?php echo $choice['percentage']; ?>%</span>
                        </div>
                    </div>
                    <div class="wp-survey-analytics-result-count">
                        <strong><?php echo number_format($choice['votes']); ?></strong>
                        <span><?php _e('votes', 'wp-survey'); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <!-- Recent Votes -->
    <?php if (!empty($data['recent'])): ?>
    <div class="wp-survey-analytics-recent-card">
        <div class="wp-survey-analytics-chart-header">
            <h3>🕐 <?php _e('Recent Votes', 'wp-survey'); ?></h3>
            <span class="wp-survey-analytics-chart-sub"><?php _e('Last 20 votes', 'wp-survey'); ?></span>
        </div>
        <div class="wp-survey-analytics-recent-table-wrap">
            <table class="wp-survey-analytics-recent-table">
                <thead>
                    <tr>
                        <th><?php _e('Date & Time', 'wp-survey'); ?></th>
                        <?php if ($survey->survey_type === 'multi-question'): ?>
                        <th><?php _e('Question', 'wp-survey'); ?></th>
                        <?php endif; ?>
                        <th><?php _e('Choice', 'wp-survey'); ?></th>
                        <th><?php _e('IP Address', 'wp-survey'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['recent'] as $r): ?>
                    <tr>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($r->created_at))); ?></td>
                        <?php if ($survey->survey_type === 'multi-question'): ?>
                        <td><?php echo esc_html(wp_trim_words($r->question_text, 8, '…')); ?></td>
                        <?php endif; ?>
                        <td><strong><?php echo esc_html($r->choice_title); ?></strong></td>
                        <td><code><?php echo esc_html($r->ip_address); ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; // end if has votes ?>

</div><!-- .wp-survey-analytics-wrap -->

<?php if ($analytics && $analytics['total_votes'] > 0):
    // Prepare data for JS
    $time_labels  = wp_json_encode(array_column($data['votes_by_day'], 'label'));
    $time_data    = wp_json_encode(array_column($data['votes_by_day'], 'votes'));
    $hourly_data  = wp_json_encode(array_values($data['hourly']));
    $hourly_labels = wp_json_encode(array_map(function($h) {
        return ($h === 0) ? '12am' : ($h < 12 ? $h.'am' : ($h === 12 ? '12pm' : ($h-12).'pm'));
    }, range(0, 23)));
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Color palette ──
    var PALETTE = ['#6366f1','#10b981','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#a855f7'];
    
    var chartDefaults = {
        plugins: { legend: { display: false } },
        responsive: true,
        maintainAspectRatio: true,
    };
    
    // ── Votes Over Time ──
    new Chart(document.getElementById('chart-votes-time'), {
        type: 'line',
        data: {
            labels: <?php echo $time_labels; ?>,
            datasets: [{
                label: '<?php _e('Votes', 'wp-survey'); ?>',
                data: <?php echo $time_data; ?>,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#6366f1',
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            ...chartDefaults,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
            }
        }
    });
    
    // ── Hourly Distribution ──
    new Chart(document.getElementById('chart-hourly'), {
        type: 'bar',
        data: {
            labels: <?php echo $hourly_labels; ?>,
            datasets: [{
                label: '<?php _e('Votes', 'wp-survey'); ?>',
                data: <?php echo $hourly_data; ?>,
                backgroundColor: function(ctx) {
                    var v = ctx.dataset.data[ctx.dataIndex];
                    var max = Math.max(...ctx.dataset.data);
                    var alpha = max > 0 ? 0.3 + (v / max) * 0.7 : 0.3;
                    return 'rgba(245,158,11,' + alpha + ')';
                },
                borderRadius: 4,
            }]
        },
        options: {
            ...chartDefaults,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false }, ticks: { maxRotation: 45 } }
            }
        }
    });
    
    // ── Per-question doughnut / bar charts ──
    <?php foreach ($data['questions'] as $qi => $qdata):
        if (empty($qdata['choices'])) continue;
        $chart_id     = 'chart-q-' . $qdata['id'] . '-' . $qi;
        $q_labels     = wp_json_encode(array_column($qdata['choices'], 'title'));
        $q_votes      = wp_json_encode(array_column($qdata['choices'], 'votes'));
        $q_colors     = wp_json_encode(array_slice($GLOBALS['wp_survey_analytics_colors'] ?? ['#6366f1','#10b981','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#a855f7'], 0, count($qdata['choices'])));
    ?>
    (function() {
        var labels = <?php echo $q_labels; ?>;
        var votes  = <?php echo $q_votes; ?>;
        var colors = PALETTE.slice(0, labels.length);
        
        new Chart(document.getElementById('<?php echo $chart_id; ?>'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '<?php _e('Votes', 'wp-survey'); ?>',
                    data: votes,
                    backgroundColor: colors,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((ctx.parsed.x / total) * 100).toFixed(1) : 0;
                                return ' ' + ctx.parsed.x + ' votes (' + pct + '%)';
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    })();
    <?php endforeach; ?>
});
</script>

<style>
/* ── Analytics Page Styles ── */
.wp-survey-analytics-wrap { max-width: 1400px; }

.wp-survey-analytics-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin: 20px 0 24px;
}
.wp-survey-analytics-header h1 { margin: 0; font-size: 24px; }
.wp-survey-analytics-selector { display: flex; align-items: center; gap: 10px; }
.wp-survey-analytics-select {
    height: 36px;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 14px;
    min-width: 280px;
}

.wp-survey-analytics-mode-banner {
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 20px;
}
.wp-survey-analytics-mode-banner.warning { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; }
.wp-survey-analytics-mode-banner.success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }

/* Stats Cards */
.wp-survey-analytics-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.wp-survey-analytics-stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: box-shadow 0.2s;
}
.wp-survey-analytics-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.wp-survey-analytics-stat-icon {
    width: 48px; height: 48px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.wp-survey-analytics-stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    line-height: 1;
    margin-bottom: 4px;
}
.wp-survey-analytics-stat-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}
.wp-survey-analytics-trend {
    font-size: 13px;
    font-weight: 600;
    margin-left: 4px;
}
.wp-survey-analytics-trend.up { color: #059669; }
.wp-survey-analytics-trend.down { color: #dc2626; }

/* Charts Row */
.wp-survey-analytics-charts-row {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    margin-bottom: 24px;
}
.wp-survey-analytics-chart-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.wp-survey-analytics-chart-card.wide { grid-column: 1; }
.wp-survey-analytics-chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.wp-survey-analytics-chart-header h3 { margin: 0; font-size: 16px; color: #111827; }
.wp-survey-analytics-chart-sub { font-size: 12px; color: #9ca3af; }
.wp-survey-analytics-chart-body { position: relative; }

/* Question Cards */
.wp-survey-analytics-question-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    overflow: hidden;
}
.wp-survey-analytics-question-header {
    padding: 18px 20px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.wp-survey-analytics-question-badge {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.wp-survey-analytics-question-text {
    flex: 1;
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}
.wp-survey-analytics-question-total {
    font-size: 13px;
    color: #6b7280;
    white-space: nowrap;
    background: #e5e7eb;
    padding: 4px 10px;
    border-radius: 20px;
}
.wp-survey-analytics-question-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    padding: 20px;
}
.wp-survey-analytics-barchart-wrap { display: flex; align-items: center; }

/* Results Table */
.wp-survey-analytics-results-table { display: flex; flex-direction: column; gap: 12px; }
.wp-survey-analytics-result-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: #f9fafb;
    border-radius: 8px;
}
.wp-survey-analytics-result-rank {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
}
.wp-survey-analytics-result-info { flex: 1; min-width: 0; }
.wp-survey-analytics-result-title {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 6px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.wp-survey-analytics-result-bar-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.wp-survey-analytics-result-bar {
    flex: 1;
    height: 6px;
    background: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}
.wp-survey-analytics-result-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.6s ease;
}
.wp-survey-analytics-result-pct {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    width: 40px;
    text-align: right;
}
.wp-survey-analytics-result-count {
    text-align: right;
    min-width: 60px;
}
.wp-survey-analytics-result-count strong { display: block; font-size: 18px; color: #111827; }
.wp-survey-analytics-result-count span { font-size: 11px; color: #9ca3af; }

/* Recent Votes */
.wp-survey-analytics-recent-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    margin-bottom: 30px;
}
.wp-survey-analytics-recent-table-wrap { overflow-x: auto; margin-top: 16px; }
.wp-survey-analytics-recent-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.wp-survey-analytics-recent-table th {
    text-align: left;
    padding: 10px 14px;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    color: #374151;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.wp-survey-analytics-recent-table td {
    padding: 10px 14px;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
}
.wp-survey-analytics-recent-table tr:last-child td { border-bottom: none; }
.wp-survey-analytics-recent-table tr:hover td { background: #f9fafb; }
.wp-survey-analytics-recent-table code {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    color: #6b7280;
}

/* Empty State */
.wp-survey-analytics-empty {
    text-align: center;
    padding: 80px 20px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-top: 20px;
}
.wp-survey-analytics-empty-icon { font-size: 80px; margin-bottom: 20px; }
.wp-survey-analytics-empty h2 { font-size: 22px; margin-bottom: 10px; }
.wp-survey-analytics-empty p { color: #6b7280; font-size: 15px; margin-bottom: 20px; }

/* Responsive */
@media (max-width: 1100px) {
    .wp-survey-analytics-charts-row { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
    .wp-survey-analytics-question-body { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .wp-survey-analytics-stats-row { grid-template-columns: 1fr 1fr; }
}
</style>
<?php endif; ?>

<?php if (!defined('ABSPATH')) exit;

// Gather first/last vote dates for cover
global $wpdb;
$rt = $wpdb->prefix . 'survey_responses';
$first_vote = $selected_id ? $wpdb->get_var($wpdb->prepare("SELECT MIN(created_at) FROM $rt WHERE survey_id=%d", $selected_id)) : null;
$last_vote  = $selected_id ? $wpdb->get_var($wpdb->prepare("SELECT MAX(created_at) FROM $rt WHERE survey_id=%d", $selected_id)) : null;
$site_logo  = get_site_icon_url(120);
$site_name  = get_bloginfo('name');
$site_url   = get_site_url();
?>

<div class="wrap wps-results-wrap">

    <!-- ── Header ──────────────────────────────────────────────────── -->
    <div class="wps-results-header">
        <div class="wps-results-title-row">
            <h1>📋 <?php _e('Survey Results', 'wp-survey'); ?></h1>
            <?php if ($results && $results['total_votes'] > 0): ?>
            <div class="wps-results-header-actions">
                <a href="<?php echo admin_url('admin.php?page=wp-survey-add&id='.$selected_id); ?>" class="button button-secondary">✏️ <?php _e('Edit Survey','wp-survey'); ?></a>
                <a href="<?php echo admin_url('admin.php?page=wp-survey-analytics&survey_id='.$selected_id); ?>" class="button button-secondary">📊 <?php _e('Analytics','wp-survey'); ?></a>
                <?php if (!$ai_report): ?>
                <button type="button" id="wps-generate-report-btn" class="button button-secondary"
                    <?php echo !$ai_configured ? 'disabled title="'.__('Configure OpenRouter API key in Import/Export → AI Settings','wp-survey').'"' : ''; ?>>
                    🤖 <?php _e('Generate Report','wp-survey'); ?>
                </button>
                <?php else: ?>
                <button type="button" id="wps-delete-report-btn" class="button button-secondary" style="color:#dc2626; border-color:#fca5a5;">
                    🗑 <?php _e('Delete Report','wp-survey'); ?>
                </button>
                <button type="button" id="wps-regen-report-btn" class="button button-secondary">
                    🔄 <?php _e('Regenerate','wp-survey'); ?>
                </button>
                <?php endif; ?>
                <button type="button" id="wps-export-pdf-btn" class="button button-primary" style="display:flex;align-items:center;gap:6px;">
                    <span id="wps-pdf-icon">⬇️</span> <?php _e('Export PDF','wp-survey'); ?>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($surveys)): ?>
        <div class="wps-results-selector-bar">
            <form method="get" action="" class="wps-results-selector-form">
                <input type="hidden" name="page" value="wp-survey-results">
                <div class="wps-results-selector-inner">
                    <label class="wps-results-selector-label"><?php _e('Survey:','wp-survey'); ?></label>
                    <select name="survey_id" class="wps-results-select" onchange="this.form.submit()">
                        <?php foreach ($surveys as $s): ?>
                        <option value="<?php echo $s->id; ?>" <?php selected($selected_id, $s->id); ?>>
                            #<?php echo $s->id; ?> — <?php echo esc_html($s->title); ?>
                            (<?php echo $s->survey_type==='multi-question' ? 'Multi-Q' : 'Simple'; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <?php if (!$ai_configured): ?>
            <div style="margin-left:auto; padding:6px 12px; background:#fef3c7; border:1px solid #fde68a; border-radius:8px; font-size:12px; color:#92400e;">
                ⚠️ <a href="<?php echo admin_url('admin.php?page=wp-survey-import-export'); ?>" style="color:inherit; font-weight:600;"><?php _e('Set up AI Report settings','wp-survey'); ?></a> <?php _e('to enable report generation','wp-survey'); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Report generation status UI ────────────────────────────── -->
    <div id="wps-report-status-box" style="display:none; margin-bottom:20px;">
        <div class="wps-report-generating">
            <div class="wps-report-generating-spinner"></div>
            <div>
                <div style="font-weight:700; color:#1e1b4b; font-size:15px;"><?php _e('Generating AI Report…','wp-survey'); ?></div>
                <div id="wps-report-status-msg" style="font-size:13px; color:#6366f1; margin-top:4px;"><?php _e('Calling AI (this may take 30–90 seconds)…','wp-survey'); ?></div>
            </div>
        </div>
    </div>

    <?php if (empty($surveys)): ?>
    <div class="wps-results-empty">
        <div class="wps-results-empty-icon">📋</div>
        <h2><?php _e('No surveys found','wp-survey'); ?></h2>
        <p><?php _e('Create a survey first.','wp-survey'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=wp-survey-add'); ?>" class="button button-primary button-hero"><?php _e('Create Survey','wp-survey'); ?></a>
    </div>

    <?php elseif (!$results || $results['total_votes'] == 0): ?>
    <div class="wps-results-empty">
        <div class="wps-results-empty-icon">🗳️</div>
        <h2><?php _e('No votes yet','wp-survey'); ?></h2>
        <?php if ($results): ?>
        <p><strong><?php _e('Shortcode:','wp-survey'); ?></strong> <code>[wp_survey id="<?php echo $selected_id; ?>"]</code></p>
        <?php endif; ?>
    </div>

    <?php else:
        $survey      = $results['survey'];
        $multi_votes = !empty($survey->allow_multiple_votes);
        $colors      = ['#6366f1','#10b981','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#a855f7'];
        $date_from   = $first_vote ? date_i18n(get_option('date_format'), strtotime($first_vote)) : '—';
        $date_to     = $last_vote  ? date_i18n(get_option('date_format'), strtotime($last_vote))  : '—';
    ?>

    <!-- ══════════════════════════════════════════════════════════════
         PDF-ONLY COVER PAGE  (hidden on screen, shown during export)
    ═══════════════════════════════════════════════════════════════ -->
    <div id="wps-pdf-cover" style="display:none;">
        <div style="min-height:680px; background:linear-gradient(160deg,#0f172a 0%,#1e1b4b 45%,#312e81 100%); border-radius:16px; padding:48px 52px; color:#fff; display:flex; flex-direction:column; justify-content:space-between; box-sizing:border-box;">

            <!-- Top: logo + site -->
            <div style="display:flex; align-items:center; gap:16px; padding-bottom:32px; border-bottom:1px solid rgba(255,255,255,0.15);">
                <?php if ($site_logo): ?>
                <img src="<?php echo esc_url($site_logo); ?>" style="width:64px;height:64px;border-radius:12px;object-fit:cover;" crossorigin="anonymous">
                <?php else: ?>
                <div style="width:64px;height:64px;border-radius:12px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:28px;">📊</div>
                <?php endif; ?>
                <div>
                    <div style="font-size:16px; font-weight:700; color:#fff;"><?php echo esc_html($site_name); ?></div>
                    <div style="font-size:12px; color:#a5b4fc;"><?php echo esc_html($site_url); ?></div>
                </div>
            </div>

            <!-- Middle: title + subtitle -->
            <div style="flex:1; display:flex; flex-direction:column; justify-content:center; padding:40px 0;">
                <div style="font-size:11px; text-transform:uppercase; letter-spacing:3px; color:#818cf8; margin-bottom:16px; font-weight:600;">
                    <?php _e('Official Survey Report','wp-survey'); ?>
                </div>
                <h1 style="margin:0 0 20px; font-size:34px; font-weight:800; color:#fff; line-height:1.2; max-width:700px;">
                    <?php echo esc_html($survey->title); ?>
                </h1>
                <p style="color:#c7d2fe; font-size:15px; line-height:1.6; margin:0 0 32px; max-width:620px;">
                    <?php _e('Results &amp; Analysis Report','wp-survey'); ?> &mdash;
                    <?php echo sprintf(__('Data collected from %s to %s','wp-survey'), esc_html($date_from), esc_html($date_to)); ?>
                </p>

                <!-- Stats strip -->
                <div style="display:flex; gap:32px; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:36px; font-weight:800; color:#fff; line-height:1;"><?php echo number_format($results['total_votes']); ?></div>
                        <div style="font-size:12px; color:#818cf8; text-transform:uppercase; letter-spacing:1px; margin-top:4px;"><?php _e('Total Votes','wp-survey'); ?></div>
                    </div>
                    <div style="width:1px; background:rgba(255,255,255,0.15);"></div>
                    <div>
                        <div style="font-size:36px; font-weight:800; color:#fff; line-height:1;"><?php echo number_format($results['unique_voters']); ?></div>
                        <div style="font-size:12px; color:#818cf8; text-transform:uppercase; letter-spacing:1px; margin-top:4px;"><?php _e('Unique Voters','wp-survey'); ?></div>
                    </div>
                    <div style="width:1px; background:rgba(255,255,255,0.15);"></div>
                    <div>
                        <div style="font-size:36px; font-weight:800; color:#fff; line-height:1;"><?php echo count($results['questions']); ?></div>
                        <div style="font-size:12px; color:#818cf8; text-transform:uppercase; letter-spacing:1px; margin-top:4px;"><?php _e('Questions','wp-survey'); ?></div>
                    </div>
                    <?php if ($ai_report): ?>
                    <div style="width:1px; background:rgba(255,255,255,0.15);"></div>
                    <div>
                        <div style="font-size:15px; font-weight:700; color:#34d399; line-height:1;">✓ <?php _e('AI Analysis','wp-survey'); ?></div>
                        <div style="font-size:12px; color:#818cf8; text-transform:uppercase; letter-spacing:1px; margin-top:4px;"><?php _e('Included','wp-survey'); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bottom: generated date -->
            <div style="padding-top:24px; border-top:1px solid rgba(255,255,255,0.15); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:12px; color:#818cf8;">
                    <?php _e('Generated','wp-survey'); ?>: <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format')); ?>
                </div>
                <?php if ($multi_votes): ?>
                <div style="font-size:11px; background:rgba(245,158,11,0.2); color:#fde68a; padding:4px 12px; border-radius:20px; border:1px solid rgba(245,158,11,0.3);">
                    ⚠️ <?php _e('Multiple votes enabled','wp-survey'); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         AI REPORT (shown on screen + in PDF)
    ═══════════════════════════════════════════════════════════════ -->
    <?php if ($ai_report): ?>
    <div id="wps-ai-report-wrap">
        <!-- Report rendered by JS from REPORT_DATA below -->
    </div>
    <?php else: ?>
    <div id="wps-ai-report-wrap" style="display:none;"></div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════════
         SURVEY RESULTS CARDS
    ═══════════════════════════════════════════════════════════════ -->
    <?php if ($multi_votes): ?>
    <div class="wps-results-notice warning">
        ⚠️ <?php _e('This survey has <strong>Multiple Votes</strong> enabled.','wp-survey'); ?>
    </div>
    <?php endif; ?>

    <div id="wps-survey-results-content">

    <div class="wps-results-summary">
        <div class="wps-results-summary-item">
            <span class="wps-results-summary-number"><?php echo number_format($results['total_votes']); ?></span>
            <span class="wps-results-summary-label"><?php _e('Total Votes','wp-survey'); ?></span>
        </div>
        <div class="wps-results-summary-divider"></div>
        <div class="wps-results-summary-item">
            <span class="wps-results-summary-number"><?php echo number_format($results['unique_voters']); ?></span>
            <span class="wps-results-summary-label"><?php _e('Unique Voters','wp-survey'); ?></span>
        </div>
        <div class="wps-results-summary-divider"></div>
        <div class="wps-results-summary-item">
            <span class="wps-results-summary-number"><?php echo count($results['questions']); ?></span>
            <span class="wps-results-summary-label"><?php _e('Question(s)','wp-survey'); ?></span>
        </div>
        <div class="wps-results-summary-divider"></div>
        <div class="wps-results-summary-item">
            <span class="wps-results-summary-number" style="font-size:14px; font-weight:500;">
                <?php echo $multi_votes
                    ? '<span style="color:#d97706">⚠️ '.__('Unlimited','wp-survey').'</span>'
                    : '<span style="color:#059669">🔒 '.__('One per person','wp-survey').'</span>'; ?>
            </span>
            <span class="wps-results-summary-label"><?php _e('Voting Mode','wp-survey'); ?></span>
        </div>
    </div>

    <?php foreach ($results['questions'] as $qi => $qdata):
        $winner = !empty($qdata['choices']) ? $qdata['choices'][0] : null;
    ?>
    <div class="wps-results-question-card">
        <div class="wps-results-question-head">
            <?php if ($survey->survey_type === 'multi-question'): ?>
            <span class="wps-results-q-badge">Q<?php echo $qi+1; ?></span>
            <?php endif; ?>
            <div class="wps-results-q-text"><?php echo esc_html($qdata['question']); ?></div>
            <div class="wps-results-q-meta">
                <span class="wps-results-q-total"><?php echo number_format($qdata['total_votes']); ?> <?php _e('votes','wp-survey'); ?></span>
                <?php if ($winner): ?>
                <span class="wps-results-q-leader">🏆 <?php echo esc_html($winner['title']); ?> (<?php echo $winner['percentage']; ?>%)</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="wps-results-question-body">
            <?php if (!empty($qdata['choices'])): ?>
            <div class="wps-results-donut-wrap">
                <canvas id="donut-<?php echo $qdata['id'].'-'.$qi; ?>" width="220" height="220"></canvas>
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
            <div class="wps-results-rows">
                <?php foreach ($qdata['choices'] as $ci => $choice):
                    $color     = $colors[$ci % count($colors)];
                    $is_winner = ($ci === 0);
                ?>
                <div class="wps-results-row <?php echo $is_winner ? 'is-winner' : ''; ?>">
                    <?php if ($choice['image_url']): ?>
                    <div class="wps-results-row-img"><img src="<?php echo esc_url($choice['image_url']); ?>" alt=""></div>
                    <?php endif; ?>
                    <div class="wps-results-row-body">
                        <div class="wps-results-row-title-row">
                            <span class="wps-results-row-rank" style="background:<?php echo $color; ?>"><?php echo $is_winner ? '🏆' : ($ci+1); ?></span>
                            <span class="wps-results-row-title"><?php echo esc_html($choice['title']); ?></span>
                            <?php if ($is_winner): ?><span class="wps-results-winner-badge"><?php _e('Leading','wp-survey'); ?></span><?php endif; ?>
                        </div>
                        <div class="wps-results-bar-row">
                            <div class="wps-results-bar-track">
                                <div class="wps-results-bar-fill" style="width:<?php echo $choice['percentage']; ?>%; background:<?php echo $color; ?>;" data-pct="<?php echo $choice['percentage']; ?>"></div>
                            </div>
                            <span class="wps-results-bar-pct" style="color:<?php echo $color; ?>"><?php echo $choice['percentage']; ?>%</span>
                        </div>
                    </div>
                    <div class="wps-results-row-count" style="border-color:<?php echo $color; ?>">
                        <strong><?php echo number_format($choice['votes']); ?></strong>
                        <span><?php _e('votes','wp-survey'); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    </div><!-- #wps-survey-results-content -->

    <!-- PDF footer -->
    <div id="wps-pdf-footer" style="display:none; text-align:center; font-size:11px; color:#9ca3af; border-top:1px solid #e5e7eb; padding:14px 0; margin-top:8px;">
        <?php echo esc_html($site_name); ?> · <?php echo esc_html($site_url); ?> · <?php _e('Generated','wp-survey'); ?> <?php echo date_i18n(get_option('date_format').' '.get_option('time_format')); ?>
    </div>

    <?php endif; // end has votes ?>

</div><!-- .wps-results-wrap -->

<?php
// Pass report data to JS
$js_report   = $ai_report ? wp_json_encode($ai_report) : 'null';
$js_survey_id = intval($selected_id);
$js_title    = $results ? wp_json_encode($results['survey']->title) : '"survey"';
?>

<?php if (!empty($results) && $results['total_votes'] > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var COLORS = ['#6366f1','#10b981','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#a855f7'];

    <?php foreach ($results['questions'] as $qi => $qdata):
        if (empty($qdata['choices'])) continue;
        $cid    = 'donut-'.$qdata['id'].'-'.$qi;
        $labels = wp_json_encode(array_column($qdata['choices'],'title'));
        $votes  = wp_json_encode(array_column($qdata['choices'],'votes'));
        $cnt    = count($qdata['choices']);
    ?>
    (function(){
        var el = document.getElementById('<?php echo $cid; ?>');
        if (!el) return;
        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    data: <?php echo $votes; ?>,
                    backgroundColor: COLORS.slice(0, <?php echo $cnt; ?>),
                    borderWidth: 3, borderColor: '#fff', hoverOffset: 6,
                }]
            },
            options: {
                responsive: false, cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(ctx) {
                        var t = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                        return ' '+ctx.label+': '+ctx.parsed+' ('+(t>0?((ctx.parsed/t)*100).toFixed(1):0)+'%)';
                    }}}
                }
            }
        });
    })();
    <?php endforeach; ?>

    // Animate bars
    document.querySelectorAll('.wps-results-bar-fill').forEach(function(b){
        var w = b.dataset.pct+'%'; b.style.width='0%';
        requestAnimationFrame(function(){ setTimeout(function(){ b.style.width=w; }, 100); });
    });

    // ── Report JS data ────────────────────────────────────────────────
    var REPORT_DATA  = <?php echo $js_report; ?>;
    var SURVEY_ID    = <?php echo $js_survey_id; ?>;
    var SURVEY_TITLE = <?php echo $js_title; ?>;
    var REPORT_NONCE = '<?php echo esc_js(wp_create_nonce('wps_report_nonce')); ?>';

    // ── Generate Report — 4 chained AJAX calls ───────────────────────
    var SECTIONS = [
        { key: 'overview',     label: '<?php echo esc_js(__('Writing executive overview…','wp-survey')); ?>',       step: '1/4' },
        { key: 'questions',    label: '<?php echo esc_js(__('Analysing all questions…','wp-survey')); ?>',          step: '2/4' },
        { key: 'cross',        label: '<?php echo esc_js(__('Finding cross-question patterns…','wp-survey')); ?>', step: '3/4' },
        { key: 'conclusions',  label: '<?php echo esc_js(__('Writing conclusions…','wp-survey')); ?>',              step: '4/4' },
    ];

    function setStatus(msg, step) {
        var el = document.getElementById('wps-report-status-msg');
        if (el) el.innerHTML = step
            ? '<strong style="color:#312e81">Step ' + step + '</strong> — ' + msg
            : msg;
    }

    function runSection(surveyId, sectionIndex) {
        var sec = SECTIONS[sectionIndex];
        setStatus(sec.label, sec.step);

        jQuery.ajax({
            url:     wpSurvey.ajaxurl,
            method:  'POST',
            timeout: 120000,   // 2 min per section — plenty for one AI call
            data: {
                action:    'wps_generate_report',
                nonce:     REPORT_NONCE,
                survey_id: surveyId,
                section:   sec.key,
            },
            success: function(res) {
                if (!res.success) {
                    onGenerateError(res.data && res.data.message ? res.data.message : '<?php echo esc_js(__('Unknown error','wp-survey')); ?>', sec.key);
                    return;
                }

                if (res.data.done) {
                    // All 4 sections complete
                    setStatus('<?php echo esc_js(__('Report complete! Reloading…','wp-survey')); ?>', null);
                    setTimeout(function(){ location.reload(); }, 800);
                } else {
                    // Move to next section
                    runSection(surveyId, sectionIndex + 1);
                }
            },
            error: function(xhr, status) {
                var msg = status === 'timeout'
                    ? '<?php echo esc_js(__('Section timed out. Your server may be slow — try again.','wp-survey')); ?>'
                    : '<?php echo esc_js(__('Network error on section','wp-survey')); ?> ' + sec.key;
                onGenerateError(msg, sec.key);
            }
        });
    }

    function startGenerate() {
        var box  = document.getElementById('wps-report-status-box');
        var genB = document.getElementById('wps-generate-report-btn');
        var regB = document.getElementById('wps-regen-report-btn');
        if (box)  box.style.display = 'block';
        if (genB) genB.disabled = true;
        if (regB) regB.disabled = true;
        setStatus('<?php echo esc_js(__('Starting…','wp-survey')); ?>', null);
        runSection(SURVEY_ID, 0);
    }

    function onGenerateError(msg, section) {
        var box  = document.getElementById('wps-report-status-box');
        var genB = document.getElementById('wps-generate-report-btn');
        var regB = document.getElementById('wps-regen-report-btn');
        if (box)  box.style.display = 'none';
        if (genB) genB.disabled = false;
        if (regB) regB.disabled = false;
        alert('❌ Error on section "' + section + '": ' + msg);
    }

    var genBtn   = document.getElementById('wps-generate-report-btn');
    var regenBtn = document.getElementById('wps-regen-report-btn');
    var delBtn   = document.getElementById('wps-delete-report-btn');

    if (genBtn)   genBtn.addEventListener('click', startGenerate);
    if (regenBtn) regenBtn.addEventListener('click', function() {
        if (!confirm('<?php echo esc_js(__('Regenerate the report? The existing report will be replaced.','wp-survey')); ?>')) return;
        startGenerate();
    });
    if (delBtn) {
        delBtn.addEventListener('click', function() {
            if (!confirm('<?php echo esc_js(__('Delete this report?','wp-survey')); ?>')) return;
            jQuery.post(wpSurvey.ajaxurl, { action:'wps_delete_report', nonce:REPORT_NONCE, survey_id:SURVEY_ID }, function(res) {
                if (res.success) location.reload();
            });
        });
    }

    // ── Render report HTML (used when report arrives without reload) ──
    function renderReport(report) {
        var wrap = document.getElementById('wps-ai-report-wrap');
        if (!wrap || !report) return;
        wrap.innerHTML = buildReportHTML(report);
        wrap.style.display = '';
    }

    function buildReportHTML(r) {
        var o = r.overview || {}, q = r.questions || {}, cr = r.cross || {}, co = r.conclusions || {};
        var meta = r._meta || {};
        var html = '<div class="wps-report-container">';

        // Report header bar
        html += '<div class="wps-report-topbar">';
        html += '<div class="wps-report-topbar-left"><span class="wps-report-topbar-icon">🤖</span><strong><?php echo esc_js(__('AI Analysis Report','wp-survey')); ?></strong>';
        if (meta.generated_at) html += ' <span class="wps-report-topbar-date"><?php echo esc_js(__('Generated','wp-survey')); ?>: '+meta.generated_at+'</span>';
        if (meta.model) html += ' · <span class="wps-report-topbar-model">'+meta.model+'</span>';
        html += '</div></div>';

        // Section 1: Overview
        if (o.executive_summary || (o.key_findings && o.key_findings.length)) {
            html += '<div class="wps-report-section">';
            html += '<div class="wps-report-section-title"><span class="wps-report-section-num">01</span><?php echo esc_js(__('Executive Summary','wp-survey')); ?></div>';
            html += '<div class="wps-report-section-body">';
            if (o.executive_summary) {
                o.executive_summary.split('\n\n').forEach(function(p){ if(p.trim()) html += '<p>'+p.trim()+'</p>'; });
            }
            if (o.participation_note) html += '<p class="wps-report-note">📊 '+o.participation_note+'</p>';
            if (o.key_findings && o.key_findings.length) {
                html += '<div class="wps-report-findings-title"><?php echo esc_js(__('Key Findings','wp-survey')); ?></div>';
                html += '<div class="wps-report-findings">';
                o.key_findings.forEach(function(f, i) {
                    html += '<div class="wps-report-finding"><div class="wps-report-finding-num">'+(i+1)+'</div><div>'+f+'</div></div>';
                });
                html += '</div>';
            }
            html += '</div></div>';
        }

        // Section 2: Question Analysis
        if (q.questions && q.questions.length) {
            html += '<div class="wps-report-section">';
            html += '<div class="wps-report-section-title"><span class="wps-report-section-num">02</span><?php echo esc_js(__('Question-by-Question Analysis','wp-survey')); ?></div>';
            html += '<div class="wps-report-section-body">';
            q.questions.forEach(function(qd, qi) {
                html += '<div class="wps-report-q-block">';
                html += '<div class="wps-report-q-header">';
                html += '<span class="wps-report-q-num">Q'+(qi+1)+'</span>';
                html += '<div class="wps-report-q-title">'+escHtml(qd.question || '')+'</div>';
                if (qd.leading_choice) {
                    html += '<div class="wps-report-q-leader">🏆 '+escHtml(qd.leading_choice)+(qd.leading_pct?' ('+qd.leading_pct+')':'')+'</div>';
                }
                html += '</div>';
                if (qd.analysis) {
                    qd.analysis.split('\n\n').forEach(function(p){ if(p.trim()) html += '<p>'+p.trim()+'</p>'; });
                }
                if (qd.notable) html += '<div class="wps-report-notable">💡 '+qd.notable+'</div>';
                html += '</div>';
            });
            html += '</div></div>';
        }

        // Section 3: Cross-Analysis
        if (cr.patterns || cr.correlations) {
            html += '<div class="wps-report-section">';
            html += '<div class="wps-report-section-title"><span class="wps-report-section-num">03</span><?php echo esc_js(__('Cross-Question Patterns','wp-survey')); ?></div>';
            html += '<div class="wps-report-section-body">';
            if (cr.patterns) cr.patterns.split('\n\n').forEach(function(p){ if(p.trim()) html += '<p>'+p.trim()+'</p>'; });
            if (cr.voter_segments) { html += '<h4><?php echo esc_js(__('Voter Segments','wp-survey')); ?></h4>'; cr.voter_segments.split('\n\n').forEach(function(p){ if(p.trim()) html += '<p>'+p.trim()+'</p>'; }); }
            if (cr.correlations && cr.correlations.length) {
                html += '<div class="wps-report-findings">';
                cr.correlations.forEach(function(c){ html += '<div class="wps-report-correlation-item">🔗 '+c+'</div>'; });
                html += '</div>';
            }
            html += '</div></div>';
        }

        // Section 4: Conclusions
        if (co.main_conclusion || co.recommendations) {
            html += '<div class="wps-report-section wps-report-conclusions">';
            html += '<div class="wps-report-section-title"><span class="wps-report-section-num">04</span><?php echo esc_js(__('Conclusions & Recommendations','wp-survey')); ?></div>';
            html += '<div class="wps-report-section-body">';
            if (co.main_conclusion) co.main_conclusion.split('\n\n').forEach(function(p){ if(p.trim()) html += '<p>'+p.trim()+'</p>'; });
            if (co.implications) { html += '<h4><?php echo esc_js(__('Implications','wp-survey')); ?></h4>'; co.implications.split('\n\n').forEach(function(p){ if(p.trim()) html += '<p>'+p.trim()+'</p>'; }); }
            if (co.recommendations && co.recommendations.length) {
                html += '<div class="wps-report-findings-title"><?php echo esc_js(__('Recommendations','wp-survey')); ?></div>';
                html += '<div class="wps-report-findings">';
                co.recommendations.forEach(function(rec, i) {
                    html += '<div class="wps-report-finding wps-report-finding-green"><div class="wps-report-finding-num" style="background:#059669">'+(i+1)+'</div><div>'+rec+'</div></div>';
                });
                html += '</div>';
            }
            html += '</div></div>';
        }

        html += '</div>';
        return html;
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Render on load if report already exists
    if (REPORT_DATA) {
        var wrap = document.getElementById('wps-ai-report-wrap');
        if (wrap) {
            wrap.innerHTML = buildReportHTML(REPORT_DATA);
            wrap.style.display = '';
        }
    }

    // ── PDF Export ───────────────────────────────────────────────────
    var pdfBtn = document.getElementById('wps-export-pdf-btn');
    if (!pdfBtn) return;

    var MARGIN   = 10, PAGE_W = 210, PAGE_H = 297, USABLE_W = 190, USABLE_H = 277, SCALE = 2, CAP_W = 1060;

    var h2cOpts = {
        scale: SCALE, useCORS: true, allowTaint: true, backgroundColor: '#ffffff',
        logging: false, windowWidth: CAP_W,
        onclone: function(d) {
            d.querySelectorAll('.wps-results-bar-fill').forEach(function(b){
                b.style.transition='none'; b.style.width=b.dataset.pct+'%';
            });
        }
    };

    function captureEl(el) {
        return html2canvas(el, h2cOpts).then(function(c) {
            return { img: c.toDataURL('image/png'), mmH: (c.height / c.width) * USABLE_W };
        });
    }

    pdfBtn.addEventListener('click', function() {
        pdfBtn.disabled = true;
        document.getElementById('wps-pdf-icon').innerHTML = '<span style="display:inline-block;animation:wps-spin 0.8s linear infinite;">⏳</span>';

        var cover  = document.getElementById('wps-pdf-cover');
        var footer = document.getElementById('wps-pdf-footer');
        if (cover)  cover.style.display  = 'block';
        if (footer) footer.style.display = 'block';

        // Build capture list
        var sections = [];
        if (cover) sections.push({ el: cover, type: 'cover' });

        // AI report sections (each top-level section individually)
        var reportSections = document.querySelectorAll('.wps-report-section');
        if (reportSections.length) {
            var reportBar = document.querySelector('.wps-report-topbar');
            if (reportBar) sections.push({ el: reportBar, type: 'normal' });
            reportSections.forEach(function(s) { sections.push({ el: s, type: 'normal' }); });
        }

        // Summary bar
        var sumBar = document.querySelector('.wps-results-summary');
        if (sumBar) sections.push({ el: sumBar, type: 'normal' });

        // Question cards
        document.querySelectorAll('.wps-results-question-card').forEach(function(c) {
            sections.push({ el: c, type: 'normal' });
        });

        if (footer) sections.push({ el: footer, type: 'footer' });

        requestAnimationFrame(function(){ setTimeout(function() {
            var chain = Promise.resolve([]);
            sections.forEach(function(s) {
                chain = chain.then(function(res) {
                    return captureEl(s.el).then(function(r) {
                        res.push({ img: r.img, mmH: r.mmH, type: s.type });
                        return res;
                    });
                });
            });

            chain.then(function(captured) {
                var { jsPDF } = window.jspdf;
                var pdf  = new jsPDF({ orientation:'portrait', unit:'mm', format:'a4' });
                var curY = MARGIN;
                var pageNum = 0;

                captured.forEach(function(item) {
                    var imgH = item.mmH;
                    var GAP  = (pageNum === 0 && item.type !== 'cover') ? 5 : (pageNum > 0 ? 5 : 0);

                    // Cover gets its own full page
                    if (item.type === 'cover') {
                        if (pageNum > 0) { pdf.addPage(); curY = MARGIN; }
                        var h = Math.min(imgH, USABLE_H);
                        pdf.addImage(item.img, 'PNG', MARGIN, curY, USABLE_W, h);
                        pdf.addPage(); curY = MARGIN; pageNum++;
                        return;
                    }

                    // If item is taller than a full page, scale to fit
                    if (imgH > USABLE_H) {
                        if (curY > MARGIN) { pdf.addPage(); curY = MARGIN; }
                        pdf.addImage(item.img, 'PNG', MARGIN, curY, USABLE_W, USABLE_H);
                        pdf.addPage(); curY = MARGIN; pageNum++;
                        return;
                    }

                    // New page if won't fit
                    if (curY + GAP + imgH > PAGE_H - MARGIN) {
                        pdf.addPage(); curY = MARGIN; GAP = 0;
                    }

                    pdf.addImage(item.img, 'PNG', MARGIN, curY + GAP, USABLE_W, imgH);
                    curY += GAP + imgH;
                    pageNum++;
                });

                var fn = (SURVEY_TITLE.replace(/[^a-z0-9]/gi,'-').toLowerCase()+'-report-'+new Date().toISOString().slice(0,10)+'.pdf');
                pdf.save(fn);

            }).catch(function(e){ console.error(e); alert('PDF failed: '+e.message); })
              .finally(function() {
                if (cover)  cover.style.display  = 'none';
                if (footer) footer.style.display = 'none';
                pdfBtn.disabled = false;
                document.getElementById('wps-pdf-icon').innerHTML = '⬇️';
            });
        }, 200); });
    });
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<?php endif; ?>

<style>
/* ── Results wrap & existing styles ──────────────────────────────── */
.wps-results-wrap { max-width: 1100px; }
.wps-results-header { margin: 20px 0 24px; }
.wps-results-title-row { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
.wps-results-title-row h1 { margin:0; font-size:24px; }
.wps-results-header-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.wps-results-selector-bar { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:14px 18px; box-shadow:0 1px 3px rgba(0,0,0,0.06); display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.wps-results-selector-inner { display:flex; align-items:center; gap:12px; }
.wps-results-selector-label { font-weight:600; font-size:13px; color:#374151; white-space:nowrap; }
.wps-results-select { flex:1; max-width:500px; height:38px; padding:4px 12px; border-radius:6px; border:1px solid #d1d5db; font-size:14px; }
.wps-results-notice { padding:12px 16px; border-radius:8px; font-size:13px; margin-bottom:20px; }
.wps-results-notice.warning { background:#fef3c7; border:1px solid #fcd34d; color:#92400e; }
.wps-results-summary { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 28px; display:flex; align-items:center; gap:0; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.06); flex-wrap:wrap; }
.wps-results-summary-item { flex:1; min-width:120px; display:flex; flex-direction:column; align-items:center; gap:4px; padding:8px 0; }
.wps-results-summary-number { font-size:28px; font-weight:700; color:#111827; line-height:1; }
.wps-results-summary-label  { font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px; font-weight:500; }
.wps-results-summary-divider { width:1px; background:#e5e7eb; align-self:stretch; margin:0 4px; }
.wps-results-question-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; margin-bottom:24px; box-shadow:0 1px 4px rgba(0,0,0,0.06); overflow:hidden; }
.wps-results-question-head { background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%); padding:18px 22px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.wps-results-q-badge { background:rgba(255,255,255,0.2); color:#fff; font-size:12px; font-weight:700; padding:4px 10px; border-radius:20px; white-space:nowrap; }
.wps-results-q-text { flex:1; font-size:16px; font-weight:600; color:#fff; line-height:1.4; }
.wps-results-q-meta { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.wps-results-q-total { background:rgba(255,255,255,0.15); color:#e0e7ff; font-size:12px; font-weight:500; padding:4px 10px; border-radius:20px; }
.wps-results-q-leader { background:rgba(245,158,11,0.25); color:#fde68a; font-size:12px; font-weight:600; padding:4px 10px; border-radius:20px; }
.wps-results-question-body { display:flex; gap:0; align-items:flex-start; }
.wps-results-donut-wrap { padding:24px 20px; border-right:1px solid #e5e7eb; display:flex; flex-direction:column; align-items:center; gap:16px; min-width:260px; flex-shrink:0; }
.wps-results-donut-legend { width:100%; }
.wps-results-legend-item { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
.wps-results-legend-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.wps-results-legend-name { flex:1; font-size:12px; color:#374151; }
.wps-results-legend-pct  { font-size:12px; font-weight:700; color:#6b7280; min-width:36px; text-align:right; }
.wps-results-rows { flex:1; padding:16px 20px; display:flex; flex-direction:column; gap:12px; }
.wps-results-row { display:flex; align-items:center; gap:14px; padding:12px 14px; border-radius:10px; background:#f9fafb; border:1px solid #f3f4f6; transition:box-shadow 0.2s; }
.wps-results-row:hover { box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.wps-results-row.is-winner { background:linear-gradient(to right,#fefce8,#f9fafb); border-color:#fde68a; }
.wps-results-row-img { width:56px; height:56px; border-radius:8px; overflow:hidden; flex-shrink:0; }
.wps-results-row-img img { width:100%; height:100%; object-fit:cover; display:block; }
.wps-results-row-body { flex:1; min-width:0; }
.wps-results-row-title-row { display:flex; align-items:center; gap:8px; margin-bottom:8px; }
.wps-results-row-rank { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:#fff; flex-shrink:0; }
.wps-results-row-title { font-size:14px; font-weight:600; color:#111827; flex:1; }
.wps-results-winner-badge { background:#fef08a; color:#713f12; font-size:11px; font-weight:700; padding:2px 8px; border-radius:20px; white-space:nowrap; }
.wps-results-bar-row { display:flex; align-items:center; gap:10px; }
.wps-results-bar-track { flex:1; height:8px; background:#e5e7eb; border-radius:10px; overflow:hidden; }
.wps-results-bar-fill { height:100%; border-radius:10px; transition:width 0.8s cubic-bezier(0.4,0,0.2,1); }
.wps-results-bar-pct { font-size:13px; font-weight:700; min-width:40px; text-align:right; }
.wps-results-row-count { text-align:center; min-width:64px; padding:8px 10px; border:2px solid #e5e7eb; border-radius:8px; flex-shrink:0; }
.wps-results-row-count strong { display:block; font-size:20px; color:#111827; line-height:1; }
.wps-results-row-count span   { font-size:11px; color:#9ca3af; }
.wps-results-empty { text-align:center; padding:80px 20px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; margin-top:20px; }
.wps-results-empty-icon { font-size:80px; margin-bottom:16px; }
.wps-results-empty h2 { font-size:22px; margin-bottom:8px; }
.wps-results-empty p  { color:#6b7280; font-size:15px; margin-bottom:16px; }

/* ── Report generating spinner ────────────────────────────────── */
.wps-report-generating { background:#f0f4ff; border:1px solid #c7d2fe; border-radius:12px; padding:20px 24px; display:flex; align-items:center; gap:16px; }
.wps-report-generating-spinner { width:32px; height:32px; border:3px solid #c7d2fe; border-top-color:#6366f1; border-radius:50%; animation:wps-spin 0.8s linear infinite; flex-shrink:0; }
@keyframes wps-spin { to { transform:rotate(360deg); } }

/* ── AI Report Container ──────────────────────────────────────── */
.wps-report-container { margin-bottom:28px; }

.wps-report-topbar {
    background:linear-gradient(135deg,#1e1b4b,#4338ca);
    color:#fff; padding:12px 20px; border-radius:12px 12px 0 0;
    display:flex; align-items:center; justify-content:space-between;
    font-size:13px;
}
.wps-report-topbar-icon { font-size:18px; margin-right:8px; }
.wps-report-topbar-left { display:flex; align-items:center; gap:6px; }
.wps-report-topbar-date  { color:#a5b4fc; font-size:11px; }
.wps-report-topbar-model { color:#a5b4fc; font-size:11px; font-family:monospace; }

.wps-report-section {
    background:#fff;
    border:1px solid #e5e7eb;
    border-top:none;
    padding:24px 28px;
}
.wps-report-section:last-child { border-radius:0 0 12px 12px; }

.wps-report-section-title {
    display:flex; align-items:center; gap:12px;
    font-size:17px; font-weight:700; color:#1e1b4b;
    margin-bottom:16px; padding-bottom:12px;
    border-bottom:2px solid #ede9fe;
}
.wps-report-section-num {
    background:linear-gradient(135deg,#6366f1,#4338ca);
    color:#fff; font-size:11px; font-weight:800;
    padding:3px 8px; border-radius:20px;
    letter-spacing:0.5px;
}
.wps-report-section-body p { font-size:14px; line-height:1.7; color:#374151; margin:0 0 12px; }
.wps-report-section-body h4 { font-size:14px; font-weight:700; color:#1e1b4b; margin:16px 0 8px; }
.wps-report-note { background:#f0f4ff; border-left:3px solid #6366f1; padding:10px 14px; border-radius:0 8px 8px 0; color:#3730a3 !important; font-style:italic; }

/* Key findings */
.wps-report-findings-title { font-size:13px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.5px; margin:16px 0 10px; }
.wps-report-findings { display:flex; flex-direction:column; gap:8px; }
.wps-report-finding { display:flex; gap:12px; align-items:flex-start; background:#f8f7ff; border:1px solid #ede9fe; border-radius:8px; padding:10px 14px; font-size:13px; color:#374151; line-height:1.5; }
.wps-report-finding-num { background:linear-gradient(135deg,#6366f1,#4338ca); color:#fff; font-size:11px; font-weight:800; min-width:22px; height:22px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.wps-report-finding-green { background:#f0fdf4; border-color:#a7f3d0; }
.wps-report-correlation-item { background:#f0f4ff; border:1px solid #c7d2fe; border-radius:8px; padding:8px 14px; font-size:13px; color:#3730a3; }

/* Question analysis blocks */
.wps-report-q-block { margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid #f3f4f6; }
.wps-report-q-block:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
.wps-report-q-header { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
.wps-report-q-num { background:linear-gradient(135deg,#6366f1,#4338ca); color:#fff; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; white-space:nowrap; }
.wps-report-q-title { font-size:14px; font-weight:700; color:#1e1b4b; flex:1; }
.wps-report-q-leader { background:#fef3c7; color:#92400e; font-size:12px; font-weight:600; padding:3px 10px; border-radius:20px; white-space:nowrap; }
.wps-report-notable { background:#fef9ec; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; font-size:13px; color:#92400e; margin-top:10px; line-height:1.5; }

/* Conclusions section accent */
.wps-report-conclusions { border-left:4px solid #059669 !important; }
.wps-report-conclusions .wps-report-section-title { border-bottom-color:#a7f3d0; color:#065f46; }
.wps-report-conclusions .wps-report-section-num { background:linear-gradient(135deg,#059669,#047857); }

@media (max-width:820px) {
    .wps-results-question-body { flex-direction:column; }
    .wps-results-donut-wrap { border-right:none; border-bottom:1px solid #e5e7eb; }
    .wps-results-summary { gap:12px; }
    .wps-results-summary-divider { display:none; }
    .wps-report-section { padding:16px 18px; }
}
#wps-export-pdf-btn, #wps-generate-report-btn, #wps-regen-report-btn, #wps-delete-report-btn { cursor:pointer; }
</style>
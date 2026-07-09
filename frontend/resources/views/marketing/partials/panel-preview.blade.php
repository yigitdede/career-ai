<div class="trajectory-stage" aria-label="{{ __('marketing.preview.aria') }}">
    <div class="trajectory-stage__grid" aria-hidden="true"></div>
    <svg class="trajectory-stage__routes" viewBox="0 0 600 620" fill="none" aria-hidden="true">
        <path d="M58 146C136 66 220 102 270 168C334 252 432 190 544 102"/>
        <path d="M64 506C150 424 204 484 278 520C362 562 456 516 548 430"/>
        <path d="M84 284C164 260 180 190 282 198C398 208 408 336 538 330"/>
    </svg>
    <span class="trajectory-stage__packet trajectory-stage__packet--one" aria-hidden="true"></span>
    <span class="trajectory-stage__packet trajectory-stage__packet--two" aria-hidden="true"></span>

    <div class="trajectory-node trajectory-node--cv" aria-hidden="true">
        <span class="trajectory-node__icon">
            <i data-lucide="file-text" aria-hidden="true"></i>
        </span>
        <span><small>{{ __('marketing.preview.node_input') }}</small>{{ __('marketing.preview.node_cv') }}</span>
    </div>
    <div class="trajectory-node trajectory-node--role" aria-hidden="true">
        <span class="trajectory-node__icon trajectory-node__icon--aqua">
            <i data-lucide="target" aria-hidden="true"></i>
        </span>
        <span><small>{{ __('marketing.preview.node_target') }}</small>{{ __('marketing.preview.node_role') }}</span>
    </div>
    <div class="trajectory-node trajectory-node--plan" aria-hidden="true">
        <span class="trajectory-node__icon trajectory-node__icon--amber">
            <i data-lucide="clipboard-list" aria-hidden="true"></i>
        </span>
        <span><small>{{ __('marketing.preview.node_output') }}</small>{{ __('marketing.preview.node_plan') }}</span>
    </div>

    <div class="career-dashboard">
        <div class="career-dashboard__bar">
            <div class="career-dashboard__brand">
                <span class="brand-mark brand-mark--tiny" aria-hidden="true"><span></span><span></span><span></span></span>
                <span>CareerTalent</span>
            </div>
            <div class="career-dashboard__window" aria-hidden="true"><span></span><span></span><span></span></div>
        </div>

        <div class="career-dashboard__body">
            <div class="career-dashboard__heading">
                <div>
                    <span>{{ __('marketing.preview.welcome') }}</span>
                    <strong>{{ __('marketing.preview.role') }}</strong>
                </div>
                <span class="career-dashboard__live"><i></i>{{ __('marketing.preview.live') }}</span>
            </div>

            <div class="career-dashboard__metrics">
                <div class="career-score">
                    <div class="career-score__ring" aria-label="{{ __('marketing.preview.readiness') }}: 67%">
                        <svg viewBox="0 0 92 92" aria-hidden="true">
                            <circle cx="46" cy="46" r="37"></circle>
                            <circle cx="46" cy="46" r="37"></circle>
                        </svg>
                        <strong data-readiness-value="67">67<span>%</span></strong>
                    </div>
                    <div>
                        <span>{{ __('marketing.preview.readiness') }}</span>
                        <small>{{ __('marketing.preview.from_gap') }}</small>
                    </div>
                </div>
                <div class="career-gap">
                    <span>{{ __('marketing.preview.gap_label') }}</span>
                    <strong>SQL · Tableau · Python</strong>
                    <small>{{ __('marketing.preview.gap_hint') }}</small>
                </div>
            </div>

            <div class="career-dashboard__week">
                <div class="career-dashboard__week-head">
                    <div><span>{{ __('marketing.preview.this_week') }}</span><strong>{{ __('marketing.preview.weekly_tasks') }}</strong></div>
                    <b>{{ __('marketing.preview.tasks') }}</b>
                </div>
                <ul>
                    <li class="is-done"><span><i></i>{{ __('marketing.preview.task_1') }}</span><small>{{ __('marketing.preview.done') }}</small></li>
                    <li><span><i></i>{{ __('marketing.preview.task_2') }}</span><small>60%</small></li>
                    <li><span><i></i>{{ __('marketing.preview.task_3') }}</span><small>{{ __('marketing.preview.queued') }}</small></li>
                </ul>
            </div>

            <div class="career-dashboard__insight">
                <span aria-hidden="true">
                    <i data-lucide="lightbulb"></i>
                </span>
                <p><small>{{ __('marketing.preview.ai_note') }}</small>{{ __('marketing.preview.ai_insight') }}</p>
            </div>
        </div>
    </div>
</div>

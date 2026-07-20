@php
    $permissionModules = [
        'dashboard' => ['label' => __('admin.nav.dashboard'), 'permissions' => ['dashboard.view'], 'required' => true],
        'organizations' => ['label' => __('admin.nav.organizations'), 'permissions' => ['organizations.view', 'organizations.write', 'organizations.delete']],
        'career_data' => ['label' => __('career-data.title'), 'permissions' => ['career_data.view', 'career_data.write', 'career_data.delete']],
        'students' => ['label' => __('admin.modules.students.title'), 'permissions' => ['students.view', 'students.write', 'students.delete']],
        'readiness' => ['label' => __('admin.modules.readiness.title'), 'permissions' => ['readiness.view']],
        'skill_passport' => ['label' => __('admin.modules.skill-passport.title'), 'permissions' => ['skill_passport.view']],
        'job_radar' => ['label' => __('admin.modules.job-radar.title'), 'permissions' => ['job_radar.view']],
        'applications' => ['label' => __('admin.modules.applications.title'), 'permissions' => ['applications.view', 'applications.write', 'applications.delete']],
        'interviews' => ['label' => __('admin.modules.interviews.title'), 'permissions' => ['interviews.view', 'interviews.write', 'interviews.delete']],
    ];
    $availablePermissions = array_values($permissionKeys ?? []);
    $selectedPermissionValues = array_values($selectedPermissions ?? []);
    $knownPermissions = [];
@endphp

<div class="space-y-2" data-permission-selector="{{ $permissionSelectorId }}">
    @foreach ($permissionModules as $moduleKey => $module)
        @php
            $modulePermissions = array_values(array_intersect($module['permissions'], $availablePermissions));
            $knownPermissions = array_merge($knownPermissions, $modulePermissions);
        @endphp
        @continue($modulePermissions === [])

        @if (count($modulePermissions) === 1)
            @php($permission = $modulePermissions[0])
            @php($isRequired = (bool) ($module['required'] ?? false))
            <label class="flex min-h-12 items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm transition hover:border-emerald-500/40 dark:border-slate-800"
                data-permission-single="{{ $permission }}">
                <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                    @checked($isRequired || in_array($permission, $selectedPermissionValues, true))
                    @disabled($isRequired)>
                @if ($isRequired)<input type="hidden" name="permissions[]" value="{{ $permission }}">@endif
                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $module['label'] }}</span>
                <span class="ml-auto text-xs text-slate-500">{{ $permissionLabels[$permission] ?? $permission }}</span>
            </label>
        @else
            @php($moduleId = $permissionSelectorId.'-'.$moduleKey)
            <details class="group rounded-xl border border-slate-200 transition open:border-emerald-500/40 dark:border-slate-800"
                data-permission-module="{{ $moduleKey }}">
                <summary class="flex min-h-12 cursor-pointer list-none items-center gap-3 px-4 py-3 marker:hidden">
                    <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-3" data-permission-toggle-label>
                        <input type="checkbox" data-permission-module-toggle aria-controls="{{ $moduleId }}">
                        <span class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $module['label'] }}</span>
                    </label>
                    <span class="text-xs tabular-nums text-slate-500" data-permission-count></span>
                    <span class="text-slate-400 transition-transform group-open:rotate-180" aria-hidden="true">⌄</span>
                </summary>
                <div id="{{ $moduleId }}" class="grid gap-2 border-t border-slate-200 p-3 sm:grid-cols-3 dark:border-slate-800" data-permission-options>
                    @foreach ($modulePermissions as $permission)
                        <label class="flex items-start gap-2 rounded-lg bg-slate-50 px-3 py-2.5 text-sm dark:bg-slate-900/60">
                            <input class="mt-0.5" type="checkbox" name="permissions[]" value="{{ $permission }}"
                                @checked(in_array($permission, $selectedPermissionValues, true))
                                data-permission-option>
                            <span>{{ $permissionLabels[$permission] ?? $permission }}</span>
                        </label>
                    @endforeach
                </div>
            </details>
        @endif
    @endforeach

    @foreach (array_values(array_diff($availablePermissions, $knownPermissions)) as $permission)
        <label class="flex min-h-12 items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm dark:border-slate-800"
            data-permission-single="{{ $permission }}">
            <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $selectedPermissionValues, true))>
            <span>{{ $permissionLabels[$permission] ?? $permission }}</span>
        </label>
    @endforeach
</div>

@once
    <script>
        (() => {
            const initPermissionSelectors = (root = document) => {
                root.querySelectorAll('[data-permission-module]').forEach((module) => {
                    if (module.dataset.permissionReady === 'true') return;

                    const toggle = module.querySelector('[data-permission-module-toggle]');
                    const options = [...module.querySelectorAll('[data-permission-option]')];
                    const count = module.querySelector('[data-permission-count]');
                    if (!toggle || options.length === 0) return;

                    const sync = () => {
                        const selected = options.filter((option) => option.checked).length;
                        toggle.checked = selected === options.length;
                        toggle.indeterminate = selected > 0 && selected < options.length;
                        toggle.setAttribute('aria-checked', toggle.indeterminate ? 'mixed' : String(toggle.checked));
                        if (count) count.textContent = `${selected}/${options.length}`;
                    };

                    toggle.addEventListener('click', (event) => event.stopPropagation());
                    toggle.addEventListener('change', () => {
                        options.forEach((option) => { option.checked = toggle.checked; });
                        sync();
                    });
                    options.forEach((option) => option.addEventListener('change', sync));
                    module.dataset.permissionReady = 'true';
                    sync();
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => initPermissionSelectors(), { once: true });
            } else {
                initPermissionSelectors();
            }
            document.addEventListener('livewire:navigated', () => initPermissionSelectors());
        })();
    </script>
@endonce

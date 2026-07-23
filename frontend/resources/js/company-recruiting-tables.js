function matchesQuery(needle, values) {
    if (!needle) {
        return true;
    }

    return values.some((value) => String(value || '').toLowerCase().includes(needle));
}

export function companyApplications(config) {
    const applications = Array.isArray(config.applications) ? config.applications : [];

    return {
        applications,
        query: '',
        stageFilter: 'all',
        positionFilter: 'all',
        stageOptions: Array.isArray(config.stageOptions) ? config.stageOptions : [],
        positionOptions: Array.isArray(config.positionOptions) ? config.positionOptions : [],
        labels: config.labels || {},
        isModalOpen: false,
        selectedCandidate: null,
        activeModalTab: 'profile',

        openCandidateModal(candidate) {
            this.selectedCandidate = candidate;
            this.activeModalTab = 'profile';
            this.isModalOpen = true;
        },

        closeCandidateModal() {
            this.isModalOpen = false;
            this.selectedCandidate = null;
        },

        setModalTab(tabName) {
            this.activeModalTab = tabName;
        },

        openCvPreview(candidate) {
            const targetCandidate = candidate || this.selectedCandidate;
            const cvDocId = targetCandidate?.cv_document_id || targetCandidate?.application_snapshot?.cv?.id;
            
            if (cvDocId) {
                window.open(`/panel/cv-documents/${cvDocId}/download`, '_blank');
            } else if (targetCandidate?.application_snapshot?.cv) {
                const cvData = targetCandidate.application_snapshot.cv;
                const win = window.open('', '_blank');
                if (win) {
                    win.document.write(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>${cvData.display_name || 'Özgeçmiş Önizleme'}</title>
                            <style>
                                body { font-family: system-ui, -apple-system, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; color: #0f172a; line-height: 1.6; }
                                h1 { font-size: 24px; border-bottom: 2px solid #10b981; padding-bottom: 10px; margin-bottom: 20px; }
                                .meta { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
                                pre { background: #0f172a; color: #f8fafc; padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 13px; }
                            </style>
                        </head>
                        <body>
                            <h1>${targetCandidate.candidate_name || 'Aday'} — CV Sürüm Özeti</h1>
                            <div class="meta">
                                <p><strong>E-posta:</strong> ${targetCandidate.candidate_email || '—'}</p>
                                <p><strong>CV Sürüm Adı:</strong> ${cvData.display_name || 'Özgeçmiş'}</p>
                                <p><strong>Dil:</strong> ${(cvData.language || 'tr').toUpperCase()}</p>
                            </div>
                            <h3>CV İçerik Yapısı (Snapshot)</h3>
                            <pre>${JSON.stringify(cvData, null, 2)}</pre>
                        </body>
                        </html>
                    `);
                    win.document.close();
                }
            } else {
                alert('Adayın gösterilebilir CV dokümanı bulunamadı.');
            }
        },

        filteredApplications() {
            const needle = this.query.trim().toLowerCase();

            return this.applications.filter((application) => {
                if (this.stageFilter !== 'all' && application.current_stage !== this.stageFilter) {
                    return false;
                }
                if (this.positionFilter !== 'all' && application.position_title !== this.positionFilter) {
                    return false;
                }

                return matchesQuery(needle, [
                    application.candidate_name,
                    application.candidate_email,
                    application.position_title,
                ]);
            });
        },

        isVisible(application) {
            return this.filteredApplications().some((item) => item.id === application.id);
        },

        visibleCount() {
            return this.filteredApplications().length;
        },
    };
}

export function companyAssessments(config) {
    const assessments = Array.isArray(config.assessments) ? config.assessments : [];

    return {
        assessments,
        query: '',
        statusFilter: 'all',
        positionFilter: 'all',
        statusOptions: Array.isArray(config.statusOptions) ? config.statusOptions : [],
        positionOptions: Array.isArray(config.positionOptions) ? config.positionOptions : [],
        labels: config.labels || {},

        filteredAssessments() {
            const needle = this.query.trim().toLowerCase();

            return this.assessments.filter((assessment) => {
                if (this.statusFilter !== 'all' && assessment.status !== this.statusFilter) {
                    return false;
                }
                if (this.positionFilter !== 'all' && assessment.position_title !== this.positionFilter) {
                    return false;
                }

                return matchesQuery(needle, [
                    assessment.candidate_name,
                    assessment.position_title,
                    assessment.title,
                ]);
            });
        },

        isVisible(assessment) {
            return this.filteredAssessments().some((item) => item.id === assessment.id);
        },

        visibleCount() {
            return this.filteredAssessments().length;
        },
    };
}

export function companyPositions(config) {
    const positions = Array.isArray(config.positions) ? config.positions : [];

    return {
        positions,
        query: '',
        statusFilter: 'all',
        workplaceFilter: 'all',
        statusOptions: Array.isArray(config.statusOptions) ? config.statusOptions : [],
        workplaceOptions: Array.isArray(config.workplaceOptions) ? config.workplaceOptions : [],
        labels: config.labels || {},
        showUrlTemplate: config.showUrlTemplate || '',

        filteredPositions() {
            const needle = this.query.trim().toLowerCase();

            return this.positions.filter((position) => {
                if (this.statusFilter !== 'all' && position.status !== this.statusFilter) {
                    return false;
                }
                if (this.workplaceFilter !== 'all' && position.workplace_type !== this.workplaceFilter) {
                    return false;
                }

                return matchesQuery(needle, [
                    position.title,
                    position.department,
                    position.location,
                    position.recruiter_name,
                    position.technical_manager_name,
                ]);
            });
        },

        isVisible(position) {
            return this.filteredPositions().some((item) => item.id === position.id);
        },

        visibleCount() {
            return this.filteredPositions().length;
        },

        showUrl(positionId) {
            return this.showUrlTemplate.replace('__ID__', String(positionId));
        },

        goToPosition(position) {
            window.location.href = this.showUrl(position.id);
        },
    };
}

export function companyShareLinks(labels = {}) {
    return {
        copied: false,
        confirmOpen: false,
        pending: null,
        labels,
        copy(value) {
            navigator.clipboard.writeText(value).then(() => {
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, 1600);
            });
        },
        openToggleConfirm(link) {
            this.pending = link;
            this.confirmOpen = true;
        },
        closeConfirm() {
            this.confirmOpen = false;
            this.pending = null;
        },
        confirmTitle() {
            if (!this.pending) {
                return '';
            }

            return this.pending.is_active ? this.labels.deactivate_title : this.labels.activate_title;
        },
        confirmMessage() {
            if (!this.pending) {
                return '';
            }

            const template = this.pending.is_active ? this.labels.confirm_deactivate : this.labels.confirm_activate;

            return (template || '').replace(':label', this.pending.label);
        },
        submitToggle() {
            if (!this.pending) {
                return;
            }

            this.$refs.toggleForm.action = this.pending.action;
            this.$refs.toggleIsActive.value = this.pending.is_active ? '0' : '1';
            this.$refs.toggleForm.submit();
        },
    };
}

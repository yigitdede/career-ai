"""Restore engagement foreign-key delete rules."""

from alembic import op


revision = "20260717_07"
down_revision = "20260717_06"
branch_labels = None
depends_on = None


def _replace_foreign_key(
    table: str,
    constraint: str,
    local_column: str,
    remote_table: str,
    remote_column: str,
    *,
    ondelete: str | None,
) -> None:
    op.drop_constraint(constraint, table, type_="foreignkey")
    op.create_foreign_key(
        constraint,
        table,
        remote_table,
        [local_column],
        [remote_column],
        ondelete=ondelete,
    )


def upgrade() -> None:
    if op.get_bind().dialect.name != "postgresql":
        return
    _replace_foreign_key(
        "personal_tasks",
        "personal_tasks_target_id_fkey",
        "target_id",
        "career_targets",
        "id",
        ondelete="SET NULL",
    )
    _replace_foreign_key(
        "job_applications",
        "job_applications_job_id_fkey",
        "job_id",
        "job_opportunities",
        "id",
        ondelete="SET NULL",
    )


def downgrade() -> None:
    if op.get_bind().dialect.name != "postgresql":
        return
    _replace_foreign_key(
        "job_applications",
        "job_applications_job_id_fkey",
        "job_id",
        "job_opportunities",
        "id",
        ondelete=None,
    )
    _replace_foreign_key(
        "personal_tasks",
        "personal_tasks_target_id_fkey",
        "target_id",
        "career_targets",
        "id",
        ondelete=None,
    )

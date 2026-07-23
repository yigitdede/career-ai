"""Track AI builder drafts for uploaded CV documents.

Revision ID: 20260722_19
Revises: 20260721_18
"""

from alembic import op
import sqlalchemy as sa


revision = "20260722_19"
down_revision = "20260721_18"
branch_labels = None
depends_on = None


def upgrade() -> None:
    with op.batch_alter_table("cv_documents") as batch_op:
        batch_op.add_column(
            sa.Column("builder_draft_status", sa.String(24), nullable=False, server_default="not_requested")
        )
        batch_op.add_column(sa.Column("builder_draft_error", sa.Text(), nullable=True))
        batch_op.add_column(sa.Column("builder_draft_analysis_id", sa.String(36), nullable=True))
        batch_op.create_index("ix_cv_documents_builder_draft_status", ["builder_draft_status"])
        batch_op.create_index("ix_cv_documents_builder_draft_analysis_id", ["builder_draft_analysis_id"])

    op.execute(
        "UPDATE cv_documents SET builder_draft_status = 'ready' "
        "WHERE kind = 'generated' AND builder_data IS NOT NULL"
    )


def downgrade() -> None:
    with op.batch_alter_table("cv_documents") as batch_op:
        batch_op.drop_index("ix_cv_documents_builder_draft_analysis_id")
        batch_op.drop_index("ix_cv_documents_builder_draft_status")
        batch_op.drop_column("builder_draft_analysis_id")
        batch_op.drop_column("builder_draft_error")
        batch_op.drop_column("builder_draft_status")

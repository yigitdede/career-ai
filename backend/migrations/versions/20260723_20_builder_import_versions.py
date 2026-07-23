"""Link imported builder drafts to persistent CV versions.

Revision ID: 20260723_20
Revises: 20260722_19
"""

from alembic import op
import sqlalchemy as sa


revision = "20260723_20"
down_revision = "20260722_19"
branch_labels = None
depends_on = None


def upgrade() -> None:
    with op.batch_alter_table("candidate_cv_versions") as batch_op:
        batch_op.add_column(sa.Column("source_document_id", sa.String(36), nullable=True))
        batch_op.create_index(
            "ix_candidate_cv_versions_source_document_id",
            ["source_document_id"],
        )
        batch_op.create_index(
            "uq_candidate_cv_versions_source_document_language",
            ["source_document_id", "language"],
            unique=True,
        )
        batch_op.create_foreign_key(
            "fk_candidate_cv_versions_source_document_id",
            "cv_documents",
            ["source_document_id"],
            ["id"],
            ondelete="SET NULL",
        )


def downgrade() -> None:
    with op.batch_alter_table("candidate_cv_versions") as batch_op:
        batch_op.drop_constraint(
            "fk_candidate_cv_versions_source_document_id",
            type_="foreignkey",
        )
        batch_op.drop_index("uq_candidate_cv_versions_source_document_language")
        batch_op.drop_index("ix_candidate_cv_versions_source_document_id")
        batch_op.drop_column("source_document_id")

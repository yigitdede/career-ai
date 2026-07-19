"""İşveren/ajans tenant çekirdeği ve çoklu kurum üyelikleri."""

from alembic import op
import sqlalchemy as sa


revision = "20260719_09"
down_revision = "20260719_08"
branch_labels = None
depends_on = None


def upgrade() -> None:
    op.create_table(
        "organizations",
        sa.Column("id", sa.String(length=36), primary_key=True),
        sa.Column("name", sa.String(length=160), nullable=False),
        sa.Column("slug", sa.String(length=100), nullable=False),
        sa.Column("organization_type", sa.String(length=20), nullable=False),
        sa.Column("size_band", sa.String(length=20), nullable=False),
        sa.Column("status", sa.String(length=20), nullable=False, server_default="onboarding"),
        sa.Column("plan_code", sa.String(length=20), nullable=False, server_default="pilot"),
        sa.Column("billing_email", sa.String(length=255), nullable=False),
        sa.Column("website", sa.String(length=2048), nullable=True),
        sa.Column("settings", sa.JSON(), nullable=False, server_default=sa.text("'{}'")),
        sa.Column("created_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.CheckConstraint("organization_type IN ('employer', 'agency')", name="ck_organizations_type"),
        sa.CheckConstraint("size_band IN ('smb', 'mid_market', 'enterprise')", name="ck_organizations_size_band"),
        sa.CheckConstraint("status IN ('onboarding', 'active', 'suspended', 'closed')", name="ck_organizations_status"),
        sa.CheckConstraint("plan_code IN ('pilot', 'starter', 'growth', 'agency', 'enterprise')", name="ck_organizations_plan_code"),
        sa.UniqueConstraint("slug", name="uq_organizations_slug"),
    )
    op.create_index("ix_organizations_slug", "organizations", ["slug"])
    op.create_index("ix_organizations_organization_type", "organizations", ["organization_type"])
    op.create_index("ix_organizations_size_band", "organizations", ["size_band"])
    op.create_index("ix_organizations_status", "organizations", ["status"])
    op.create_index("ix_organizations_plan_code", "organizations", ["plan_code"])

    op.create_table(
        "organization_memberships",
        sa.Column("id", sa.String(length=36), primary_key=True),
        sa.Column("organization_id", sa.String(length=36), nullable=False),
        sa.Column("user_id", sa.Integer(), nullable=False),
        sa.Column("role", sa.String(length=24), nullable=False),
        sa.Column("status", sa.String(length=20), nullable=False, server_default="invited"),
        sa.Column("created_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.CheckConstraint("role IN ('owner', 'admin', 'recruiter', 'hiring_manager', 'viewer')", name="ck_organization_memberships_role"),
        sa.CheckConstraint("status IN ('invited', 'active', 'suspended')", name="ck_organization_memberships_status"),
        sa.ForeignKeyConstraint(["organization_id"], ["organizations.id"], name="fk_organization_memberships_organization", ondelete="CASCADE"),
        sa.ForeignKeyConstraint(["user_id"], ["users.id"], name="fk_organization_memberships_user", ondelete="CASCADE"),
        sa.UniqueConstraint("organization_id", "user_id", name="uq_organization_memberships_organization_user"),
    )
    op.create_index("ix_organization_memberships_organization_id", "organization_memberships", ["organization_id"])
    op.create_index("ix_organization_memberships_user_id", "organization_memberships", ["user_id"])
    op.create_index("ix_organization_memberships_role", "organization_memberships", ["role"])
    op.create_index("ix_organization_memberships_status", "organization_memberships", ["status"])


def downgrade() -> None:
    raise RuntimeError("Recruiting tenant migration is forward-only; organization data must not be dropped.")

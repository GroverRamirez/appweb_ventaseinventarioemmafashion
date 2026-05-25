<?php

namespace App\Support;

readonly class TeamPermissions
{
    public function __construct(
        // Gestión del equipo
        public bool $canUpdateTeam,
        public bool $canDeleteTeam,
        public bool $canAddMember,
        public bool $canUpdateMember,
        public bool $canRemoveMember,
        public bool $canCreateInvitation,
        public bool $canCancelInvitation,
        // Módulos de negocio
        public bool $canViewDashboard,
        public bool $canViewProduct,
        public bool $canManageProduct,
        public bool $canViewCategory,
        public bool $canManageCategory,
        public bool $canViewSupplier,
        public bool $canManageSupplier,
        public bool $canViewPurchase,
        public bool $canCreatePurchase,
        public bool $canViewSale,
        public bool $canCreateSale,
        public bool $canViewReport,
    ) {
        //
    }
}

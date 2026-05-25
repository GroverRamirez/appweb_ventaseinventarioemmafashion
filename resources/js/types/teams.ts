export type TeamRole = 'owner' | 'member';

export type Team = {
    id: number;
    name: string;
    slug: string;
    isPersonal: boolean;
    role?: TeamRole;
    roleLabel?: string;
    isCurrent?: boolean;
};

export type TeamMember = {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    role: TeamRole;
    role_label: string;
};

export type TeamInvitation = {
    code: string;
    email: string;
    role: TeamRole;
    role_label: string;
    created_at: string;
};

export type TeamPermissions = {
    // Gestión del equipo
    canUpdateTeam: boolean;
    canDeleteTeam: boolean;
    canAddMember: boolean;
    canUpdateMember: boolean;
    canRemoveMember: boolean;
    canCreateInvitation: boolean;
    canCancelInvitation: boolean;
    // Módulos de negocio
    canViewDashboard: boolean;
    canViewProduct: boolean;
    canManageProduct: boolean;
    canViewCategory: boolean;
    canManageCategory: boolean;
    canViewSupplier: boolean;
    canManageSupplier: boolean;
    canViewPurchase: boolean;
    canCreatePurchase: boolean;
    canViewSale: boolean;
    canCreateSale: boolean;
    canViewReport: boolean;
};

export type RoleOption = {
    value: TeamRole;
    label: string;
};

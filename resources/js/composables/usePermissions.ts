import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { TeamPermissions, TeamRole, Team } from '@/types/teams';

export function usePermissions() {
    const page = usePage();

    const permissions = computed<TeamPermissions | null>(() => {
        return page.props.permissions ?? null;
    });

    const currentTeam = computed<Team | null>(() => {
        return page.props.currentTeam ?? null;
    });

    const userRole = computed<TeamRole | undefined>(() => {
        return currentTeam.value?.role;
    });

    const isOwner = computed(() => {
        return userRole.value === 'owner';
    });

    const isMember = computed(() => {
        return userRole.value === 'member';
    });

    const hasPermission = (permission: keyof TeamPermissions): boolean => {
        return permissions.value?.[permission] ?? false;
    };

    const canViewDashboard = () => hasPermission('canViewDashboard');
    const canViewProduct = () => hasPermission('canViewProduct');
    const canManageProduct = () => hasPermission('canManageProduct');
    const canViewCategory = () => hasPermission('canViewCategory');
    const canManageCategory = () => hasPermission('canManageCategory');
    const canViewSupplier = () => hasPermission('canViewSupplier');
    const canManageSupplier = () => hasPermission('canManageSupplier');
    const canViewPurchase = () => hasPermission('canViewPurchase');
    const canCreatePurchase = () => hasPermission('canCreatePurchase');
    const canViewSale = () => hasPermission('canViewSale');
    const canCreateSale = () => hasPermission('canCreateSale');
    const canViewReport = () => hasPermission('canViewReport');
    const canUpdateTeam = () => hasPermission('canUpdateTeam');
    const canDeleteTeam = () => hasPermission('canDeleteTeam');
    const canAddMember = () => hasPermission('canAddMember');
    const canUpdateMember = () => hasPermission('canUpdateMember');
    const canRemoveMember = () => hasPermission('canRemoveMember');
    const canCreateInvitation = () => hasPermission('canCreateInvitation');
    const canCancelInvitation = () => hasPermission('canCancelInvitation');

    const hasRole = (...roles: TeamRole[]): boolean => {
        return roles.includes(userRole.value as TeamRole);
    };

    return {
        permissions,
        currentTeam,
        userRole,
        isOwner,
        isMember,
        hasPermission,
        canViewDashboard,
        canViewProduct,
        canManageProduct,
        canViewCategory,
        canManageCategory,
        canViewSupplier,
        canManageSupplier,
        canViewPurchase,
        canCreatePurchase,
        canViewSale,
        canCreateSale,
        canViewReport,
        canUpdateTeam,
        canDeleteTeam,
        canAddMember,
        canUpdateMember,
        canRemoveMember,
        canCreateInvitation,
        canCancelInvitation,
        hasRole,
    };
}
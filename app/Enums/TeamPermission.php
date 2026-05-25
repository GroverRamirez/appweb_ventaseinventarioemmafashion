<?php

namespace App\Enums;

enum TeamPermission: string
{
    // ---- Gestión del equipo (solo Propietaria) ----
    case UpdateTeam = 'team:update';
    case DeleteTeam = 'team:delete';

    case AddMember = 'member:add';
    case UpdateMember = 'member:update';
    case RemoveMember = 'member:remove';

    case CreateInvitation = 'invitation:create';
    case CancelInvitation = 'invitation:cancel';

    // ---- Módulos de negocio ----
    case ViewDashboard = 'dashboard:view';

    case ViewProduct = 'product:view';
    case ManageProduct = 'product:manage';

    case ViewCategory = 'category:view';
    case ManageCategory = 'category:manage';

    case ViewSupplier = 'supplier:view';
    case ManageSupplier = 'supplier:manage';

    case ViewPurchase = 'purchase:view';
    case CreatePurchase = 'purchase:create';

    case ViewSale = 'sale:view';
    case CreateSale = 'sale:create';

    case ViewReport = 'report:view';
}

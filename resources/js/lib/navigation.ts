import type { LucideIcon } from 'lucide-react';
import {
    Activity,
    BarChart3,
    Boxes,
    Building2,
    Cloud,
    FileText,
    FolderKanban,
    Globe2,
    HardDrive,
    LayoutDashboard,
    LifeBuoy,
    Plug,
    Server,
    Settings,
    Shield,
    Wallet,
} from 'lucide-react';

export type NavItem = {
    name: string;
    href: string;
    icon?: LucideIcon;
    permission?: string;
    children?: NavItem[];
};

export type NavSection = {
    id: string;
    label?: string;
    items: NavItem[];
};

export const mainNavigationSections: NavSection[] = [
    {
        id: 'home',
        items: [{ name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard, permission: 'dashboard.view' }],
    },
    {
        id: 'workspace',
        label: 'Workspace',
        items: [
            {
                name: 'Clients',
                href: '/clients',
                icon: Building2,
                permission: 'clients.view',
                children: [
                    { name: 'All Clients', href: '/clients', permission: 'clients.view' },
                    { name: 'Contacts', href: '/contacts', permission: 'contacts.view' },
                    { name: 'Client Groups', href: '/client-groups', permission: 'client_groups.view' },
                ],
            },
            {
                name: 'Projects',
                href: '/projects',
                icon: FolderKanban,
                permission: 'projects.view',
                children: [
                    { name: 'All Projects', href: '/projects', permission: 'projects.view' },
                    { name: 'Active', href: '/projects?status=active', permission: 'projects.view' },
                    { name: 'Completed', href: '/projects?status=completed', permission: 'projects.view' },
                    { name: 'Templates', href: '/project-templates', permission: 'project_templates.view' },
                ],
            },
            {
                name: 'Services',
                href: '/service-types',
                icon: Boxes,
                permission: 'service_catalog.view',
                children: [
                    { name: 'Service Catalog', href: '/service-types', permission: 'service_catalog.view' },
                    { name: 'Categories', href: '/service-categories', permission: 'service_catalog.view' },
                    { name: 'Client Services', href: '/client-services', permission: 'client_services.view' },
                    { name: 'Renewals', href: '/renewals', permission: 'renewals.view' },
                ],
            },
        ],
    },
    {
        id: 'assets',
        label: 'Assets',
        items: [
            {
                name: 'Applications',
                href: '/applications',
                icon: Cloud,
                permission: 'applications.view',
                children: [
                    { name: 'Applications', href: '/applications', permission: 'applications.view' },
                    { name: 'Environments', href: '/environments', permission: 'environments.view' },
                    { name: 'Deployments', href: '/deployments', permission: 'deployments.view' },
                ],
            },
            {
                name: 'Infrastructure',
                href: '/servers',
                icon: Server,
                permission: 'infrastructure.view',
                children: [
                    { name: 'Servers', href: '/servers', permission: 'infrastructure.view' },
                    { name: 'Cloud', href: '/cloud-resources', permission: 'infrastructure.view' },
                    { name: 'Providers', href: '/providers', permission: 'providers.view' },
                ],
            },
            {
                name: 'Domains',
                href: '/domains',
                icon: Globe2,
                permission: 'domains.view',
                children: [
                    { name: 'Domains', href: '/domains', permission: 'domains.view' },
                    { name: 'SSL Certificates', href: '/ssl-certificates', permission: 'domains.view' },
                ],
            },
            {
                name: 'Hosting',
                href: '/hosting-accounts',
                icon: HardDrive,
                permission: 'infrastructure.view',
            },
            {
                name: 'Tasks',
                href: '/tasks',
                icon: Activity,
                permission: 'tasks.view',
            },
        ],
    },
    {
        id: 'operations',
        label: 'Operations',
        items: [
            {
                name: 'Support',
                href: '/tickets',
                icon: LifeBuoy,
                permission: 'tickets.view',
                children: [
                    { name: 'Tickets', href: '/tickets', permission: 'tickets.view' },
                    { name: 'Knowledge Base', href: '/knowledge-articles', permission: 'knowledge.view' },
                    { name: 'SLA Policies', href: '/sla-policies', permission: 'sla.view' },
                    { name: 'Notifications', href: '/notifications', permission: 'notifications.view' },
                ],
            },
            {
                name: 'Finance',
                href: '/finance/reports',
                icon: Wallet,
                permission: 'finance_reports.view',
                children: [
                    { name: 'Reports', href: '/finance/reports', permission: 'finance_reports.view' },
                    {
                        name: 'Revenue',
                        href: '/financial-entries?entry_type=revenue',
                        permission: 'financial_entries.view',
                    },
                    {
                        name: 'Costs',
                        href: '/financial-entries?entry_type=cost',
                        permission: 'financial_entries.view',
                    },
                    { name: 'Invoices', href: '/invoices', permission: 'invoices.view' },
                    { name: 'Discount reasons', href: '/discount-reasons', permission: 'invoices.view' },
                    { name: 'Renewals', href: '/renewals', permission: 'renewals.view' },
                ],
            },
            {
                name: 'Documents',
                href: '/documents',
                icon: FileText,
                permission: 'documents.view',
            },
            {
                name: 'Reports',
                href: '/finance/reports',
                icon: BarChart3,
                permission: 'finance_reports.view',
            },
            {
                name: 'Timeline',
                href: '/timeline',
                icon: Activity,
                permission: 'timeline.view',
            },
        ],
    },
    {
        id: 'platform',
        label: 'Platform',
        items: [
            {
                name: 'Integrations',
                href: '/integrations',
                icon: Plug,
                permission: 'integrations.view',
            },
            {
                name: 'Settings',
                href: '/settings',
                icon: Settings,
                permission: 'settings.view',
                children: [
                    { name: 'Overview', href: '/settings', permission: 'settings.view' },
                    { name: 'Company', href: '/settings/organization', permission: 'settings.view' },
                    { name: 'Users', href: '/settings/users', permission: 'users.view' },
                    { name: 'Teams', href: '/settings/teams', permission: 'teams.view' },
                    { name: 'Roles', href: '/settings/roles', permission: 'roles.view' },
                    { name: 'Audit log', href: '/settings/audit', permission: 'audit.view' },
                ],
            },
        ],
    },
];

export const mainNavigation: NavItem[] = mainNavigationSections.flatMap((section) => section.items);

export const commandLinks: Array<{ name: string; href: string; icon: LucideIcon; keywords?: string }> = [
    { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { name: 'Clients', href: '/clients', icon: Building2 },
    { name: 'Projects', href: '/projects', icon: FolderKanban },
    { name: 'Services', href: '/service-types', icon: Boxes },
    { name: 'Applications', href: '/applications', icon: Cloud },
    { name: 'Infrastructure', href: '/servers', icon: Server },
    { name: 'Domains', href: '/domains', icon: Globe2 },
    { name: 'Hosting', href: '/hosting-accounts', icon: HardDrive },
    { name: 'Tasks', href: '/tasks', icon: Activity },
    { name: 'Tickets', href: '/tickets', icon: LifeBuoy },
    { name: 'Finance reports', href: '/finance/reports', icon: Wallet },
    { name: 'Documents', href: '/documents', icon: FileText },
    { name: 'Integrations', href: '/integrations', icon: Plug },
    { name: 'Settings', href: '/settings', icon: Settings },
    { name: 'Roles', href: '/settings/roles', icon: Shield },
];

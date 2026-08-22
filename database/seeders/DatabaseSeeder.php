<?php

namespace Database\Seeders;

use App\Domains\Crm\Enums\ClientStatus;
use App\Domains\Crm\Models\Client;
use App\Domains\Crm\Models\ClientGroup;
use App\Domains\Crm\Models\Contact;
use App\Domains\Identity\Models\Department;
use App\Domains\Identity\Models\Organization;
use App\Domains\Identity\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'teams.view',
            'teams.create',
            'teams.update',
            'teams.delete',
            'roles.view',
            'roles.update',
            'audit.view',
            'settings.view',
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'contacts.view',
            'contacts.create',
            'contacts.update',
            'contacts.delete',
            'client_groups.view',
            'client_groups.create',
            'client_groups.update',
            'client_groups.delete',
            'service_catalog.view',
            'service_catalog.create',
            'service_catalog.update',
            'service_catalog.delete',
            'client_services.view',
            'client_services.create',
            'client_services.update',
            'client_services.delete',
            'renewals.view',
            'renewals.update',
            'financial_entries.view',
            'financial_entries.create',
            'financial_entries.update',
            'financial_entries.delete',
            'financial_entries.post',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'finance_reports.view',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'project_templates.view',
            'project_templates.create',
            'project_templates.update',
            'project_templates.delete',
            'applications.view',
            'applications.create',
            'applications.update',
            'applications.delete',
            'environments.view',
            'environments.create',
            'environments.update',
            'environments.delete',
            'deployments.view',
            'deployments.create',
            'deployments.update',
            'deployments.delete',
            'domains.view',
            'domains.create',
            'domains.update',
            'domains.delete',
            'infrastructure.view',
            'infrastructure.create',
            'infrastructure.update',
            'infrastructure.delete',
            'providers.view',
            'providers.create',
            'providers.update',
            'providers.delete',
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'knowledge.view',
            'knowledge.create',
            'knowledge.update',
            'knowledge.delete',
            'sla.view',
            'sla.create',
            'sla.update',
            'sla.delete',
            'notifications.view',
            'notifications.update',
            'timeline.view',
            'integrations.view',
            'integrations.create',
            'integrations.update',
            'integrations.delete',
            'integrations.sync',
            'automation.view',
            'automation.create',
            'automation.update',
            'automation.delete',
            'automation.run',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'Super Admin',
            'Administrator',
            'Management',
            'Project Manager',
            'Tech Lead',
            'Developer',
            'DevOps',
            'Support',
            'Finance',
            'Sales',
            'Client',
        ];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        Role::findByName('Super Admin', 'web')->syncPermissions(Permission::all());
        Role::findByName('Administrator', 'web')->syncPermissions(Permission::all());

        $crmPermissions = [
            'dashboard.view',
            'settings.view',
            'clients.view',
            'clients.create',
            'clients.update',
            'contacts.view',
            'contacts.create',
            'contacts.update',
            'client_groups.view',
        ];

        Role::findByName('Management', 'web')->syncPermissions([
            ...$crmPermissions,
            'clients.delete',
            'contacts.delete',
            'client_groups.create',
            'client_groups.update',
            'client_groups.delete',
            'service_catalog.view',
            'service_catalog.create',
            'service_catalog.update',
            'client_services.view',
            'client_services.create',
            'client_services.update',
            'client_services.delete',
            'renewals.view',
            'renewals.update',
            'financial_entries.view',
            'finance_reports.view',
            'invoices.view',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'project_templates.view',
            'project_templates.create',
            'project_templates.update',
            'users.view',
            'teams.view',
            'audit.view',
            'automation.view',
            'automation.run',
        ]);

        Role::findByName('Sales', 'web')->syncPermissions([
            ...$crmPermissions,
            'client_groups.create',
            'client_groups.update',
            'service_catalog.view',
            'client_services.view',
            'client_services.create',
            'client_services.update',
            'renewals.view',
            'invoices.view',
            'projects.view',
            'tasks.view',
        ]);

        Role::findByName('Finance', 'web')->syncPermissions([
            'dashboard.view',
            'settings.view',
            'clients.view',
            'service_catalog.view',
            'client_services.view',
            'client_services.update',
            'renewals.view',
            'renewals.update',
            'financial_entries.view',
            'financial_entries.create',
            'financial_entries.update',
            'financial_entries.delete',
            'financial_entries.post',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'finance_reports.view',
            'projects.view',
        ]);

        Role::findByName('Project Manager', 'web')->syncPermissions([
            'dashboard.view',
            'settings.view',
            'clients.view',
            'clients.update',
            'contacts.view',
            'contacts.create',
            'contacts.update',
            'client_groups.view',
            'service_catalog.view',
            'client_services.view',
            'client_services.create',
            'client_services.update',
            'renewals.view',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'project_templates.view',
            'project_templates.create',
            'project_templates.update',
            'applications.view',
            'applications.create',
            'applications.update',
            'applications.delete',
            'environments.view',
            'environments.create',
            'environments.update',
            'deployments.view',
            'deployments.create',
            'domains.view',
            'domains.create',
            'domains.update',
            'infrastructure.view',
            'infrastructure.create',
            'infrastructure.update',
            'providers.view',
            'providers.create',
            'providers.update',
            'tickets.view',
            'tickets.create',
            'tickets.update',
        ]);

        $assetsView = [
            'applications.view',
            'environments.view',
            'deployments.view',
            'domains.view',
            'infrastructure.view',
            'providers.view',
        ];

        $assetsDev = [
            ...$assetsView,
            'applications.update',
            'environments.update',
            'deployments.create',
            'domains.update',
            'infrastructure.update',
            'providers.view',
        ];

        $opsView = [
            'tickets.view',
            'knowledge.view',
            'documents.view',
            'notifications.view',
            'timeline.view',
        ];

        $opsStaff = [
            ...$opsView,
            'tickets.update',
            'knowledge.update',
            'documents.update',
            'documents.create',
        ];

        foreach (['Tech Lead', 'DevOps'] as $roleName) {
            Role::findByName($roleName, 'web')->syncPermissions([
                'dashboard.view',
                'settings.view',
                'clients.view',
                'contacts.view',
                'client_groups.view',
                'service_catalog.view',
                'client_services.view',
                'renewals.view',
                'projects.view',
                'tasks.view',
                'tasks.create',
                'tasks.update',
                ...$assetsDev,
                'applications.create',
                'environments.create',
                'domains.create',
                'infrastructure.create',
                'providers.create',
                'providers.update',
                'integrations.view',
                'integrations.create',
                'integrations.update',
                'integrations.sync',
                'automation.view',
                'automation.run',
                'automation.update',
                ...$opsStaff,
            ]);
        }

        foreach (['Developer', 'Support'] as $roleName) {
            Role::findByName($roleName, 'web')->syncPermissions([
                'dashboard.view',
                'settings.view',
                'clients.view',
                'contacts.view',
                'client_groups.view',
                'service_catalog.view',
                'client_services.view',
                'renewals.view',
                'projects.view',
                'tasks.view',
                'tasks.create',
                'tasks.update',
                ...$assetsView,
                'applications.update',
                'environments.update',
                'deployments.create',
                'integrations.view',
                'automation.view',
                ...$opsStaff,
            ]);
        }

        $organization = Organization::query()->firstOrCreate(
            ['slug' => 'cypherhost'],
            [
                'name' => 'CypherHost',
                'timezone' => 'UTC',
                'currency' => 'USD',
                'status' => 'active',
            ]
        );

        $department = Department::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'ENG',
            ],
            [
                'name' => 'Engineering',
                'status' => 'active',
            ]
        );

        $team = Team::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'CORE',
            ],
            [
                'department_id' => $department->id,
                'name' => 'Core Platform',
                'status' => 'active',
            ]
        );

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@cypherhost.local'],
            [
                'organization_id' => $organization->id,
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Super Admin');
        $admin->teams()->syncWithoutDetaching([$team->id]);

        app()->instance('currentOrganizationId', $organization->id);

        $group = ClientGroup::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'VIP',
            ],
            [
                'name' => 'VIP',
                'color' => '#2563eb',
                'description' => 'Strategic accounts',
            ]
        );

        $client = Client::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'client_code' => 'ABC-001',
            ],
            [
                'company_name' => 'ABC Logistics',
                'trading_name' => 'ABC Logistics',
                'client_type' => 'enterprise',
                'industry' => 'Logistics',
                'website' => 'https://example.com',
                'email' => 'ops@abc-logistics.example',
                'phone' => '+1-555-0100',
                'country' => 'United States',
                'status' => ClientStatus::Active,
                'account_manager_id' => $admin->id,
                'notes' => 'Seed demo client for Phase 2.',
            ]
        );

        $client->groups()->syncWithoutDetaching([$group->id]);

        Contact::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'client_id' => $client->id,
                'email' => 'jane@abc-logistics.example',
            ],
            [
                'name' => 'Jane Carter',
                'position' => 'IT Director',
                'phone' => '+1-555-0101',
                'department' => 'Technology',
                'is_primary' => true,
                'is_technical' => true,
                'status' => 'active',
            ]
        );

        $hostingCategory = \App\Domains\Catalog\Models\ServiceCategory::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'HOSTING',
            ],
            [
                'name' => 'Hosting',
                'description' => 'Hosting and infrastructure packages',
                'sort_order' => 1,
                'status' => 'active',
            ]
        );

        $maintenanceCategory = \App\Domains\Catalog\Models\ServiceCategory::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'MAINT',
            ],
            [
                'name' => 'Maintenance',
                'description' => 'Ongoing support and maintenance',
                'sort_order' => 2,
                'status' => 'active',
            ]
        );

        $vpsType = \App\Domains\Catalog\Models\ServiceType::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'VPS-STD',
            ],
            [
                'service_category_id' => $hostingCategory->id,
                'name' => 'Standard VPS',
                'description' => 'Managed VPS hosting',
                'billing_type' => 'recurring',
                'billing_cycle' => 'monthly',
                'default_cost' => 40,
                'default_selling_price' => 99,
                'owner_id' => $admin->id,
                'status' => 'active',
            ]
        );

        $maintType = \App\Domains\Catalog\Models\ServiceType::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'CARE-PLAN',
            ],
            [
                'service_category_id' => $maintenanceCategory->id,
                'name' => 'Care Plan',
                'description' => 'Monthly website care plan',
                'billing_type' => 'recurring',
                'billing_cycle' => 'monthly',
                'default_cost' => 25,
                'default_selling_price' => 149,
                'owner_id' => $admin->id,
                'status' => 'active',
            ]
        );

        $clientService = \App\Domains\Catalog\Models\ClientService::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'client_id' => $client->id,
                'service_type_id' => $vpsType->id,
                'name' => 'ABC Logistics Production VPS',
            ],
            [
                'start_date' => now()->subMonths(2)->toDateString(),
                'renewal_date' => now()->addDays(20)->toDateString(),
                'billing_type' => 'recurring',
                'billing_cycle' => 'monthly',
                'cost' => 40,
                'selling_price' => 99,
                'recurring_revenue' => 99,
                'provider' => 'CypherHost',
                'account_manager_id' => $admin->id,
                'technical_owner_id' => $admin->id,
                'status' => 'active',
            ]
        );

        app(\App\Domains\Finance\Actions\SyncRenewalFromClientService::class)->handle($clientService);

        \App\Domains\Catalog\Models\ClientService::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'client_id' => $client->id,
                'service_type_id' => $maintType->id,
                'name' => 'ABC Logistics Care Plan',
            ],
            [
                'start_date' => now()->subMonth()->toDateString(),
                'renewal_date' => now()->addDays(5)->toDateString(),
                'billing_type' => 'recurring',
                'billing_cycle' => 'monthly',
                'cost' => 25,
                'selling_price' => 149,
                'recurring_revenue' => 149,
                'account_manager_id' => $admin->id,
                'status' => 'active',
            ]
        );

        $template = \App\Domains\Delivery\Models\ProjectTemplate::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'WEB-LAUNCH',
            ],
            [
                'name' => 'Website Launch',
                'description' => 'Standard website delivery checklist',
                'default_status' => 'planned',
                'is_active' => true,
            ]
        );

        if ($template->items()->count() === 0) {
            foreach ([
                ['title' => 'Discovery workshop', 'sort_order' => 1],
                ['title' => 'Design mockups', 'sort_order' => 2],
                ['title' => 'Development sprint', 'sort_order' => 3],
                ['title' => 'UAT & handover', 'sort_order' => 4],
            ] as $item) {
                $template->items()->create([
                    'title' => $item['title'],
                    'status' => 'todo',
                    'priority' => 'medium',
                    'sort_order' => $item['sort_order'],
                ]);
            }
        }

        $project = \App\Domains\Delivery\Models\Project::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'ABC-WEB-01',
            ],
            [
                'client_id' => $client->id,
                'project_template_id' => $template->id,
                'name' => 'ABC Logistics Website Rebuild',
                'description' => 'Phase 4 demo project',
                'status' => 'active',
                'priority' => 'high',
                'health_status' => 'healthy',
                'health_score' => 100,
                'start_date' => now()->subWeeks(2)->toDateString(),
                'due_date' => now()->addWeeks(6)->toDateString(),
                'budget' => 12000,
                'project_manager_id' => $admin->id,
            ]
        );

        app(\App\Domains\Delivery\Actions\SyncProjectServices::class)->handle($project, [$clientService->id]);

        if ($project->tasks()->count() === 0) {
            app(\App\Domains\Delivery\Actions\ApplyProjectTemplate::class)->handle($project, $template);
        }

        app(\App\Domains\Delivery\Services\ProjectHealthCalculator::class)->apply($project->fresh());

        $hostingProvider = \App\Domains\Assets\Models\Provider::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'CYPHERHOST',
            ],
            [
                'name' => 'CypherHost',
                'type' => 'hosting',
                'website' => 'https://cypherhost.local',
                'status' => 'active',
            ]
        );

        $application = \App\Domains\Assets\Models\Application::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'ABC-PORTAL',
            ],
            [
                'client_id' => $client->id,
                'project_id' => $project->id,
                'name' => 'ABC Logistics Portal',
                'description' => 'Customer portal for ABC Logistics',
                'stack' => 'Laravel + Inertia React',
                'repository_url' => 'https://github.com/example/abc-logistics-portal',
                'status' => 'active',
                'owner_id' => $admin->id,
            ]
        );

        $production = \App\Domains\Assets\Models\Environment::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'application_id' => $application->id,
                'slug' => 'production',
            ],
            [
                'name' => 'Production',
                'url' => 'https://portal.abc-logistics.example',
                'status' => 'active',
                'branch' => 'main',
            ]
        );

        $staging = \App\Domains\Assets\Models\Environment::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'application_id' => $application->id,
                'slug' => 'staging',
            ],
            [
                'name' => 'Staging',
                'url' => 'https://staging.abc-logistics.example',
                'status' => 'active',
                'branch' => 'develop',
            ]
        );

        \App\Domains\Assets\Models\Deployment::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'environment_id' => $production->id,
                'version' => '1.0.0',
            ],
            [
                'status' => 'success',
                'deployed_at' => now()->subDays(2),
                'deployed_by' => $admin->id,
                'commit_sha' => 'abc1234',
                'notes' => 'Initial production release',
            ]
        );

        $domain = \App\Domains\Assets\Models\Domain::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => 'abc-logistics.example',
            ],
            [
                'client_id' => $client->id,
                'project_id' => $project->id,
                'provider_id' => $hostingProvider->id,
                'registrar' => 'Example Registrar',
                'registered_at' => now()->subYears(2)->toDateString(),
                'expires_at' => now()->addMonths(10)->toDateString(),
                'auto_renew' => true,
                'status' => 'active',
            ]
        );

        app(\App\Domains\Assets\Actions\SyncRenewalFromAsset::class)->handle($domain);

        foreach ([
            ['type' => 'A', 'name' => '@', 'value' => '203.0.113.10'],
            ['type' => 'MX', 'name' => '@', 'value' => 'mail.abc-logistics.example', 'priority' => 10],
        ] as $record) {
            \App\Domains\Assets\Models\DnsRecord::withoutGlobalScopes()->firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'domain_id' => $domain->id,
                    'type' => $record['type'],
                    'name' => $record['name'],
                    'value' => $record['value'],
                ],
                [
                    'ttl' => 3600,
                    'priority' => $record['priority'] ?? null,
                    'status' => 'active',
                ]
            );
        }

        $ssl = \App\Domains\Assets\Models\SslCertificate::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'common_name' => 'abc-logistics.example',
            ],
            [
                'client_id' => $client->id,
                'domain_id' => $domain->id,
                'issuer' => "Let's Encrypt",
                'issued_at' => now()->subMonths(2)->toDateString(),
                'expires_at' => now()->addMonths(4)->toDateString(),
                'auto_renew' => true,
                'status' => 'active',
            ]
        );

        app(\App\Domains\Assets\Actions\SyncRenewalFromAsset::class)->handle($ssl);

        $hosting = \App\Domains\Assets\Models\HostingAccount::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => 'ABC Logistics Production Hosting',
            ],
            [
                'client_id' => $client->id,
                'provider_id' => $hostingProvider->id,
                'plan' => 'Business VPS',
                'primary_domain' => 'abc-logistics.example',
                'username' => 'abcprod',
                'expires_at' => now()->addMonths(8)->toDateString(),
                'cost' => 99,
                'status' => 'active',
            ]
        );

        app(\App\Domains\Assets\Actions\SyncRenewalFromAsset::class)->handle($hosting);

        $server = \App\Domains\Assets\Models\Server::withoutGlobalScopes()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'hostname' => 'abc-portal-01.cypherhost.local',
            ],
            [
                'client_id' => $client->id,
                'provider_id' => $hostingProvider->id,
                'name' => 'ABC Portal Web 01',
                'ip_address' => '203.0.113.10',
                'role' => 'web',
                'os' => 'Ubuntu 22.04',
                'specs' => ['cpu' => 4, 'ram_gb' => 8, 'disk_gb' => 160],
                'status' => 'active',
            ]
        );

        app(\App\Domains\Assets\Actions\SyncEnvironmentResources::class)->handle($production, [
            ['type' => 'server', 'id' => $server->id, 'role' => 'primary'],
        ]);

        $slaPolicy = app(\App\Domains\Operations\Actions\UpsertSlaPolicy::class)->handle(null, [
            'organization_id' => $organization->id,
            'name' => 'Standard Support',
            'code' => 'STD-SLA',
            'description' => 'Default support SLA for all clients',
            'is_default' => true,
            'status' => 'active',
        ], [
            ['priority' => 'low', 'respond_within_minutes' => 480, 'resolve_within_minutes' => 2880],
            ['priority' => 'medium', 'respond_within_minutes' => 240, 'resolve_within_minutes' => 1440],
            ['priority' => 'high', 'respond_within_minutes' => 60, 'resolve_within_minutes' => 480],
            ['priority' => 'urgent', 'respond_within_minutes' => 15, 'resolve_within_minutes' => 240],
        ]);

        $ticket = app(\App\Domains\Operations\Actions\UpsertTicket::class)->handle(null, [
            'organization_id' => $organization->id,
            'client_id' => $client->id,
            'project_id' => $project->id,
            'application_id' => $application->id,
            'sla_policy_id' => $slaPolicy->id,
            'subject' => 'Portal login issue for warehouse staff',
            'description' => 'Users report intermittent 500 errors when signing in to the customer portal.',
            'status' => 'open',
            'priority' => 'high',
            'type' => 'incident',
            'requester_id' => $admin->id,
            'assignee_id' => $admin->id,
        ], $admin);

        app(\App\Domains\Operations\Actions\AddTicketComment::class)->handle($ticket, [
            'body' => 'Investigating server logs — initial response sent to client.',
            'is_internal' => false,
        ], $admin);

        $kbCategory = \App\Domains\Operations\Models\KnowledgeCategory::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'slug' => 'getting-started',
            ],
            [
                'name' => 'Getting Started',
                'description' => 'Onboarding guides and FAQs',
                'sort_order' => 1,
                'status' => 'active',
            ]
        );

        \App\Domains\Operations\Models\KnowledgeArticle::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'slug' => 'reset-portal-password',
            ],
            [
                'knowledge_category_id' => $kbCategory->id,
                'title' => 'How to reset your portal password',
                'summary' => 'Step-by-step password reset for ABC Logistics portal users.',
                'body' => "1. Go to the login page\n2. Click Forgot password\n3. Check your email for the reset link\n4. Set a new password meeting complexity requirements",
                'status' => 'published',
                'is_internal' => false,
                'author_id' => $admin->id,
                'published_at' => now(),
            ]
        );

        $docPath = "documents/{$organization->id}/abc-onboarding.pdf";
        \Illuminate\Support\Facades\Storage::disk('local')->put($docPath, 'Demo document placeholder');

        app(\App\Domains\Operations\Actions\UpsertDocument::class)->handle(null, [
            'organization_id' => $organization->id,
            'client_id' => $client->id,
            'project_id' => $project->id,
            'title' => 'ABC Logistics Onboarding Pack',
            'description' => 'Welcome pack and access credentials overview',
            'disk' => 'local',
            'path' => $docPath,
            'original_name' => 'abc-onboarding.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 24,
            'visibility' => 'client',
            'uploaded_by' => $admin->id,
            'status' => 'active',
        ]);

        app(\App\Domains\Operations\Services\CreateNotification::class)->handle(
            $admin,
            'ticket.assigned',
            "Ticket {$ticket->number} requires attention",
            $ticket->subject,
            ['ticket_id' => $ticket->id],
            $ticket,
        );

        $revenueEntry = app(\App\Domains\Finance\Actions\UpsertFinancialEntry::class)->handle(null, [
            'organization_id' => $organization->id,
            'client_id' => $client->id,
            'client_service_id' => $clientService->id,
            'entry_type' => 'revenue',
            'category' => 'hosting',
            'description' => 'ABC Logistics VPS — monthly revenue',
            'amount' => 99,
            'currency' => 'USD',
            'occurred_on' => now()->startOfMonth()->toDateString(),
            'created_by' => $admin->id,
        ]);
        app(\App\Domains\Finance\Actions\PostFinancialEntry::class)->handle($revenueEntry);

        $costEntry = app(\App\Domains\Finance\Actions\UpsertFinancialEntry::class)->handle(null, [
            'organization_id' => $organization->id,
            'client_id' => $client->id,
            'client_service_id' => $clientService->id,
            'entry_type' => 'cost',
            'category' => 'hosting',
            'description' => 'ABC Logistics VPS — provider cost',
            'amount' => 40,
            'currency' => 'USD',
            'occurred_on' => now()->startOfMonth()->toDateString(),
            'created_by' => $admin->id,
        ]);
        app(\App\Domains\Finance\Actions\PostFinancialEntry::class)->handle($costEntry);

        $careRevenue = app(\App\Domains\Finance\Actions\UpsertFinancialEntry::class)->handle(null, [
            'organization_id' => $organization->id,
            'client_id' => $client->id,
            'entry_type' => 'revenue',
            'category' => 'subscription',
            'description' => 'ABC Logistics Care Plan — monthly',
            'amount' => 149,
            'currency' => 'USD',
            'occurred_on' => now()->startOfMonth()->toDateString(),
            'created_by' => $admin->id,
        ]);
        app(\App\Domains\Finance\Actions\PostFinancialEntry::class)->handle($careRevenue);

        app(\App\Domains\Finance\Actions\UpsertInvoice::class)->handle(null, [
            'organization_id' => $organization->id,
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'tax_amount' => 0,
            'notes' => 'Draft invoice for ABC Logistics services',
        ], [
            [
                'description' => 'ABC Logistics Production VPS — monthly',
                'quantity' => 1,
                'unit_price' => 99,
                'client_service_id' => $clientService->id,
            ],
            [
                'description' => 'ABC Logistics Care Plan — monthly',
                'quantity' => 1,
                'unit_price' => 149,
            ],
        ]);

        app()->instance('currentOrganizationId', $organization->id);

        $awsIntegration = app(\App\Domains\Integrations\Actions\UpsertIntegration::class)->handle(null, [
            'organization_id' => $organization->id,
            'name' => 'AWS Production Inventory',
            'provider' => 'aws',
            'category' => 'cloud',
            'status' => 'connected',
            'config' => ['region' => 'us-east-1', 'account_id' => '123456789012'],
            'connected_by' => $admin->id,
            'last_synced_at' => now()->subHour(),
        ], [
            'access_key' => 'AKIAFAKESEEDKEY',
            'secret_key' => 'fake-secret-seed-value',
        ]);

        foreach ([
            ['ec2_instance', 'i-seed001', 'web-prod-01'],
            ['rds', 'db-seed001', 'app-db-primary'],
            ['s3_bucket', 'assets-seed-bucket', 'assets-seed-bucket'],
        ] as [$type, $externalId, $name]) {
            \App\Domains\Integrations\Models\IntegrationExternalRef::query()->create([
                'organization_id' => $organization->id,
                'integration_id' => $awsIntegration->id,
                'external_type' => $type,
                'external_id' => $externalId,
                'external_name' => $name,
                'payload' => ['region' => 'us-east-1'],
                'last_seen_at' => now()->subHour(),
            ]);
        }

        \App\Domains\Integrations\Models\IntegrationSyncLog::query()->create([
            'organization_id' => $organization->id,
            'integration_id' => $awsIntegration->id,
            'action' => 'sync',
            'status' => 'succeeded',
            'message' => 'Completed successfully.',
            'meta' => ['imported' => 3],
            'started_at' => now()->subHour(),
            'finished_at' => now()->subHour(),
            'triggered_by' => $admin->id,
        ]);

        $githubIntegration = app(\App\Domains\Integrations\Actions\UpsertIntegration::class)->handle(null, [
            'organization_id' => $organization->id,
            'name' => 'GitHub Org',
            'provider' => 'github',
            'category' => 'source_control',
            'status' => 'connected',
            'config' => ['org' => 'cypherhost'],
            'connected_by' => $admin->id,
            'last_synced_at' => now()->subHours(2),
        ], [
            'token' => 'ghp_fake_seed_token',
        ]);

        \App\Domains\Integrations\Models\IntegrationExternalRef::query()->create([
            'organization_id' => $organization->id,
            'integration_id' => $githubIntegration->id,
            'external_type' => 'github_repo',
            'external_id' => 'cypherhost/platform-api',
            'external_name' => 'platform-api',
            'payload' => ['default_branch' => 'main'],
            'last_seen_at' => now()->subHours(2),
        ]);

        app(\App\Domains\Integrations\Actions\UpsertIntegration::class)->handle(null, [
            'organization_id' => $organization->id,
            'name' => 'Cloudflare DNS',
            'provider' => 'cloudflare',
            'category' => 'dns',
            'status' => 'disconnected',
            'config' => ['zone_id' => 'zone-seed-stub'],
            'is_enabled' => true,
        ]);

        $renewalRule = \App\Domains\Automation\Models\AutomationRule::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => 'Renewals in 14 days',
            ],
            [
                'description' => 'Notify administrators when a renewal is due within 14 days',
                'trigger' => 'renewal_expiring',
                'conditions' => ['days_ahead' => 14],
                'actions' => [['type' => 'notify_users', 'role' => 'Administrator']],
                'notify_in_app' => true,
                'notify_email' => false,
                'is_enabled' => true,
                'created_by' => $admin->id,
            ]
        );

        \App\Domains\Automation\Models\AutomationRule::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => 'SLA breached',
            ],
            [
                'description' => 'Notify assignee and administrators on SLA breach',
                'trigger' => 'sla_breach',
                'conditions' => null,
                'actions' => [
                    ['type' => 'notify_assignee'],
                    ['type' => 'notify_users', 'role' => 'Administrator'],
                ],
                'notify_in_app' => true,
                'is_enabled' => true,
                'created_by' => $admin->id,
            ]
        );

        $renewalSchedule = \App\Domains\Automation\Models\AutomationSchedule::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => 'Daily renewal scan',
            ],
            [
                'job_type' => 'scan_renewals',
                'frequency' => 'daily',
                'is_enabled' => true,
                'config' => ['renewal_days' => [7, 14, 30]],
            ]
        );

        \App\Domains\Automation\Models\AutomationSchedule::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => 'Hourly SLA scan',
            ],
            [
                'job_type' => 'scan_sla',
                'frequency' => 'hourly',
                'is_enabled' => true,
            ]
        );

        \App\Domains\Automation\Models\AutomationSchedule::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'name' => 'Hourly integration sync',
            ],
            [
                'job_type' => 'sync_integrations',
                'frequency' => 'hourly',
                'is_enabled' => true,
            ]
        );

        \App\Domains\Automation\Models\AutomationRun::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'automation_rule_id' => $renewalRule->id,
                'trigger' => 'renewal_expiring',
                'status' => 'succeeded',
            ],
            [
                'message' => 'Seed sample run completed.',
                'meta' => ['matched_count' => 1, 'notified_count' => 1],
                'started_at' => now()->subHour(),
                'finished_at' => now()->subHour(),
                'triggered_by' => $admin->id,
            ]
        );
    }
}

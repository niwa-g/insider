<?php

use App\Http\Controllers\Assets\ApplicationController;
use App\Http\Controllers\Assets\CloudResourceController;
use App\Http\Controllers\Assets\DeploymentController;
use App\Http\Controllers\Assets\DnsRecordController;
use App\Http\Controllers\Assets\DomainController;
use App\Http\Controllers\Assets\EnvironmentController;
use App\Http\Controllers\Assets\HostingAccountController;
use App\Http\Controllers\Assets\ProviderController;
use App\Http\Controllers\Assets\ServerController;
use App\Http\Controllers\Assets\SslCertificateController;
use App\Http\Controllers\Catalog\ClientServiceController;
use App\Http\Controllers\Catalog\ServiceCategoryController;
use App\Http\Controllers\Catalog\ServiceTypeController;
use App\Http\Controllers\Crm\ClientController;
use App\Http\Controllers\Crm\ClientGroupController;
use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\OrganizationController;
use App\Http\Controllers\Settings\RoleController as SettingsRoleController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\TeamController as SettingsTeamController;
use App\Http\Controllers\Settings\UserController as SettingsUserController;
use App\Http\Controllers\Delivery\ProjectController;
use App\Http\Controllers\Delivery\ProjectTemplateController;
use App\Http\Controllers\Delivery\TaskController;
use App\Http\Controllers\Finance\DiscountReasonController;
use App\Http\Controllers\Finance\FinanceReportController;
use App\Http\Controllers\Finance\FinancialEntryController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\RenewalController;
use App\Http\Controllers\Finance\VendorBillController;
use App\Http\Controllers\Finance\VendorController;
use App\Http\Controllers\Operations\DocumentController;
use App\Http\Controllers\Operations\KnowledgeArticleController;
use App\Http\Controllers\Operations\KnowledgeCategoryController;
use App\Http\Controllers\Operations\NotificationController;
use App\Http\Controllers\Operations\SlaPolicyController;
use App\Http\Controllers\Operations\TicketCommentController;
use App\Http\Controllers\Operations\TicketController;
use App\Http\Controllers\Operations\TimelineController;
use App\Http\Controllers\Integrations\IntegrationConnectionController;
use App\Http\Controllers\Integrations\IntegrationController;
use App\Http\Controllers\Integrations\IntegrationSyncLogController;
use App\Http\Controllers\Automation\AutomationRuleController;
use App\Http\Controllers\Automation\AutomationRunController;
use App\Http\Controllers\Automation\AutomationScheduleController;
use App\Http\Middleware\SetCurrentOrganization;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => inertia('Auth/Login'))->name('login');
});

Route::middleware(['auth', SetCurrentOrganization::class])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', DashboardController::class);

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        Route::get('/organization', [OrganizationController::class, 'edit'])->name('organization.edit');
        Route::post('/organization', [OrganizationController::class, 'update'])->name('organization.update');

        Route::get('/users', [SettingsUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [SettingsUserController::class, 'create'])->name('users.create');
        Route::post('/users', [SettingsUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [SettingsUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [SettingsUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [SettingsUserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/roles', [SettingsUserController::class, 'updateRoles'])->name('users.roles');

        Route::get('/teams', [SettingsTeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/create', [SettingsTeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [SettingsTeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}/edit', [SettingsTeamController::class, 'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [SettingsTeamController::class, 'update'])->name('teams.update');
        Route::delete('/teams/{team}', [SettingsTeamController::class, 'destroy'])->name('teams.destroy');

        Route::get('/roles', [SettingsRoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [SettingsRoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [SettingsRoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [SettingsRoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [SettingsRoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [SettingsRoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
    });

    Route::resource('clients', ClientController::class);
    Route::resource('contacts', ContactController::class)->except(['show']);
    Route::resource('client-groups', ClientGroupController::class)
        ->parameters(['client-groups' => 'client_group'])
        ->except(['show']);

    Route::resource('service-categories', ServiceCategoryController::class)
        ->parameters(['service-categories' => 'service_category'])
        ->except(['show']);
    Route::resource('service-types', ServiceTypeController::class)
        ->parameters(['service-types' => 'service_type'])
        ->except(['show']);
    Route::resource('client-services', ClientServiceController::class)
        ->parameters(['client-services' => 'client_service'])
        ->except(['show']);

    Route::get('/renewals', [RenewalController::class, 'index'])->name('renewals.index');
    Route::post('/renewals/{renewal}/renew', [RenewalController::class, 'renew'])->name('renewals.renew');

    Route::resource('financial-entries', FinancialEntryController::class)
        ->parameters(['financial-entries' => 'financial_entry'])
        ->except(['show']);
    Route::post('/financial-entries/{financial_entry}/post', [FinancialEntryController::class, 'post'])
        ->name('financial-entries.post');
    Route::post('/financial-entries/{financial_entry}/void', [FinancialEntryController::class, 'void'])
        ->name('financial-entries.void');

    Route::get('/invoices/from-services', [InvoiceController::class, 'createFromServices'])->name('invoices.from-services');
    Route::post('/invoices/from-services', [InvoiceController::class, 'storeFromServices'])->name('invoices.from-services.store');
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
    Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
    Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');

    Route::resource('discount-reasons', DiscountReasonController::class)
        ->parameters(['discount-reasons' => 'discount_reason'])
        ->except(['show']);

    Route::resource('vendors', VendorController::class)->except(['show']);
    Route::resource('vendor-bills', VendorBillController::class)
        ->parameters(['vendor-bills' => 'vendor_bill'])
        ->only(['index', 'create', 'store', 'show']);
    Route::post('/vendor-bills/{vendor_bill}/payments', [VendorBillController::class, 'pay'])
        ->name('vendor-bills.payments.store');
    Route::post('/vendor-bills/{vendor_bill}/pay', [VendorBillController::class, 'payInFull'])
        ->name('vendor-bills.pay');

    Route::get('/finance/reports', [FinanceReportController::class, 'index'])->name('finance.reports.index');

    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/services', [ProjectController::class, 'syncServices'])
        ->name('projects.services.sync');

    Route::resource('project-templates', ProjectTemplateController::class)
        ->parameters(['project-templates' => 'project_template'])
        ->except(['show']);

    Route::resource('tasks', TaskController::class)->except(['show']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

    Route::resource('providers', ProviderController::class)->except(['show']);
    Route::resource('applications', ApplicationController::class);
    Route::get('/applications/{application}/relationships', [ApplicationController::class, 'relationships'])
        ->name('applications.relationships');
    Route::resource('environments', EnvironmentController::class)->except(['show']);
    Route::post('/environments/{environment}/resources', [EnvironmentController::class, 'syncResources'])
        ->name('environments.resources.sync');
    Route::resource('deployments', DeploymentController::class)->only(['index', 'store', 'destroy']);
    Route::resource('domains', DomainController::class);
    Route::resource('domains.dns-records', DnsRecordController::class)
        ->parameters(['dns-records' => 'dns_record'])
        ->except(['show', 'index']);
    Route::resource('ssl-certificates', SslCertificateController::class)
        ->parameters(['ssl-certificates' => 'ssl_certificate'])
        ->except(['show']);
    Route::resource('hosting-accounts', HostingAccountController::class)
        ->parameters(['hosting-accounts' => 'hosting_account'])
        ->except(['show']);
    Route::resource('servers', ServerController::class)->except(['show']);
    Route::resource('cloud-resources', CloudResourceController::class)
        ->parameters(['cloud-resources' => 'cloud_resource'])
        ->except(['show']);

    Route::resource('tickets', TicketController::class);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])->name('tickets.comments.store');
    Route::delete('/ticket-comments/{ticket_comment}', [TicketCommentController::class, 'destroy'])->name('ticket-comments.destroy');

    Route::resource('documents', DocumentController::class)->except(['show']);

    Route::resource('knowledge-categories', KnowledgeCategoryController::class)
        ->parameters(['knowledge-categories' => 'knowledge_category'])
        ->except(['show']);
    Route::resource('knowledge-articles', KnowledgeArticleController::class)
        ->parameters(['knowledge-articles' => 'knowledge_article']);

    Route::resource('sla-policies', SlaPolicyController::class)
        ->parameters(['sla-policies' => 'sla_policy'])
        ->except(['show']);

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');

    Route::resource('integrations', IntegrationController::class);
    Route::post('/integrations/{integration}/test', [IntegrationConnectionController::class, 'test'])->name('integrations.test');
    Route::post('/integrations/{integration}/connect', [IntegrationConnectionController::class, 'connect'])->name('integrations.connect');
    Route::post('/integrations/{integration}/disconnect', [IntegrationConnectionController::class, 'disconnect'])->name('integrations.disconnect');
    Route::post('/integrations/{integration}/sync', [IntegrationConnectionController::class, 'sync'])->name('integrations.sync');
    Route::get('/integration-sync-logs', [IntegrationSyncLogController::class, 'index'])->name('integration-sync-logs.index');

    Route::resource('automation/rules', AutomationRuleController::class)
        ->parameters(['rules' => 'automation_rule'])
        ->names('automation.rules');
    Route::post('/automation/rules/{automation_rule}/toggle', [AutomationRuleController::class, 'toggle'])
        ->name('automation.rules.toggle');
    Route::post('/automation/rules/{automation_rule}/run', [AutomationRuleController::class, 'run'])
        ->name('automation.rules.run');

    Route::resource('automation/schedules', AutomationScheduleController::class)
        ->parameters(['schedules' => 'automation_schedule'])
        ->except(['show'])
        ->names('automation.schedules');
    Route::post('/automation/schedules/{automation_schedule}/toggle', [AutomationScheduleController::class, 'toggle'])
        ->name('automation.schedules.toggle');
    Route::post('/automation/schedules/{automation_schedule}/run', [AutomationScheduleController::class, 'run'])
        ->name('automation.schedules.run');

    Route::get('/automation/runs', [AutomationRunController::class, 'index'])->name('automation.runs.index');
    Route::get('/automation/runs/{automation_run}', [AutomationRunController::class, 'show'])->name('automation.runs.show');
});

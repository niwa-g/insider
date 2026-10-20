<?php

namespace App\Providers;

use App\Domains\Assets\Models\Application;
use App\Domains\Assets\Models\CloudResource;
use App\Domains\Assets\Models\Deployment;
use App\Domains\Assets\Models\DnsRecord;
use App\Domains\Assets\Models\Domain;
use App\Domains\Assets\Models\Environment;
use App\Domains\Assets\Models\HostingAccount;
use App\Domains\Assets\Models\Provider;
use App\Domains\Assets\Models\Server;
use App\Domains\Assets\Models\SslCertificate;
use App\Domains\Assets\Policies\ApplicationPolicy;
use App\Domains\Assets\Policies\CloudResourcePolicy;
use App\Domains\Assets\Policies\DeploymentPolicy;
use App\Domains\Assets\Policies\DnsRecordPolicy;
use App\Domains\Assets\Policies\DomainPolicy;
use App\Domains\Assets\Policies\EnvironmentPolicy;
use App\Domains\Assets\Policies\HostingAccountPolicy;
use App\Domains\Assets\Policies\ProviderPolicy;
use App\Domains\Assets\Policies\ServerPolicy;
use App\Domains\Assets\Policies\SslCertificatePolicy;
use App\Domains\Catalog\Models\ClientService;
use App\Domains\Catalog\Models\ServiceCategory;
use App\Domains\Catalog\Models\ServiceType;
use App\Domains\Catalog\Policies\ClientServicePolicy;
use App\Domains\Catalog\Policies\ServiceCategoryPolicy;
use App\Domains\Catalog\Policies\ServiceTypePolicy;
use App\Domains\Crm\Models\Client;
use App\Domains\Crm\Models\ClientGroup;
use App\Domains\Crm\Models\Contact;
use App\Domains\Crm\Policies\ClientGroupPolicy;
use App\Domains\Crm\Policies\ClientPolicy;
use App\Domains\Crm\Policies\ContactPolicy;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\ProjectTemplate;
use App\Domains\Delivery\Models\Task;
use App\Domains\Delivery\Policies\ProjectPolicy;
use App\Domains\Delivery\Policies\ProjectTemplatePolicy;
use App\Domains\Delivery\Policies\TaskPolicy;
use App\Domains\Finance\Models\DiscountReason;
use App\Domains\Finance\Models\ExpenseClaim;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Finance\Models\Vendor;
use App\Domains\Finance\Models\VendorBill;
use App\Domains\Finance\Policies\DiscountReasonPolicy;
use App\Domains\Finance\Policies\ExpenseClaimPolicy;
use App\Domains\Finance\Policies\FinancialEntryPolicy;
use App\Domains\Finance\Policies\InvoicePolicy;
use App\Domains\Finance\Policies\RenewalPolicy;
use App\Domains\Finance\Policies\VendorBillPolicy;
use App\Domains\Finance\Policies\VendorPolicy;
use App\Domains\Operations\Models\Document;
use App\Domains\Operations\Models\KnowledgeArticle;
use App\Domains\Operations\Models\KnowledgeCategory;
use App\Domains\Operations\Models\OpsNotification;
use App\Domains\Operations\Models\SlaPolicy;
use App\Domains\Operations\Models\Ticket;
use App\Domains\Operations\Models\TicketComment;
use App\Domains\Operations\Policies\DocumentPolicy;
use App\Domains\Operations\Policies\KnowledgeArticlePolicy;
use App\Domains\Operations\Policies\KnowledgeCategoryPolicy;
use App\Domains\Operations\Policies\OpsNotificationPolicy;
use App\Domains\Operations\Policies\SlaPolicyPolicy;
use App\Domains\Operations\Policies\TicketCommentPolicy;
use App\Domains\Operations\Policies\TicketPolicy;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationSyncLog;
use App\Domains\Integrations\Policies\IntegrationPolicy;
use App\Domains\Integrations\Policies\IntegrationSyncLogPolicy;
use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\AutomationRun;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Automation\Policies\AutomationRulePolicy;
use App\Domains\Automation\Policies\AutomationRunPolicy;
use App\Domains\Automation\Policies\AutomationSchedulePolicy;
use App\Domains\Automation\Events\TicketCreated;
use App\Domains\Automation\Listeners\EvaluateTicketCreatedRules;
use App\Domains\Platform\Services\AuditLogger;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class);
    }

    public function boot(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(ClientGroup::class, ClientGroupPolicy::class);
        Gate::policy(ServiceCategory::class, ServiceCategoryPolicy::class);
        Gate::policy(ServiceType::class, ServiceTypePolicy::class);
        Gate::policy(ClientService::class, ClientServicePolicy::class);
        Gate::policy(Renewal::class, RenewalPolicy::class);
        Gate::policy(FinancialEntry::class, FinancialEntryPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(DiscountReason::class, DiscountReasonPolicy::class);
        Gate::policy(ExpenseClaim::class, ExpenseClaimPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(VendorBill::class, VendorBillPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(ProjectTemplate::class, ProjectTemplatePolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Environment::class, EnvironmentPolicy::class);
        Gate::policy(Deployment::class, DeploymentPolicy::class);
        Gate::policy(Domain::class, DomainPolicy::class);
        Gate::policy(DnsRecord::class, DnsRecordPolicy::class);
        Gate::policy(SslCertificate::class, SslCertificatePolicy::class);
        Gate::policy(Provider::class, ProviderPolicy::class);
        Gate::policy(Server::class, ServerPolicy::class);
        Gate::policy(CloudResource::class, CloudResourcePolicy::class);
        Gate::policy(HostingAccount::class, HostingAccountPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(TicketComment::class, TicketCommentPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(KnowledgeCategory::class, KnowledgeCategoryPolicy::class);
        Gate::policy(KnowledgeArticle::class, KnowledgeArticlePolicy::class);
        Gate::policy(SlaPolicy::class, SlaPolicyPolicy::class);
        Gate::policy(OpsNotification::class, OpsNotificationPolicy::class);
        Gate::policy(Integration::class, IntegrationPolicy::class);
        Gate::policy(IntegrationSyncLog::class, IntegrationSyncLogPolicy::class);
        Gate::policy(AutomationRule::class, AutomationRulePolicy::class);
        Gate::policy(AutomationSchedule::class, AutomationSchedulePolicy::class);
        Gate::policy(AutomationRun::class, AutomationRunPolicy::class);

        Event::listen(TicketCreated::class, EvaluateTicketCreatedRules::class);
    }
}

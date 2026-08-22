import { Link } from '@inertiajs/react';
import {
    AlertTriangle,
    Building2,
    FolderKanban,
    LifeBuoy,
    RefreshCcw,
    TrendingDown,
    TrendingUp,
    Wallet,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { EmptyState, KpiCard, PageHeader } from '@/components/ui/page';
import AppLayout from '@/layouts/AppLayout';
import { formatCurrency } from '@/lib/utils';

type AttentionItem = {
    id: string;
    title: string;
    href: string;
    meta: string;
};

type Props = {
    kpis: Record<string, number>;
    attention: {
        renewals: AttentionItem[];
        overdue_tasks: AttentionItem[];
        at_risk_projects: AttentionItem[];
        sla_breaches: AttentionItem[];
    };
    recentAudit: Array<{ id: number; action: string; created_at: string; user?: { name: string } | null }>;
    userRole?: string;
};

function AttentionList({ title, items, empty }: { title: string; items: AttentionItem[]; empty: string }) {
    return (
        <div>
            <h3 className="text-sm font-medium text-foreground">{title}</h3>
            {items.length === 0 ? (
                <p className="mt-2 text-sm text-muted-foreground">{empty}</p>
            ) : (
                <ul className="mt-2 space-y-1">
                    {items.map((item) => (
                        <li key={item.id}>
                            <Link href={item.href} className="block rounded-lg px-2.5 py-2 transition-colors hover:bg-muted">
                                <div className="text-sm font-medium">{item.title}</div>
                                {item.meta ? <div className="text-xs text-muted-foreground">{item.meta}</div> : null}
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export default function DashboardIndex({ kpis, attention, recentAudit, userRole }: Props) {
    const attentionCount =
        attention.renewals.length +
        attention.overdue_tasks.length +
        attention.at_risk_projects.length +
        attention.sla_breaches.length;

    return (
        <AppLayout title="Dashboard">
            <PageHeader
                title="Dashboard"
                description="Operational overview across clients, delivery, assets, and finance."
                breadcrumbs={[{ label: 'Dashboard' }]}
                actions={
                    userRole ? (
                        <span className="rounded-full border border-border bg-card px-3 py-1 text-xs text-muted-foreground">
                            Viewing as {userRole}
                        </span>
                    ) : null
                }
            />

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <KpiCard label="Active clients" value={kpis.active_clients} hint={`${kpis.clients} total`} icon={Building2} />
                <KpiCard label="Active projects" value={kpis.active_projects} icon={FolderKanban} />
                <KpiCard label="MRR" value={formatCurrency(kpis.mrr)} icon={Wallet} />
                <KpiCard label="Open tickets" value={kpis.open_tickets} icon={LifeBuoy} />
                <KpiCard label="Monthly revenue" value={formatCurrency(kpis.monthly_revenue)} icon={TrendingUp} />
                <KpiCard label="Monthly costs" value={formatCurrency(kpis.monthly_costs)} icon={TrendingDown} />
                <KpiCard label="Est. profit" value={formatCurrency(kpis.estimated_profit)} />
                <KpiCard
                    label="Renewals (30d)"
                    value={kpis.expiring_renewals}
                    hint={`${kpis.failed_deployments} failed deploys`}
                    icon={RefreshCcw}
                />
            </div>

            <div className="mt-6 grid gap-4 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-base">
                            <AlertTriangle className="h-4 w-4 text-amber-500" />
                            Attention required
                        </CardTitle>
                        <CardDescription>
                            {attentionCount === 0 ? 'Nothing urgent right now.' : `${attentionCount} items need review.`}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-6 md:grid-cols-2">
                            <AttentionList
                                title="Renewals due (30 days)"
                                items={attention.renewals}
                                empty="No renewals due in the next 30 days."
                            />
                            <AttentionList title="Overdue tasks" items={attention.overdue_tasks} empty="No overdue tasks." />
                            <AttentionList
                                title="At-risk projects"
                                items={attention.at_risk_projects}
                                empty="No at-risk projects."
                            />
                            <AttentionList title="SLA breaches" items={attention.sla_breaches} empty="No SLA breaches." />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between gap-2">
                            <div>
                                <CardTitle className="text-base">Recent activity</CardTitle>
                                <CardDescription>Latest audit events</CardDescription>
                            </div>
                            <Button asChild variant="ghost" size="sm">
                                <Link href="/settings/audit">View all</Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {recentAudit.length === 0 ? (
                            <EmptyState title="No audit events yet" description="Activity will show up as your team works." />
                        ) : (
                            <ul className="space-y-3">
                                {recentAudit.map((item) => (
                                    <li key={item.id} className="flex items-start justify-between gap-3 text-sm">
                                        <div>
                                            <div className="font-medium">{item.action}</div>
                                            {item.user?.name ? (
                                                <div className="text-xs text-muted-foreground">{item.user.name}</div>
                                            ) : null}
                                        </div>
                                        <span className="shrink-0 text-xs text-muted-foreground">{item.created_at}</span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

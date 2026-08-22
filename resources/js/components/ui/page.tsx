import { ReactNode } from 'react';
import { Link } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

type Breadcrumb = { label: string; href?: string };

type Props = {
    title: string;
    description?: string;
    breadcrumbs?: Breadcrumb[];
    actions?: ReactNode;
    className?: string;
};

export function PageHeader({ title, description, breadcrumbs, actions, className }: Props) {
    return (
        <div className={cn('mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between', className)}>
            <div className="min-w-0 space-y-2">
                {breadcrumbs && breadcrumbs.length > 0 && (
                    <nav className="flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
                        {breadcrumbs.map((crumb, index) => (
                            <span key={`${crumb.label}-${index}`} className="inline-flex items-center gap-1.5">
                                {index > 0 && <span className="text-border">/</span>}
                                {crumb.href ? (
                                    <Link href={crumb.href} className="hover:text-foreground">
                                        {crumb.label}
                                    </Link>
                                ) : (
                                    <span className="text-foreground">{crumb.label}</span>
                                )}
                            </span>
                        ))}
                    </nav>
                )}
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight text-foreground">{title}</h1>
                    {description && <p className="mt-1 max-w-2xl text-sm text-muted-foreground">{description}</p>}
                </div>
            </div>
            {actions && <div className="flex shrink-0 flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}

export function EmptyState({
    icon: Icon,
    title,
    description,
    action,
}: {
    icon?: LucideIcon;
    title: string;
    description?: string;
    action?: ReactNode;
}) {
    return (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-border bg-card/50 px-6 py-16 text-center">
            {Icon && (
                <div className="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-muted text-muted-foreground">
                    <Icon className="h-5 w-5" />
                </div>
            )}
            <h3 className="text-sm font-semibold text-foreground">{title}</h3>
            {description && <p className="mt-1 max-w-sm text-sm text-muted-foreground">{description}</p>}
            {action && <div className="mt-4">{action}</div>}
        </div>
    );
}

export function KpiCard({
    label,
    value,
    hint,
    icon: Icon,
    trend,
}: {
    label: string;
    value: string | number;
    hint?: string;
    icon?: LucideIcon;
    trend?: { label: string; positive?: boolean };
}) {
    return (
        <div className="rounded-xl border border-border bg-card p-4 shadow-sm">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{label}</p>
                    <p className="mt-2 text-2xl font-semibold tabular-nums tracking-tight">{value}</p>
                </div>
                {Icon && (
                    <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <Icon className="h-4 w-4" />
                    </div>
                )}
            </div>
            {(hint || trend) && (
                <div className="mt-3 flex items-center justify-between gap-2 text-xs text-muted-foreground">
                    {hint && <span>{hint}</span>}
                    {trend && (
                        <span className={trend.positive === false ? 'text-rose-600' : 'text-emerald-600'}>
                            {trend.label}
                        </span>
                    )}
                </div>
            )}
        </div>
    );
}

export function LoadingSkeleton({ className }: { className?: string }) {
    return <div className={cn('animate-pulse rounded-lg bg-muted', className)} />;
}

export function PageActionLink({
    href,
    children,
}: {
    href: string;
    children: ReactNode;
}) {
    return (
        <Button asChild>
            <Link href={href}>{children}</Link>
        </Button>
    );
}

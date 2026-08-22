import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize transition-colors',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary/10 text-primary',
                secondary: 'border-transparent bg-secondary text-secondary-foreground',
                outline: 'border-border text-foreground',
                success: 'border-transparent bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                warning: 'border-transparent bg-amber-500/10 text-amber-800 dark:text-amber-300',
                danger: 'border-transparent bg-rose-500/10 text-rose-700 dark:text-rose-300',
                info: 'border-transparent bg-sky-500/10 text-sky-700 dark:text-sky-300',
                muted: 'border-transparent bg-muted text-muted-foreground',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
);

export function Badge({
    className,
    variant,
    ...props
}: React.HTMLAttributes<HTMLDivElement> & VariantProps<typeof badgeVariants>) {
    return <div className={cn(badgeVariants({ variant }), className)} {...props} />;
}

const statusVariantMap: Record<string, VariantProps<typeof badgeVariants>['variant']> = {
    prospect: 'info',
    active: 'success',
    inactive: 'muted',
    suspended: 'warning',
    archived: 'danger',
    open: 'info',
    closed: 'muted',
    completed: 'success',
    failed: 'danger',
    pending: 'warning',
    draft: 'muted',
    posted: 'success',
    void: 'danger',
    at_risk: 'danger',
    on_track: 'success',
};

export function StatusBadge({
    status,
    label,
    className,
}: {
    status: string;
    label?: string;
    className?: string;
}) {
    return (
        <Badge variant={statusVariantMap[status] ?? 'muted'} className={className}>
            {label ?? status.replaceAll('_', ' ')}
        </Badge>
    );
}

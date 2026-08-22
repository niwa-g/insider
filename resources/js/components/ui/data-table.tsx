import { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Column<T> = {
    key: string;
    header: string;
    className?: string;
    hideOnMobile?: boolean;
    cell: (row: T) => ReactNode;
};

type Props<T> = {
    columns: Column<T>[];
    rows: T[];
    rowKey: (row: T) => string;
    empty?: ReactNode;
    onRowClick?: (row: T) => void;
    toolbar?: ReactNode;
    mobileCard?: (row: T) => ReactNode;
};

export function DataTable<T>({ columns, rows, rowKey, empty, onRowClick, toolbar, mobileCard }: Props<T>) {
    return (
        <div className="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
            {toolbar && <div className="border-b border-border p-3 sm:p-4">{toolbar}</div>}

            {rows.length === 0 ? (
                <div className="p-6">{empty}</div>
            ) : (
                <>
                    {mobileCard && (
                        <div className="divide-y divide-border md:hidden">
                            {rows.map((row) => (
                                <div
                                    key={rowKey(row)}
                                    className={cn('p-4', onRowClick && 'cursor-pointer hover:bg-muted/40')}
                                    onClick={() => onRowClick?.(row)}
                                >
                                    {mobileCard(row)}
                                </div>
                            ))}
                        </div>
                    )}

                    <div className={cn('overflow-x-auto', mobileCard && 'hidden md:block')}>
                        <table className="min-w-full text-left text-sm">
                            <thead className="border-b border-border bg-muted/40 text-muted-foreground">
                                <tr>
                                    {columns.map((column) => (
                                        <th
                                            key={column.key}
                                            className={cn(
                                                'px-4 py-3 text-xs font-medium uppercase tracking-wide',
                                                column.hideOnMobile && 'hidden lg:table-cell',
                                                column.className,
                                            )}
                                        >
                                            {column.header}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {rows.map((row) => (
                                    <tr
                                        key={rowKey(row)}
                                        className={cn(
                                            'border-b border-border/70 last:border-0',
                                            onRowClick && 'cursor-pointer hover:bg-muted/30',
                                        )}
                                        onClick={() => onRowClick?.(row)}
                                    >
                                        {columns.map((column) => (
                                            <td
                                                key={column.key}
                                                className={cn(
                                                    'px-4 py-3 align-middle',
                                                    column.hideOnMobile && 'hidden lg:table-cell',
                                                    column.className,
                                                )}
                                            >
                                                {column.cell(row)}
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </>
            )}
        </div>
    );
}

export function PaginationLinks({
    links,
}: {
    links: Array<{ url: string | null; label: string; active: boolean }>;
}) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <div className="mt-4 flex flex-wrap gap-1">
            {links.map((link, index) =>
                link.url ? (
                    <a
                        key={`${link.label}-${index}`}
                        href={link.url}
                        className={cn(
                            'rounded-lg border px-3 py-1.5 text-xs transition-colors',
                            link.active
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-border bg-card text-muted-foreground hover:bg-muted',
                        )}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span
                        key={`${link.label}-${index}`}
                        className="rounded-lg border border-border px-3 py-1.5 text-xs text-muted-foreground/50"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ),
            )}
        </div>
    );
}

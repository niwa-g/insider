import { Link, router, usePage } from '@inertiajs/react';
import {
    Bell,
    ChevronRight,
    LogOut,
    Menu,
    Moon,
    PanelLeftClose,
    PanelLeftOpen,
    Search,
    Sun,
    User,
} from 'lucide-react';
import { FormEvent, ReactNode, useEffect, useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { commandLinks, mainNavigationSections, type NavItem, type NavSection } from '@/lib/navigation';
import { applyTheme, getStoredTheme, type Theme } from '@/lib/theme';
import { cn } from '@/lib/utils';

type SharedProps = {
    auth: {
        user: {
            name: string;
            email: string;
            permissions: string[];
            roles: string[];
        } | null;
    };
    appName: string;
};

const SIDEBAR_KEY = 'ark.sidebar.collapsed';
const EXPANDED_KEY = 'ark.nav.expanded';

function canSee(item: NavItem, permissions: string[]): boolean {
    return !item.permission || permissions.includes(item.permission);
}

function filterSection(section: NavSection, permissions: string[]): NavSection | null {
    const items: NavItem[] = [];
    for (const item of section.items) {
        const children = item.children?.filter((child) => canSee(child, permissions));
        if (canSee(item, permissions) || (children && children.length > 0)) {
            items.push({ ...item, children });
        }
    }
    return items.length ? { ...section, items } : null;
}

function pathMatches(current: string, href: string): boolean {
    const path = href.split('?')[0] || '/';
    if (path === '/') {
        return current === '/';
    }
    return current === path || current.startsWith(`${path}/`);
}

function itemIsActive(item: NavItem, current: string): boolean {
    return pathMatches(current, item.href) || (item.children?.some((child) => pathMatches(current, child.href)) ?? false);
}

function readJson<T>(key: string, fallback: T): T {
    try {
        const raw = localStorage.getItem(key);
        return raw ? (JSON.parse(raw) as T) : fallback;
    } catch {
        return fallback;
    }
}

function writeJson(key: string, value: unknown) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch {
        // ignore
    }
}

function CommandPalette({ open, onOpenChange }: { open: boolean; onOpenChange: (value: boolean) => void }) {
    const [query, setQuery] = useState('');

    useEffect(() => {
        if (!open) {
            setQuery('');
        }
    }, [open]);

    const results = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) {
            return commandLinks.slice(0, 8);
        }
        return commandLinks.filter(
            (item) => item.name.toLowerCase().includes(q) || item.href.toLowerCase().includes(q),
        );
    }, [query]);

    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-[70]">
            <button
                type="button"
                className="absolute inset-0 bg-black/40"
                onClick={() => onOpenChange(false)}
                aria-label="Close command palette"
            />
            <div className="relative mx-auto mt-[12vh] w-full max-w-xl px-4">
                <div className="overflow-hidden rounded-2xl border border-border bg-popover shadow-2xl">
                    <div className="flex items-center gap-2 border-b border-border px-3">
                        <Search className="h-4 w-4 text-muted-foreground" />
                        <input
                            autoFocus
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Search pages, jump anywhere…"
                            className="h-12 w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                        />
                    </div>
                    <div className="max-h-80 overflow-y-auto p-2">
                        {results.length === 0 ? (
                            <p className="px-3 py-6 text-center text-sm text-muted-foreground">No matches.</p>
                        ) : (
                            results.map((item) => {
                                const Icon = item.icon;
                                return (
                                    <button
                                        key={`${item.href}-${item.name}`}
                                        type="button"
                                        className="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm hover:bg-muted"
                                        onClick={() => {
                                            onOpenChange(false);
                                            router.visit(item.href);
                                        }}
                                    >
                                        <Icon className="h-4 w-4 text-muted-foreground" />
                                        <span className="flex-1 font-medium">{item.name}</span>
                                        <span className="text-xs text-muted-foreground">{item.href}</span>
                                    </button>
                                );
                            })
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

function NavTree({
    item,
    current,
    collapsed,
    expanded,
    onToggle,
    depth = 0,
}: {
    item: NavItem;
    current: string;
    collapsed: boolean;
    expanded: Record<string, boolean>;
    onToggle: (name: string) => void;
    depth?: number;
}) {
    const hasChildren = (item.children?.length ?? 0) > 0;
    const active = itemIsActive(item, current);
    const isOpen = Boolean(expanded[item.name]);
    const Icon = item.icon;

    if (collapsed) {
        return (
            <Tooltip>
                <TooltipTrigger asChild>
                    <Link
                        href={item.href}
                        className={cn(
                            'flex h-10 w-10 items-center justify-center rounded-xl transition-colors',
                            active
                                ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                : 'text-sidebar-muted hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                        )}
                    >
                        {Icon ? <Icon className="h-4 w-4" /> : <span className="text-xs">{item.name[0]}</span>}
                    </Link>
                </TooltipTrigger>
                <TooltipContent side="right">{item.name}</TooltipContent>
            </Tooltip>
        );
    }

    return (
        <div className="space-y-0.5">
            <div className="flex items-center gap-0.5">
                <Link
                    href={item.href}
                    className={cn(
                        'flex min-w-0 flex-1 items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm transition-colors',
                        active
                            ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground'
                            : 'text-sidebar-foreground hover:bg-sidebar-accent/70 hover:text-sidebar-accent-foreground',
                        depth > 0 && 'pl-9 text-[13px]',
                    )}
                >
                    {Icon && depth === 0 ? <Icon className="h-4 w-4 shrink-0 opacity-80" /> : null}
                    <span className="truncate">{item.name}</span>
                </Link>
                {hasChildren ? (
                    <button
                        type="button"
                        aria-label={isOpen ? `Collapse ${item.name}` : `Expand ${item.name}`}
                        onClick={() => onToggle(item.name)}
                        className="rounded-lg p-2 text-sidebar-muted hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                    >
                        <ChevronRight className={cn('h-3.5 w-3.5 transition-transform', isOpen && 'rotate-90')} />
                    </button>
                ) : null}
            </div>
            {hasChildren && isOpen ? (
                <div className="space-y-0.5 pb-1">
                    {item.children?.map((child) => (
                        <NavTree
                            key={child.name}
                            item={child}
                            current={current}
                            collapsed={false}
                            expanded={expanded}
                            onToggle={onToggle}
                            depth={depth + 1}
                        />
                    ))}
                </div>
            ) : null}
        </div>
    );
}

export default function AppLayout({ children, title }: { children: ReactNode; title?: string }) {
    const page = usePage<SharedProps>();
    const { auth, appName } = page.props;
    const current = page.url.split('?')[0] || '/';
    const permissions = auth.user?.permissions ?? [];

    const [mobileOpen, setMobileOpen] = useState(false);
    const [collapsed, setCollapsed] = useState(false);
    const [commandOpen, setCommandOpen] = useState(false);
    const [theme, setTheme] = useState<Theme>('system');
    const [expanded, setExpanded] = useState<Record<string, boolean>>({});
    const [search, setSearch] = useState('');

    const sections = useMemo(
        () =>
            mainNavigationSections
                .map((section) => filterSection(section, permissions))
                .filter((section): section is NavSection => section !== null),
        [permissions],
    );

    useEffect(() => {
        setCollapsed(readJson(SIDEBAR_KEY, false));
        setExpanded(readJson(EXPANDED_KEY, {}));
        const stored = getStoredTheme();
        setTheme(stored);
        applyTheme(stored);
    }, []);

    useEffect(() => {
        setExpanded((prev) => {
            let changed = false;
            const next = { ...prev };
            for (const section of sections) {
                for (const item of section.items) {
                    if ((item.children?.length ?? 0) > 0 && itemIsActive(item, current) && !next[item.name]) {
                        next[item.name] = true;
                        changed = true;
                    }
                }
            }
            if (changed) {
                writeJson(EXPANDED_KEY, next);
                return next;
            }
            return prev;
        });
    }, [current, sections]);

    useEffect(() => {
        const onKeyDown = (event: KeyboardEvent) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                setCommandOpen(true);
            }
            if (event.key === 'Escape') {
                setCommandOpen(false);
            }
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, []);

    useEffect(() => {
        setMobileOpen(false);
    }, [current]);

    const toggleCollapsed = () => {
        setCollapsed((prev) => {
            const next = !prev;
            writeJson(SIDEBAR_KEY, next);
            return next;
        });
    };

    const toggleExpanded = (name: string) => {
        setExpanded((prev) => {
            const next = { ...prev, [name]: !prev[name] };
            writeJson(EXPANDED_KEY, next);
            return next;
        });
    };

    const cycleTheme = () => {
        const order: Theme[] = ['light', 'dark', 'system'];
        const next = order[(order.indexOf(theme) + 1) % order.length] ?? 'system';
        setTheme(next);
        applyTheme(next);
    };

    const onSearch = (event: FormEvent) => {
        event.preventDefault();
        setCommandOpen(true);
    };

    const sidebar = (
        <aside
            className={cn(
                'flex h-full flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-[width] duration-200',
                collapsed ? 'w-[72px]' : 'w-[250px]',
            )}
        >
            <div className={cn('flex h-14 items-center gap-2 border-b border-sidebar-border px-3', collapsed && 'justify-center')}>
                <Link href="/dashboard" className="flex min-w-0 items-center gap-2.5">
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-xs font-bold text-primary-foreground">
                        A
                    </div>
                    {!collapsed ? (
                        <div className="min-w-0">
                            <div className="truncate text-sm font-semibold text-sidebar-accent-foreground">{appName || 'Ark'}</div>
                            <div className="truncate text-[11px] text-sidebar-muted">IT Operations</div>
                        </div>
                    ) : null}
                </Link>
            </div>

            <nav className={cn('flex-1 space-y-5 overflow-y-auto px-2 py-4', collapsed && 'space-y-2 px-1.5')}>
                {sections.map((section) => (
                    <div key={section.id} className="space-y-1">
                        {!collapsed && section.label ? (
                            <div className="px-2.5 pb-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-sidebar-muted">
                                {section.label}
                            </div>
                        ) : null}
                        <div className={cn('space-y-0.5', collapsed && 'flex flex-col items-center gap-1')}>
                            {section.items.map((item) => (
                                <NavTree
                                    key={item.name}
                                    item={item}
                                    current={current}
                                    collapsed={collapsed}
                                    expanded={expanded}
                                    onToggle={toggleExpanded}
                                />
                            ))}
                        </div>
                    </div>
                ))}
            </nav>

            <div className={cn('border-t border-sidebar-border p-2', collapsed && 'flex justify-center')}>
                <Button
                    type="button"
                    variant="ghost"
                    size={collapsed ? 'icon' : 'sm'}
                    onClick={toggleCollapsed}
                    className="hidden text-sidebar-muted hover:bg-sidebar-accent hover:text-sidebar-accent-foreground lg:inline-flex"
                >
                    {collapsed ? <PanelLeftOpen className="h-4 w-4" /> : <PanelLeftClose className="h-4 w-4" />}
                    {!collapsed ? <span>Collapse</span> : null}
                </Button>
            </div>
        </aside>
    );

    return (
        <TooltipProvider delayDuration={200}>
            <div className="min-h-screen bg-background text-foreground">
                <div className="flex min-h-screen">
                    <div className="hidden lg:block">{sidebar}</div>

                    {mobileOpen ? (
                        <div className="fixed inset-0 z-50 lg:hidden">
                            <button type="button" className="absolute inset-0 bg-black/40" onClick={() => setMobileOpen(false)} />
                            <div className="absolute inset-y-0 left-0 h-full shadow-2xl">{sidebar}</div>
                        </div>
                    ) : null}

                    <div className="flex min-w-0 flex-1 flex-col">
                        <header className="sticky top-0 z-40 flex h-14 items-center gap-3 border-b border-border bg-card/90 px-3 backdrop-blur md:px-5">
                            <Button type="button" variant="ghost" size="icon" className="lg:hidden" onClick={() => setMobileOpen(true)}>
                                <Menu className="h-4 w-4" />
                            </Button>

                            <div className="min-w-0 flex-1">
                                <div className="truncate text-xs text-muted-foreground">Operations</div>
                                <div className="truncate text-sm font-semibold">{title || 'Dashboard'}</div>
                            </div>

                            <form onSubmit={onSearch} className="relative hidden w-full max-w-md md:block">
                                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onFocus={() => setCommandOpen(true)}
                                    placeholder="Search or jump to… ⌘K"
                                    className="h-9 pl-9"
                                />
                            </form>

                            <Button type="button" variant="ghost" size="icon" className="md:hidden" onClick={() => setCommandOpen(true)}>
                                <Search className="h-4 w-4" />
                            </Button>

                            <Button type="button" variant="ghost" size="icon" onClick={cycleTheme} aria-label="Toggle theme">
                                {theme === 'dark' ? <Moon className="h-4 w-4" /> : <Sun className="h-4 w-4" />}
                            </Button>

                            <Button type="button" variant="ghost" size="icon" aria-label="Notifications">
                                <Bell className="h-4 w-4" />
                            </Button>

                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="ghost" className="h-9 gap-2 px-2">
                                        <div className="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                            {(auth.user?.name ?? 'U').slice(0, 1).toUpperCase()}
                                        </div>
                                        <div className="hidden text-left sm:block">
                                            <div className="text-xs font-medium leading-none">{auth.user?.name}</div>
                                            <div className="mt-1 text-[11px] text-muted-foreground">{auth.user?.roles?.[0]}</div>
                                        </div>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="w-56">
                                    <DropdownMenuLabel>
                                        <div className="font-medium">{auth.user?.name}</div>
                                        <div className="text-xs font-normal text-muted-foreground">{auth.user?.email}</div>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem onSelect={() => router.visit('/settings')}>
                                        <User className="h-4 w-4" />
                                        Settings
                                    </DropdownMenuItem>
                                    <DropdownMenuItem onSelect={() => router.post('/logout')}>
                                        <LogOut className="h-4 w-4" />
                                        Sign out
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </header>

                        <main className="flex-1 p-4 md:p-6">{children}</main>
                    </div>
                </div>

                <CommandPalette open={commandOpen} onOpenChange={setCommandOpen} />
            </div>
        </TooltipProvider>
    );
}

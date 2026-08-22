import { describe, expect, it } from 'vitest';
import { mainNavigation, mainNavigationSections } from './lib/navigation';

describe('navigation', () => {
    it('includes dashboard and clients', () => {
        expect(mainNavigation.some((item) => item.name === 'Dashboard')).toBe(true);
        const clients = mainNavigation.find((item) => item.name === 'Clients');
        expect(clients?.href).toBe('/clients');
        expect(clients?.children?.some((c) => c.href === '/contacts')).toBe(true);
    });

    it('lists applications, infrastructure, domains, and hosting under Assets section', () => {
        const assetsSection = mainNavigationSections.find((s) => s.id === 'assets');
        expect(assetsSection).toBeTruthy();

        const names = assetsSection?.items.map((item) => item.name) ?? [];
        expect(names).toEqual(expect.arrayContaining(['Applications', 'Infrastructure', 'Domains', 'Hosting']));
        expect(assetsSection?.items.find((i) => i.name === 'Applications')?.href).toBe('/applications');
        expect(assetsSection?.items.find((i) => i.name === 'Infrastructure')?.href).toBe('/servers');
        expect(assetsSection?.items.find((i) => i.name === 'Domains')?.href).toBe('/domains');
        expect(assetsSection?.items.find((i) => i.name === 'Hosting')?.href).toBe('/hosting-accounts');
    });
});

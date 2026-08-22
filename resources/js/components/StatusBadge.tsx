import { StatusBadge as UiStatusBadge } from '@/components/ui/badge';

export default function StatusBadge({
    status,
    label,
}: {
    status: string;
    label?: string;
}) {
    return <UiStatusBadge status={status} label={label} />;
}

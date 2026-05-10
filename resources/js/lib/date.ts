function parseDate(value: string): Date | null {
    if (!value) {
        return null;
    }

    const ymdMatch = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (ymdMatch) {
        const [, year, month, day] = ymdMatch;
        return new Date(Number(year), Number(month) - 1, Number(day));
    }

    const dmyMatch = value.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (dmyMatch) {
        const [, day, month, year] = dmyMatch;
        return new Date(Number(year), Number(month) - 1, Number(day));
    }

    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

export function formatDateDMY(value?: string | null): string {
    if (!value) {
        return '-';
    }

    const parsed = parseDate(value);
    if (!parsed) {
        return value;
    }

    const day = String(parsed.getDate()).padStart(2, '0');
    const month = String(parsed.getMonth() + 1).padStart(2, '0');
    const year = parsed.getFullYear();

    return `${day}-${month}-${year}`;
}

import Flatpickr from 'react-flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { cn } from '@/lib/utils';

interface DatePickerProps {
    id?: string;
    value: string;
    onChange: (value: string) => void;
    className?: string;
    placeholder?: string;
}

const baseInputClass =
    'border-input file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]';

export function DatePicker({ id, value, onChange, className, placeholder }: DatePickerProps) {
    return (
        <Flatpickr
            id={id}
            value={value || ''}
            onChange={(_, dateStr) => onChange(dateStr)}
            options={{
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                altInputClass: cn(baseInputClass, className),
                allowInput: true,
                disableMobile: true,
            }}
            placeholder={placeholder}
            className="sr-only"
        />
    );
}

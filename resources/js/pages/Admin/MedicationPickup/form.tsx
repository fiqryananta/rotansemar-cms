import { Checkbox } from '@/components/ui/checkbox';
import { DatePicker } from '@/components/ui/date-picker';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const textAreaClassName =
    'mt-1 min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]';

interface Pasien {
    id: number;
    name: string;
    nik: string;
}

interface Faskes {
    id: number;
    name: string;
}

interface Puskesmas {
    id: number;
    name: string;
}

interface FormData {
    pasien_id: number | string;
    faskes_id: number | string | null;
    puskesmas_id: number | string | null;
    scheduled_date: string;
    status: string;
    actual_date: string;
    transfer_date: string;
    next_pickup_date: string;
    target_faskes_id: number | string | null;
    is_outside_city: boolean;
    notes: string;
}

interface FormErrors {
    [key: string]: string | undefined;
}

interface Props {
    data: FormData;
    setData: (key: string, value: any) => void;
    clearErrors?: (...fields: string[]) => void;
    errors?: FormErrors;
    pasiens: Pasien[];
    faskes: Faskes[];
    puskesmas: Puskesmas[];
    statuses: Record<string, string>;
}

export default function MedicationPickupForm({
    data,
    setData,
    clearErrors,
    errors = {},
    pasiens,
    faskes,
    puskesmas,
    statuses,
}: Props) {
    return (
        <div className="space-y-6">
            {/* Pasien Selection */}
            <div>
                <Label htmlFor="pasien_id">Pasien *</Label>
                <Select
                    value={data.pasien_id ? String(data.pasien_id) : ''}
                    onValueChange={(value) => {
                        setData('pasien_id', Number(value));
                        clearErrors?.('pasien_id');
                    }}
                >
                    <SelectTrigger id="pasien_id" className="mt-1">
                        <SelectValue placeholder="Pilih Pasien" />
                    </SelectTrigger>
                    <SelectContent>
                        {pasiens.map((p) => (
                            <SelectItem key={p.id} value={String(p.id)}>
                                {p.name} ({p.nik})
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.pasien_id && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.pasien_id}
                    </p>
                )}
            </div>

            {/* Faskes Selection */}
            <div>
                <Label htmlFor="faskes_id">Faskes</Label>
                <Select
                    value={data.faskes_id ? String(data.faskes_id) : ''}
                    onValueChange={(value) => {
                        setData('faskes_id', value ? Number(value) : null);
                        clearErrors?.('faskes_id');
                    }}
                >
                    <SelectTrigger id="faskes_id" className="mt-1">
                        <SelectValue placeholder="Pilih Faskes (Opsional)" />
                    </SelectTrigger>
                    <SelectContent>
                        {faskes.map((f) => (
                            <SelectItem key={f.id} value={String(f.id)}>
                                {f.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.faskes_id && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.faskes_id}
                    </p>
                )}
            </div>

            {/* Puskesmas Selection */}
            <div>
                <Label htmlFor="puskesmas_id">Puskesmas</Label>
                <Select
                    value={data.puskesmas_id ? String(data.puskesmas_id) : ''}
                    onValueChange={(value) => {
                        setData('puskesmas_id', value ? Number(value) : null);
                        clearErrors?.('puskesmas_id');
                    }}
                >
                    <SelectTrigger id="puskesmas_id" className="mt-1">
                        <SelectValue placeholder="Pilih Puskesmas (Opsional)" />
                    </SelectTrigger>
                    <SelectContent>
                        {puskesmas.map((p) => (
                            <SelectItem key={p.id} value={String(p.id)}>
                                {p.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.puskesmas_id && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.puskesmas_id}
                    </p>
                )}
            </div>

            {/* Scheduled Date */}
            <div>
                <Label htmlFor="scheduled_date">Tanggal Jadwal *</Label>
                <DatePicker
                    id="scheduled_date"
                    value={data.scheduled_date}
                    onChange={(value) => {
                        setData('scheduled_date', value);
                        clearErrors?.('scheduled_date');
                    }}
                    className="mt-1"
                />
                {errors.scheduled_date && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.scheduled_date}
                    </p>
                )}
            </div>

            {/* Status Selection */}
            <div>
                <Label htmlFor="status">Status *</Label>
                <Select
                    value={data.status || ''}
                    onValueChange={(value) => {
                        setData('status', value);
                        clearErrors?.('status');
                    }}
                >
                    <SelectTrigger id="status" className="mt-1">
                        <SelectValue placeholder="Pilih Status" />
                    </SelectTrigger>
                    <SelectContent>
                        {Object.entries(statuses).map(([key, label]) => (
                            <SelectItem key={key} value={key}>
                                {label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.status && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.status}
                    </p>
                )}
            </div>

            {/* Conditional Fields Based on Status */}
            {data.status === 'terrealisasi' && (
                <>
                    <div>
                        <Label htmlFor="actual_date">
                            Tanggal Pengambilan Obat
                        </Label>
                        <DatePicker
                            id="actual_date"
                            value={data.actual_date}
                            onChange={(value) => {
                                setData('actual_date', value);
                                clearErrors?.('actual_date');
                            }}
                            className="mt-1"
                        />
                        {errors.actual_date && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.actual_date}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="next_pickup_date">
                            Jadwal Pengambilan Selanjutnya
                        </Label>
                        <DatePicker
                            id="next_pickup_date"
                            value={data.next_pickup_date}
                            onChange={(value) => {
                                setData('next_pickup_date', value);
                                clearErrors?.('next_pickup_date');
                            }}
                            className="mt-1"
                        />
                        {errors.next_pickup_date && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.next_pickup_date}
                            </p>
                        )}
                    </div>
                </>
            )}

            {data.status === 'pindah' && (
                <>
                    <div>
                        <Label htmlFor="transfer_date">Tanggal Pindah</Label>
                        <DatePicker
                            id="transfer_date"
                            value={data.transfer_date}
                            onChange={(value) => {
                                setData('transfer_date', value);
                                clearErrors?.('transfer_date');
                            }}
                            className="mt-1"
                        />
                        {errors.transfer_date && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.transfer_date}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="notes">Alasan / Keterangan</Label>
                        <textarea
                            id="notes"
                            value={data.notes}
                            onChange={(event) => {
                                setData('notes', event.target.value);
                                clearErrors?.('notes');
                            }}
                            placeholder="Masukkan alasan atau keterangan pindah"
                            className={textAreaClassName}
                            rows={3}
                        />
                        {errors.notes && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.notes}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="next_pickup_date">
                            Rencana Pasien ke Faskes Tujuan
                        </Label>
                        <DatePicker
                            id="next_pickup_date"
                            value={data.next_pickup_date}
                            onChange={(value) => {
                                setData('next_pickup_date', value);
                                clearErrors?.('next_pickup_date');
                            }}
                            className="mt-1"
                        />
                        {errors.next_pickup_date && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.next_pickup_date}
                            </p>
                        )}
                    </div>

                    <div className="flex items-center gap-2">
                        <Checkbox
                            id="is_outside_city"
                            checked={data.is_outside_city}
                            onCheckedChange={(checked) => {
                                setData('is_outside_city', Boolean(checked));
                                clearErrors?.('is_outside_city', 'target_faskes_id');
                            }}
                        />
                        <Label
                            htmlFor="is_outside_city"
                            className="cursor-pointer"
                        >
                            Pindah ke Luar Kota
                        </Label>
                    </div>

                    {!data.is_outside_city && (
                        <div>
                            <Label htmlFor="target_faskes_id">
                                Faskes Tujuan
                            </Label>
                            <Select
                                value={data.target_faskes_id ? String(data.target_faskes_id) : ''}
                                onValueChange={(value) => {
                                    setData(
                                        'target_faskes_id',
                                        value ? Number(value) : null,
                                    );
                                    clearErrors?.('target_faskes_id');
                                }}
                            >
                                <SelectTrigger
                                    id="target_faskes_id"
                                    className="mt-1"
                                >
                                    <SelectValue placeholder="Pilih Faskes Tujuan" />
                                </SelectTrigger>
                                <SelectContent>
                                    {faskes.map((f) => (
                                        <SelectItem key={f.id} value={String(f.id)}>
                                            {f.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.target_faskes_id && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.target_faskes_id}
                                </p>
                            )}
                        </div>
                    )}
                </>
            )}

            {(data.status === 'putus_obat' ||
                data.status === 'meninggal' ||
                data.status === 'obat_terakhir') && (
                <>
                    {data.status === 'obat_terakhir' && (
                        <div>
                            <Label htmlFor="actual_date">
                                Tanggal Pengambilan Obat
                            </Label>
                            <DatePicker
                                id="actual_date"
                                value={data.actual_date}
                                onChange={(value) => {
                                    setData('actual_date', value);
                                    clearErrors?.('actual_date');
                                }}
                                className="mt-1"
                            />
                            {errors.actual_date && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.actual_date}
                                </p>
                            )}
                        </div>
                    )}

                    <div>
                        <Label htmlFor="notes">Keterangan / Catatan</Label>
                        <textarea
                            id="notes"
                            value={data.notes}
                            onChange={(event) => {
                                setData('notes', event.target.value);
                                clearErrors?.('notes');
                            }}
                            placeholder="Masukkan keterangan atau catatan"
                            className={textAreaClassName}
                            rows={3}
                        />
                        {errors.notes && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.notes}
                            </p>
                        )}
                    </div>
                </>
            )}
        </div>
    );
}

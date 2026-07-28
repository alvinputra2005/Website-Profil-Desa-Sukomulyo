<?php

namespace App\Services;

use App\Models\FamilyCard;
use App\Models\Household;
use App\Models\PopulationArea;
use App\Models\PopulationYearlySnapshot;
use App\Models\Resident;
use App\Models\ResidentEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class PopulationStatistics
{
    public const DEMO_MARKER = 'DATA DUMMY STATISTIK PENDUDUK';

    public const DEMO_SOURCE = 'Data dummy lokal untuk visualisasi';

    public function residents()
    {
        return Resident::query()->where('status', 'active');
    }

    public function summary(): array
    {
        $base = $this->residents();

        return [
            'residents' => (clone $base)->count(),
            'male' => (clone $base)->where('sex', 'L')->count(),
            'female' => (clone $base)->where('sex', 'P')->count(),
            'families' => FamilyCard::where('is_active', true)->count(),
            'households' => Household::where('is_active', true)->count(),
            'areas' => PopulationArea::distinct()->count('hamlet'),
            'education_records' => (clone $base)->whereNotNull('education')->where('education', '!=', '')->count(),
            'occupation_records' => (clone $base)->whereNotNull('occupation')->where('occupation', '!=', '')->count(),
        ];
    }

    public function genderSummary(): array
    {
        $base = $this->residents();
        $male = (clone $base)->where('sex', 'L')->count();
        $female = (clone $base)->where('sex', 'P')->count();
        $total = $male + $female;
        $updatedAt = (clone $base)->max('updated_at');
        $dummyCount = (clone $base)->where('notes', self::DEMO_MARKER)->count();

        return [
            'year' => (int) config('village.population_year', now()->year),
            'male' => $male,
            'female' => $female,
            'total' => $total,
            'male_percentage' => $total > 0 ? round($male / $total * 100, 2) : 0.0,
            'female_percentage' => $total > 0 ? round($female / $total * 100, 2) : 0.0,
            'updated_at' => $updatedAt ? Carbon::parse($updatedAt)->toAtomString() : null,
            'source' => $total > 0 && $dummyCount === $total
                ? self::DEMO_SOURCE
                : 'Administrasi Kependudukan Desa',
        ];
    }

    public function yearlyTrend(?int $fromYear = null, ?int $toYear = null): array
    {
        $currentYear = (int) config('village.population_year', now()->year);
        $toYear ??= $currentYear;

        if ($toYear > $currentYear) {
            throw new InvalidArgumentException('Tahun akhir tidak boleh melebihi tahun data aktif.');
        }
        if ($fromYear !== null && $fromYear > $toYear) {
            throw new InvalidArgumentException('Tahun awal tidak boleh melebihi tahun akhir.');
        }

        $snapshots = collect();
        if (Schema::hasTable('population_yearly_snapshots')) {
            $query = PopulationYearlySnapshot::query()
                ->where('is_published', true)
                ->where('year', '<=', $toYear);

            if ($fromYear !== null) {
                $query->where('year', '>=', $fromYear);
            }

            $snapshots = $query->orderBy('year')->get();
        }

        $rows = $snapshots->map(fn (PopulationYearlySnapshot $snapshot): array => [
            'year' => $snapshot->year,
            'male' => $snapshot->male_count,
            'female' => $snapshot->female_count,
            'total' => $snapshot->total_count,
            'source' => $snapshot->source,
            'reference_date' => $snapshot->reference_date?->format('Y-m-d'),
        ]);

        $includesCurrentYear = $currentYear <= $toYear
            && ($fromYear === null || $currentYear >= $fromYear);

        if ($includesCurrentYear && ! $rows->contains('year', $currentYear)) {
            $summary = $this->genderSummary();
            $referenceDate = $summary['updated_at']
                ? Carbon::parse($summary['updated_at'])->toDateString()
                : null;

            $rows->push([
                'year' => $currentYear,
                'male' => $summary['male'],
                'female' => $summary['female'],
                'total' => $summary['total'],
                'source' => 'Data aktif per '.($referenceDate
                    ? Carbon::parse($referenceDate)->translatedFormat('d F Y')
                    : 'tanggal pembaruan terakhir'),
                'reference_date' => $referenceDate,
            ]);
        }

        $previousTotal = null;

        return $rows
            ->sortBy('year')
            ->values()
            ->map(function (array $row) use (&$previousTotal): array {
                $change = $previousTotal === null ? null : $row['total'] - $previousTotal;
                $growth = $previousTotal === null || $previousTotal === 0
                    ? null
                    : round($change / $previousTotal * 100, 2);
                $previousTotal = $row['total'];

                return array_merge($row, [
                    'change' => $change,
                    'growth_percentage' => $growth,
                ]);
            })
            ->all();
    }

    public function distribution(string $category): array
    {
        $residents = $this->residents()->get();
        $labels = match ($category) {
            'age' => $residents->map(fn (Resident $resident) => $this->ageGroup($resident->age)),
            'sex' => $residents->map(fn (Resident $resident) => $resident->sex_label),
            'area' => $residents->map(fn (Resident $resident) => $resident->area?->label ?? 'Belum terdata'),
            'religion' => $residents->pluck('religion')->map(fn ($value) => $value ?: 'Belum terdata'),
            'education' => $residents->pluck('education')->map(fn ($value) => $value ?: 'Belum terdata'),
            'occupation' => $residents->pluck('occupation')->map(fn ($value) => $value ?: 'Belum terdata'),
            'marital_status' => $residents->pluck('marital_status')->map(fn ($value) => $value ?: 'Belum terdata'),
            'citizenship' => $residents->pluck('citizenship')->map(fn ($value) => $value ?: 'Belum terdata'),
            default => $residents->map(fn (Resident $resident) => $resident->sex_label),
        };

        $order = $category === 'age'
            ? ['0–4 tahun', '5–14 tahun', '15–24 tahun', '25–44 tahun', '45–64 tahun', '65+ tahun', 'Belum terdata']
            : [];
        $groups = $labels->zip($residents)->groupBy(fn (Collection $pair) => $pair[0]);
        $rows = $groups->map(function (Collection $items, string $label) use ($residents) {
            $people = $items->pluck(1);
            $total = $people->count();

            return [
                'label' => $label,
                'total' => $total,
                'male' => $people->where('sex', 'L')->count(),
                'female' => $people->where('sex', 'P')->count(),
                'percentage' => $residents->count() ? round($total / $residents->count() * 100, 2) : 0,
            ];
        });

        if ($order !== []) {
            $rows = collect($order)->map(fn (string $label) => $rows->get($label))->filter();
        } else {
            $rows = $rows->sortByDesc('total')->values();
        }

        return $rows->values()->all();
    }

    public function monthlyReport(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $events = ResidentEvent::whereBetween('event_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        $ending = $this->summaryBySexAt($end);
        $changes = collect(['birth', 'arrival', 'departure', 'death'])->mapWithKeys(function (string $type) use ($events) {
            $items = $events->where('event_type', $type);

            return [$type => [
                'total' => $items->count(),
                'male' => $items->where('sex', 'L')->count(),
                'female' => $items->where('sex', 'P')->count(),
            ]];
        })->all();

        $beginning = [];
        foreach (['total', 'male', 'female'] as $key) {
            $beginning[$key] = max(0, $ending[$key] - $changes['birth'][$key] - $changes['arrival'][$key] + $changes['departure'][$key] + $changes['death'][$key]);
        }

        return compact('start', 'end', 'beginning', 'ending', 'changes', 'events');
    }

    private function summaryBySexAt(Carbon $end): array
    {
        $current = [
            'total' => $this->residents()->count(),
            'male' => $this->residents()->where('sex', 'L')->count(),
            'female' => $this->residents()->where('sex', 'P')->count(),
        ];

        $futureEvents = ResidentEvent::whereDate('event_date', '>', $end->toDateString())->get();
        foreach ($futureEvents as $event) {
            $key = $event->sex === 'L' ? 'male' : 'female';
            $delta = in_array($event->event_type, ['birth', 'arrival', 'reactivated'], true) ? -1
                : (in_array($event->event_type, ['departure', 'death', 'missing'], true) ? 1 : 0);
            $current['total'] += $delta;
            $current[$key] += $delta;
        }

        return collect($current)->map(fn (int $value) => max(0, $value))->all();
    }

    private function ageGroup(?int $age): string
    {
        return match (true) {
            $age === null => 'Belum terdata',
            $age <= 4 => '0–4 tahun',
            $age <= 14 => '5–14 tahun',
            $age <= 24 => '15–24 tahun',
            $age <= 44 => '25–44 tahun',
            $age <= 64 => '45–64 tahun',
            default => '65+ tahun',
        };
    }
}

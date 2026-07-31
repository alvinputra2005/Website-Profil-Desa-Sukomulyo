<?php

namespace App\Services\Web;

use App\Services\Letters\LetterSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdministrativeServicePage
{
    public function __construct(private readonly LetterSettings $letterSettings) {}

    public function data(): array
    {
        $config = config('administrative_services');
        $services = collect($config['services'])->map(
            fn (array $service): array => $this->prepareService($service)
        );

        return [
            'page' => $config['page'],
            'services' => $services,
            'submissionSteps' => collect($config['submission_steps']),
            'submissionNotice' => $config['submission_notice'],
            'officeHours' => $this->letterSettings->officeHours(),
            'office' => $config['office'],
            'whatsappUrl' => sprintf(
                'https://wa.me/%s?text=%s',
                $config['office']['whatsapp_e164'],
                rawurlencode($config['office']['whatsapp_message'])
            ),
        ];
    }

    private function prepareService(array $service): array
    {
        $service['own_search_text'] = $this->searchText([
            $service['title'],
            ...($service['keywords'] ?? []),
            ...($service['requirements'] ?? []),
            ...($service['notes'] ?? []),
        ]);

        if (isset($service['children'])) {
            $service['children'] = collect($service['children'])->map(function (array $child): array {
                $child['search_text'] = $this->searchText([
                    $child['title'],
                    ...($child['keywords'] ?? []),
                    ...$child['requirements'],
                    ...($child['notes'] ?? []),
                ]);

                return $child;
            });
        }

        $service['search_text'] = $this->serviceSearchText($service);

        return $service;
    }

    private function serviceSearchText(array $service): string
    {
        $values = [$service['own_search_text']];

        if (isset($service['children'])) {
            $values = [
                ...$values,
                ...$service['children']->flatMap(fn (array $child): array => [
                    $child['title'],
                    ...($child['keywords'] ?? []),
                    ...$child['requirements'],
                    ...($child['notes'] ?? []),
                ]),
            ];
        }

        return $this->searchText($values);
    }

    private function searchText(array|Collection $values): string
    {
        return collect($values)
            ->map(fn (mixed $value): string => Str::lower((string) $value))
            ->implode(' ');
    }
}

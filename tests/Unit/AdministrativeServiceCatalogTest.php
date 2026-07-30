<?php

namespace Tests\Unit;

use Tests\TestCase;

class AdministrativeServiceCatalogTest extends TestCase
{
    public function test_service_catalog_has_a_valid_and_unique_structure(): void
    {
        $services = collect(config('administrative_services.services'));

        $this->assertCount(8, $services);
        $this->assertSame($services->count(), $services->pluck('id')->unique()->count());

        $services->each(function (array $service): void {
            $this->assertNotEmpty($service['id']);
            $this->assertNotEmpty($service['title']);
            $this->assertNotEmpty($service['icon']);
            $this->assertNotSame(isset($service['children']), isset($service['requirements']));

            collect($service['children'] ?? [$service])->each(function (array $item): void {
                $this->assertNotEmpty($item['requirements']);
                $this->assertIsArray($item['notes']);
            });
        });
    }

    public function test_office_and_flow_data_are_valid(): void
    {
        $office = config('administrative_services.office');
        $flow = collect(config('administrative_services.service_flow'));

        $this->assertMatchesRegularExpression('/^\d+$/', $office['whatsapp_e164']);
        $this->assertSame($flow->count(), $flow->pluck('number')->unique()->count());
        $this->assertSame(range(1, 6), $flow->pluck('number')->all());
    }
}

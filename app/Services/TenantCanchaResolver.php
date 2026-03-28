<?php

namespace App\Services;

use App\Models\Cancha;

class TenantCanchaResolver
{
    public function resolve(): ?Cancha
    {
        $host = request()->getHost();

        if ($host && !in_array($host, ['127.0.0.1', 'localhost'], true)) {
            $subdominio = explode('.', $host)[0] ?? null;

            if ($subdominio) {
                $cancha = Cancha::where('subdominio', $subdominio)->first();

                if ($cancha) {
                    return $cancha;
                }
            }
        }

        if ($subdominio = env('TENANT_SUBDOMAIN')) {
            $cancha = Cancha::where('subdominio', $subdominio)->first();

            if ($cancha) {
                return $cancha;
            }
        }

        if ($identifier = env('TENANT_INTEGRATION_IDENTIFIER')) {
            return Cancha::where('integration_identifier', $identifier)->first();
        }

        return null;
    }
}

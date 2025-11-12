<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGlpiConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'glpi:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la conexión con la API de GLPI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando prueba de conexión con GLPI...');
        $this->newLine();

        // Validar configuración
        $apiUrl = config('services.glpi.api_url', env('GLPI_API_URL'));
        $appToken = config('services.glpi.app_token', env('GLPI_APP_TOKEN'));
        $userToken = config('services.glpi.user_token', env('GLPI_USER_TOKEN'));

        if (!$apiUrl || !$appToken || !$userToken) {
            $this->error('❌ Error: Faltan credenciales de GLPI en el archivo .env');
            $this->newLine();
            $this->line('Asegúrate de configurar:');
            $this->line('  - GLPI_API_URL');
            $this->line('  - GLPI_APP_TOKEN');
            $this->line('  - GLPI_USER_TOKEN');
            return 1;
        }

        $this->line("📍 URL: {$apiUrl}");
        $this->line("🔑 App Token: " . substr($appToken, 0, 10) . "...");
        $this->line("👤 User Token: " . substr($userToken, 0, 10) . "...");
        $this->newLine();

        try {
            // Intentar iniciar sesión
            $this->info('🔐 Intentando iniciar sesión...');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'App-Token' => $appToken,
                'Authorization' => "user_token {$userToken}",
            ])->get("{$apiUrl}/initSession");

            if ($response->successful()) {
                $data = $response->json();
                $sessionToken = $data['session_token'] ?? null;

                $this->newLine();
                $this->info('✅ ¡Conexión exitosa!');
                $this->newLine();
                $this->line("📊 Datos de sesión:");
                $this->line("  - Session Token: " . substr($sessionToken, 0, 20) . "...");

                // Obtener información adicional
                if (isset($data['glpiID'])) {
                    $this->line("  - GLPI ID: {$data['glpiID']}");
                }
                if (isset($data['glpiname'])) {
                    $this->line("  - Usuario: {$data['glpiname']}");
                }
                if (isset($data['glpirealname'])) {
                    $this->line("  - Nombre: {$data['glpirealname']}");
                }

                // Cerrar sesión
                if ($sessionToken) {
                    $this->newLine();
                    $this->info('🔒 Cerrando sesión...');

                    $killResponse = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'App-Token' => $appToken,
                        'Session-Token' => $sessionToken,
                    ])->get("{$apiUrl}/killSession");

                    if ($killResponse->successful()) {
                        $this->info('✅ Sesión cerrada correctamente');
                    }
                }

                $this->newLine();
                $this->info('🎉 La configuración de GLPI está funcionando correctamente');
                return 0;

            } else {
                $this->newLine();
                $this->error('❌ Error al conectar con GLPI');
                $this->newLine();
                $this->line("📋 Código de estado: {$response->status()}");
                $this->line("📄 Respuesta:");
                $this->line($response->body());
                return 1;
            }

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Error de conexión:');
            $this->error($e->getMessage());
            $this->newLine();

            if (str_contains($e->getMessage(), 'cURL error')) {
                $this->warn('💡 Sugerencia: Verifica que la URL de GLPI sea accesible desde este servidor');
            }

            return 1;
        }
    }
}
